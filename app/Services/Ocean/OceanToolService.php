<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Enums\Ocean\OceanCampaignStatusEnum;
use App\Enums\Ocean\OceanSyncTypeEnum;

class OceanToolService extends OceanService
{
    /**
     * OceanAdConvertService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $syncType
     * @param $param
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($syncType, $param){
        if($syncType == OceanSyncTypeEnum::CAMPAIGN){
            // 广告组
            $oceanCampaignService = new OceanCampaignService($param['app_id']);

            $option = [
                'account_ids' => [$param['account_id']],
                'status' => OceanCampaignStatusEnum::CAMPAIGN_STATUS_ALL,
            ];

            if(!empty($param['campaign_id'])){
                $option['ids'] = [$param['campaign_id']];
            }elseif(!empty($param['campaign_ids'])){
                $option['ids'] = $param['campaign_ids'];
            }

            $oceanCampaignService->syncCampaign($option);
        }elseif($syncType == OceanSyncTypeEnum::AD){
            // 广告计划
            $oceanAdService = new OceanAdService($param['app_id']);

            $option = [
                'account_ids' => [$param['account_id']],
                'status' => OceanAdStatusEnum::AD_STATUS_ALL,
            ];

            if(!empty($param['ad_id'])){
                $option['ids'] = [$param['ad_id']];
            }elseif(!empty($param['ad_ids'])){
                $option['ids'] = $param['ad_ids'];
            }

            $oceanAdService->syncAd($option);
        }

        return true;
    }
}
