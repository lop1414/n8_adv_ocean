<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Models\Ocean\OceanAdModel;

class OceanAdService extends OceanService
{
    /**
     * OceanCampaignService constructor.
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
        return $this->sdk->multiGetAdList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
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
            $filtering['ad_create_time'] = Functions::getDate($option['create_date']);
        }

        // 更新日期
        if(!empty($option['update_date'])){
            $filtering['ad_modify_time'] = Functions::getDate($option['update_date']);
        }

        // 广告组id
        if(!empty($option['campaign_id'])){
            $filtering['campaign_id'] = Functions::getDate($option['campaign_id']);
        }

        // 状态
        if(!empty($option['status'])){
            $status = strtoupper($option['status']);
            Functions::hasEnum(OceanAdStatusEnum::class, $status);
            $filtering['status'] = $status;
        }

        // id
        if(!empty($option['ids'])){
            $filtering['ids'] = $option['ids'];
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
                $tmp = $item;
                unset($tmp['id']);
                $item['extends'] = json_encode($tmp);

                $datetime = date('Y-m-d H:i:s');

                $item['created_at'] = $datetime;
                $item['updated_at'] = $datetime;
                $data[] = $item;
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
        $oceanAdModel = new OceanAdModel();
        $oceanAdModel->chunkInsertOrUpdate($data, 50, $oceanAdModel->getTable(), $oceanAdModel->getTableColumnsWithPrimaryKey());
        return true;
    }
}
