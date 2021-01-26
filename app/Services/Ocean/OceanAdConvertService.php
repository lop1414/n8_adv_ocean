<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAdConvertModel;

class OceanAdConvertService extends OceanService
{
    /**
     * OceanAdConvertService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $res
     * @return array|void
     * 并发获取分页列表后置处理
     */
    public function multiGetPageListAfter($res){
        $ret = [];
        foreach($res as $k => $v){
            $v['data']['list'] = $v['data']['ad_convert_list'];
            $ret[$k] = $v;
        }

        return $ret;
    }

    /**
     * @param $accounts
     * @param $filtering
     * @param $pageSize
     * @return array
     * @throws CustomException
     * 并发获取
     */
    public function multiGetAdConvertList($accounts, $filtering, $pageSize){
        return $this->multiGetPageList('ad_convert', $accounts, $filtering, $pageSize);
    }

    /**
     * @param $accountId
     * @param $adConvertId
     * @return mixed
     * @throws CustomException
     * 详情
     */
    public function readAdConvert($accountId, $adConvertId){
        $this->setAccessToken();

        return $this->sdk->readAdConvert($accountId, $adConvertId);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function syncAdConvert($option = []){
        $t = microtime(1);

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
        }

        // 获取子账户组
        $accountGroup = $this->getSubAccountGroup($accountIds);

        $pageSize = 100;
        foreach($accountGroup as $pid => $g){
            $adConverts = $this->multiGetAdConvertList($g, [], $pageSize);
            Functions::consoleDump('count:'. count($adConverts));

            // 保存
            foreach($adConverts as $adConvert){
                $account = $this->getAccount($adConvert['advertiser_id']);

                $this->setAppId($account->app_id);
                $this->setAccountId($account->account_id);
                $adConvert = array_merge($adConvert, $this->readAdConvert($account->account_id, $adConvert['id']));

                $this->saveAdConvert($adConvert);
            }
        }

        $t = microtime(1) - $t;
        Functions::consoleDump($t);

        return true;
    }

    /**
     * @param $adConvert
     * @return mixed
     * @throws CustomException
     * 保存
     */
    public function saveAdConvert($adConvert){
        $where = ['id', '=', $adConvert['id']];
        $ret = Functions::saveChange(OceanAdConvertModel::class, $where, $adConvert);
        return $ret;
    }
}
