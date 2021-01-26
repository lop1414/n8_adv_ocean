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
     * @param $accounts
     * @param $filtering
     * @param $pageSize
     * @return array
     * @throws CustomException
     * 并发获取
     */
    public function multiGetAdList($accounts, $filtering, $pageSize){
        return $this->multiGetPageList('ad', $accounts, $filtering, $pageSize);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function syncAd($option = []){
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
        }else{
            $filtering['status'] = OceanAdStatusEnum::AD_STATUS_ALL;
        }

        // id
        if(!empty($option['ids'])){
            $filtering['ids'] = $option['ids'];
        }

        // 获取子账户组
        $accountGroup = $this->getSubAccountGroup($accountIds);

        $pageSize = 100;
        foreach($accountGroup as $pid => $g){
            $ads = $this->multiGetAdList($g, $filtering, $pageSize);
            Functions::consoleDump('count:'. count($ads));

            // 保存
            foreach($ads as $ad) {
                $this->saveAd($ad);
            }
        }

        $t = microtime(1) - $t;
        Functions::consoleDump($t);

        return true;
    }

    /**
     * @param $ad
     * @return mixed
     * @throws CustomException
     * 保存
     */
    public function saveAd($ad){
        $tmp = $ad;
        unset($tmp['id']);
        $ad['extends'] = $tmp;

        $where = ['id', '=', $ad['id']];
        $ret = Functions::saveChange(OceanAdModel::class, $where, $ad);
        return $ret;
    }
}
