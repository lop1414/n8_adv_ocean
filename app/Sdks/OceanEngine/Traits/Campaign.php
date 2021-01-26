<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait Campaign
{
    /**
     * @param $accountId
     * @param array $filtering
     * @param $page
     * @param $pageSize
     * @return mixed
     * 获取广告组列表
     */
    public function getCampaignList($accountId, $filtering = [], $page = 1, $pageSize = 10){
        $url = $this->getUrl('2/campaign/get/');

        $param = [
            'advertiser_id' => $accountId,
            'filtering' => $filtering,
            'page' => $page,
            'page_size' =>$pageSize,
        ];

        return $this->authRequest($url, $param, 'GET');
    }

    /**
     * @param array $accountIds
     * @param $accessToken
     * @param array $filtering
     * @param int $page
     * @param int $pageSize
     * @param array $param
     * @return mixed
     * 并发获取广告组列表
     */
    public function multiGetCampaignList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10, $param = []){
        $url = $this->getUrl('2/campaign/get/');

        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }
}
