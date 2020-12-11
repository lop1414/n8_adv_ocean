<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
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
     * @throws CustomException
     * 同步
     */
    public function syncCampaign($option = []){
        ini_set('memory_limit', '2048M');

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = explode(",", $option['account_ids']);
        }

        $filtering = [];
        if(!empty($option['date'])){
            $filtering['campaign_create_time'] = Functions::getDate($option['date']);
        }

        $accountGroup = $this->getSubAccountGroup($accountIds);

        $t = microtime(1);

        $pageSize = 100;
        $campaigns = [];
        foreach($accountGroup as $pid => $g){
            $tmp = $this->multiGetCampaignList($g, $filtering, $pageSize);
            $campaigns = array_merge($campaigns, $tmp);

            // 保存
            foreach($campaigns as $campaign) {
                $this->saveCampaign($campaign);
            }
        }

        $t = microtime(1) - $t;
        var_dump($t);
    }

    /**
     * @param $campaign
     * @return bool
     * 保存
     */
    public function saveCampaign($campaign){
        $oceanCampaignModel = new OceanCampaignModel();
        $oceanCampaign = $oceanCampaignModel->where('campaign_id', $campaign['id'])->first();

        if(empty($oceanCampaign)){
            $oceanCampaign = new OceanCampaignModel();
        }

        $oceanCampaign->account_id = $campaign['advertiser_id'];
        $oceanCampaign->campaign_id = $campaign['id'];
        $oceanCampaign->name = $campaign['name'];
        $oceanCampaign->budget = $campaign['budget'];
        $oceanCampaign->budget_mode = $campaign['budget_mode'];
        $oceanCampaign->landing_type = $campaign['landing_type'];
        $oceanCampaign->modify_time = $campaign['modify_time'];
        $oceanCampaign->status = $campaign['status'];
        $oceanCampaign->campaign_create_time = $campaign['campaign_create_time'];
        $oceanCampaign->campaign_modify_time = $campaign['campaign_modify_time'];
        $oceanCampaign->delivery_related_num = $campaign['delivery_related_num'];
        $oceanCampaign->delivery_mode = $campaign['delivery_mode'];

        return $oceanCampaign->save();
    }
}
