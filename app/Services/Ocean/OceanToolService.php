<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;

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
     * @param $items
     * @return bool
     * @throws CustomException
     * 批量创建计划创意
     */
    public function batchCreateAdCreative($items){
        foreach($items as $item){
            $accountId = $item['account_id'] ?? '';
            $ad = $item['ad'] ?? [];
            $creative = $item['creative'] ?? [];
            if(empty($accountId) || empty($ad) || empty($creative)){
                throw new CustomException([
                    'code' => 'PARAM_ERROR',
                    'message' => '账户id、计划、创意参数均不能为空',
                    'data' => [
                        'items' => $items,
                        'item' => $item,
                    ],
                ]);
            }

            // 设置账户
            $account = $this->getAccount($accountId);
            $this->setAppId($account->app_id);
            $this->setAccountId($account->account_id);

            // 创建计划
            $ret = $this->createAd($ad);

            if(empty($ret['ad_id'])){
                throw new CustomException([
                    'code' => 'CREATE_AD_FAIL',
                    'message' => '创建广告计划失败',
                    'data' => [
                        'item' => $item,
                    ],
                    'log' => true,
                ]);
            }

            // 计划id
            $adId = $ret['ad_id'];

            // 创建创意
            $creative = array_merge($creative, ['ad_id' => $adId]);
            $this->createCreative($creative);
        }

        return true;
    }

    /**
     * @param $param
     * @return mixed
     * @throws CustomException
     * 创建计划
     */
    public function createAd($param){
        $ret = $this->forward('2/ad/create/', $param, 'POST');
        return $ret;
    }

    /**
     * @param $param
     * @return mixed
     * @throws CustomException
     * 创建创意
     */
    public function createCreative($param){
        $ret = $this->forward('2/creative/create_v2/', $param, 'POST');
        return $ret;
    }
}
