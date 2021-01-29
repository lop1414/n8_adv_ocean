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
        return $this->sdk->multiGetAdConvertList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
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
    public function sync($option = []){
        $t = microtime(1);

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
        }

        // 获取子账户组
        $accountGroup = $this->getSubAccountGroup($accountIds);

        $pageSize = 100;
        foreach($accountGroup as $g){
            $items = $this->multiGetPageList($g, [], $pageSize);
            Functions::consoleDump('count:'. count($items));

            // 保存
            foreach($items as $item){
                $account = $this->getAccount($item['advertiser_id']);

                $this->setAppId($account->app_id);
                $this->setAccountId($account->account_id);
                $item = array_merge($item, $this->readAdConvert($account->account_id, $item['id']));

                $this->save($item);
            }
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
        $ret = Functions::saveChange(OceanAdConvertModel::class, $where, $item);
        return $ret;
    }
}
