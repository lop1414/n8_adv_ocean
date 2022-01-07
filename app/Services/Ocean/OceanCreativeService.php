<?php

namespace App\Services\Ocean;

use App\Common\Enums\NoticeStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanCreativeStatusEnum;
use App\Models\Ocean\OceanAdModel;
use App\Models\Ocean\OceanCreativeLogModel;
use App\Models\Ocean\OceanCreativeModel;

class OceanCreativeService extends OceanService
{
    /**
     * OceanCreativeService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $accountIds
     * @param $accessToken
     * @param $filtering
     * @param $page
     * @param $pageSize
     * @param array $param
     * @return mixed|void
     * sdk并发获取列表
     */
    public function sdkMultiGetList($accountIds, $accessToken, $filtering, $page, $pageSize, $param = []){
        return $this->sdk->multiGetCreativeList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option = []){
        ini_set('memory_limit', '2048M');

        $t = microtime(1);

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
        }

        // 并发分片大小
        if(!empty($option['multi_chunk_size'])){
            $multiChunkSize = min(intval($option['multi_chunk_size']), 8);
            $this->sdk->setMultiChunkSize($multiChunkSize);
        }

        $filtering = [];

        // 创建日期
        if(!empty($option['create_date'])){
            $filtering['creative_create_time'] = Functions::getDate($option['create_date']);
        }

        // 更新日期
        if(!empty($option['update_date'])){
            $filtering['creative_modify_time'] = Functions::getDate($option['update_date']);
        }

        // 广告组id
        if(!empty($option['campaign_id'])){
            $filtering['campaign_id'] = trim($option['campaign_id']);
        }

        // 广告计划id
        if(!empty($option['ad_id'])){
            $filtering['ad_id'] = trim($option['ad_id']);
        }

        // 状态
        if(!empty($option['status'])){
            $status = strtoupper($option['status']);
            Functions::hasEnum(OceanCreativeStatusEnum::class, $status);
            $filtering['status'] = $status;
        }else{
            $filtering['status'] = OceanCreativeStatusEnum::CREATIVE_STATUS_ALL;
        }

        // 获取子账户组
        $accountGroup = $this->getSubAccountGroup($accountIds);

        $pageSize = 100;
        foreach($accountGroup as $g){
            $items = $this->multiGetPageList($g, $filtering, $pageSize);
            Functions::consoleDump('count:'. count($items));

            // 保存
            $data = [];
            foreach($items as $item) {
                $item['extends'] = json_encode($item);
                $item['id'] = $item['creative_id'];

                $datetime = date('Y-m-d H:i:s');

                $item['created_at'] = $datetime;
                $item['updated_at'] = $datetime;

                $data[] = $item;

                if(!empty($option['create_log'])){
                    // 创建日志
                    $this->createLog($item);
                }
            }

            // 批量保存
            $this->batchSave($data);
        }

        $t = microtime(1) - $t;
        Functions::consoleDump($t);

        return true;
    }

    /**
     * @param $data
     * @return bool
     * 批量保存
     */
    public function batchSave($data){
        $oceanCreativeModel = new OceanCreativeModel();
        $oceanCreativeModel->chunkInsertOrUpdate($data, 50, $oceanCreativeModel->getTable(), $oceanCreativeModel->getTableColumnsWithPrimaryKey());
        return true;
    }

    /**
     * @param $item
     * @return bool
     * 创建日志
     */
    public function createLog($item){
        $oceanCreative = OceanCreativeModel::find($item['id']);

        $beforeStatus = '';
        $afterStatus = $item['status'];
        if(!empty($oceanCreative)){
            $beforeStatus = $oceanCreative['status'] ?? '';
        }

        // 状态发生变化
        if($beforeStatus != $afterStatus){
            $oceanCreativeLogService = new OceanCreativeLogService();
            $oceanCreativeLogService->create([
                'account_id' => $item['account_id'],
                'ad_id' => $item['ad_id'],
                'creative_id' => $item['creative_id'],
                'before_status' => $beforeStatus,
                'after_status' => $afterStatus,
            ]);
        }

        return true;
    }
}
