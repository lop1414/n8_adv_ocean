<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait Account
{
    /**
     * @var
     * 账户id
     */
    protected $accountId;

    /**
     * @param $accountId
     * @return bool
     * 设置账户id
     */
    public function setAccountId($accountId){
        $this->accountId = $accountId;
        return true;
    }

    /**
     * @return mixed
     * @throws CustomException
     * 获取账户id
     */
    public function getAccountId(){
        if(is_null($this->accountId)){
            throw new CustomException([
                'code' => 'NOT_FOUND_ACCOUNT_ID',
                'message' => '尚未设置账户id',
            ]);
        }
        return $this->accountId;
    }

    /**
     * @param array $accountIds
     * @return mixed
     * 获取账户信息
     */
    public function getAccountInfo(array $accountIds){
        $url = $this->getUrl('/2/advertiser/info/');

        $param = [
            'advertiser_ids' => $accountIds
        ];

        return $this->authRequest($url, $param, 'GET');
    }

    /**
     * @param array $accountIds
     * @return mixed
     * 获取账户公共信息
     */
    public function getAccountPublicInfo(array $accountIds){
        $url = $this->getUrl('/2/advertiser/public_info/');

        $param = [
            'advertiser_ids' => $accountIds
        ];

        return $this->authRequest($url, $param, 'GET');
    }

    /**
     * @param $accountId
     * @return mixed
     * 获取账户列表
     */
    public function getAccountList($accountId){
        $url = $this->getUrl('/2/majordomo/advertiser/select/');

        $param = [
            'advertiser_id' => $accountId
        ];

        return $this->authRequest($url, $param, 'GET');
    }

    /**
     * @param $accountId
     * @return mixed
     * 获取账户余额
     */
    public function getAccountFund($accountId){
        $url = $this->getUrl('/2/advertiser/fund/get/');

        $param = [
            'advertiser_id' => $accountId
        ];

        return $this->authRequest($url, $param, 'GET');
    }

    /**
     * @param $accountId
     * @param $startDate
     * @param $endDate
     * @return mixed
     * 获取账户日流水
     */
    public function getAccountFundDailyStat($accountId, $startDate, $endDate){
        $url = $this->getUrl('/2/advertiser/fund/daily_stat/');

        $param = [
            'advertiser_id' => $accountId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        return $this->authRequest($url, $param, 'GET');
    }
}
