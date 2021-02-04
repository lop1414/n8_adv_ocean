<?php

namespace App\Sdks\OceanEngine\Traits;

trait Report
{
    /**
     * @param array $accountIds
     * @param $accessToken
     * @param array $filtering
     * @param int $page
     * @param int $pageSize
     * @param array $param
     * @return mixed
     * 并发获取账户报表
     */
    public function multiGetAccountReportList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10, $param = []){
        $url = $this->getUrl('2/report/advertiser/get/');

        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }
}
