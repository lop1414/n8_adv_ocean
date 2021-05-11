<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanCampaignStatusEnum;
use App\Models\Ocean\OceanCampaignModel;

class OceanCampaignService extends OceanService
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
        return $this->sdk->multiGetCampaignList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
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
            $filtering['campaign_create_time'] = Functions::getDate($option['create_date']);
        }

        // 状态
        if(!empty($option['status'])){
            $status = strtoupper($option['status']);
            Functions::hasEnum(OceanCampaignStatusEnum::class, $status);
            $filtering['status'] = $status;
        }else{
            $filtering['status'] = OceanCampaignStatusEnum::CAMPAIGN_STATUS_ALL;
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
            foreach($items as $item) {
                $this->save($item);
            }

            // 延迟
            usleep(2000000);
        }

        $t = microtime(1) - $t;
        Functions::consoleDump($t);

        return true;
    }

    /**
     * @param $item
     * @return mixed
     * @throws CustomException
     * 保存
     */
    public function save($item){
        $where = ['id', '=', $item['id']];
        $ret = Functions::saveChange(OceanCampaignModel::class, $where, $item);
        return $ret;
    }
}
