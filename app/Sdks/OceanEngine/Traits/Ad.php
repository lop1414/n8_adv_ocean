<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait Ad
{
    /**
     * @param array $accountIds
     * @param $accessToken
     * @param array $filtering
     * @param int $page
     * @param int $pageSize
     * @param array $param
     * @return mixed
     * 并发获取广告计划列表
     */
    public function multiGetAdList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10, $param = []){
        $url = $this->getUrl('2/ad/get/');

        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }

    /**
     * @param $accountId
     * @param array $adIds
     * @param $optStatus
     * @return mixed
     * 更新计划状态
     */
    public function updateAdStatus($accountId, array $adIds, $optStatus){
        $url = $this->getUrl('2/ad/update/status/');

        $param = [
            'advertiser_id' => $accountId,
            'ad_ids' => $adIds,
            'opt_status' => $optStatus,
        ];

        return $this->authRequest($url, $param, 'POST');
    }

    /**
     * @param $accountId
     * @param array $adIds
     * @param $budget
     * @return mixed
     * 更新计划预算
     */
    public function updateAdBudget($accountId, array $adIds, $budget){
        $url = $this->getUrl('2/ad/update/budget/');

        $data = [];
        foreach($adIds as $adId){
            $data[] = [
                'ad_id' => $adId,
                'budget' => $budget,
            ];
        }

        $param = [
            'advertiser_id' => $accountId,
            'data' => $data,
        ];

        return $this->authRequest($url, $param, 'POST');
    }

    /**
     * @param $accountId
     * @param array $adIds
     * @param $bid
     * @return mixed
     * 更新计划出价
     */
    public function updateAdBid($accountId, array $adIds, $bid){
        $url = $this->getUrl('2/ad/update/bid/');

        $data = [];
        foreach($adIds as $adId){
            $data[] = [
                'ad_id' => $adId,
                'bid' => $bid,
            ];
        }

        $param = [
            'advertiser_id' => $accountId,
            'data' => $data,
        ];

        return $this->authRequest($url, $param, 'POST');
    }
}
