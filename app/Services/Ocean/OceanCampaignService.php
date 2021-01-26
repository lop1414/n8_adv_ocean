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
     * @param $accounts
     * @param $filtering
     * @param $pageSize
     * @return array
     * @throws CustomException
     * 并发获取
     */
    public function multiGetCampaignList($accounts, $filtering, $pageSize){
        return $this->multiGetPageList('campaign', $accounts, $filtering, $pageSize);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function syncCampaign($option = []){
        ini_set('memory_limit', '2048M');

        $t = microtime(1);

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
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
        foreach($accountGroup as $pid => $g){
            $campaigns = $this->multiGetCampaignList($g, $filtering, $pageSize);
            Functions::consoleDump('count:'. count($campaigns));

            // 保存
            foreach($campaigns as $campaign) {
                $this->saveCampaign($campaign);
            }
        }

        $t = microtime(1) - $t;
        Functions::consoleDump($t);

        return true;
    }

    /**
     * @param $campaign
     * @return mixed
     * @throws CustomException
     * 保存
     */
    public function saveCampaign($campaign){
        $where = ['id', '=', $campaign['id']];
        $ret = Functions::saveChange(OceanCampaignModel::class, $where, $campaign);
        return $ret;
    }
}
