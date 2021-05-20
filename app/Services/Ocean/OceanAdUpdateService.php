<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanAdUpdateTypeEnum;

class OceanAdUpdateService extends OceanService
{
    /**
     * constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $item
     * @return bool
     * @throws CustomException
     * 更改
     */
    public function update($item){
        // 设置账户 access_token
        $this->setAppId($item['app_id']);
        $this->setAccountId($item['account_id']);
        $this->setAccessToken();

        if($item['ad_update_type'] == OceanAdUpdateTypeEnum::STATUS){
            $this->updateStatus($item);
        }elseif($item['ad_update_type'] == OceanAdUpdateTypeEnum::BUDGET){
            $this->updateBudget($item);
        }elseif($item['ad_update_type'] == OceanAdUpdateTypeEnum::BID){
            $this->updateBid($item);
        }else{
            throw new CustomException([
                'code' => 'PLEASE_WRITE_UPDATE_BY_OCEAN_AD_UPDATE_TYPE_CODE',
                'message' => '请书写按类型更改巨量计划代码',
            ]);
        }

        return true;
    }

    /**
     * @param $item
     * @return mixed
     * 更新状态
     */
    public function updateStatus($item){
        return $this->sdk->updateAdStatus($item['account_id'], [$item['ad_id']], $item['data']['opt_status']);
    }

    /**
     * @param $item
     * @return mixed
     * 更新预算
     */
    public function updateBudget($item){
        return $this->sdk->updateAdBudget($item['account_id'], [$item['ad_id']], $item['data']['budget']);
    }

    /**
     * @param $item
     * @return mixed
     * 更新出价
     */
    public function updateBid($item){
        return $this->sdk->updateAdBid($item['account_id'], [$item['ad_id']], $item['data']['bid']);
    }
}
