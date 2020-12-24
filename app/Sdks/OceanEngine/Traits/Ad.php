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
     * @return mixed
     * 并发获取广告计划列表
     */
    public function multiGetAdList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10){
        $url = $this->getUrl('2/ad/get/');

        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize);
    }
}
