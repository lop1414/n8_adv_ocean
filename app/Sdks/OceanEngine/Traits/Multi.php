<?php

namespace App\Sdks\OceanEngine\Traits;

trait Multi
{
    /**
     * @param $url
     * @param array $accountIds
     * @param $accessToken
     * @param array $filtering
     * @param int $page
     * @param int $pageSize
     * @param array $param
     * @return mixed
     * 并发获取管家账户下分页列表
     */
    public function multiGetPageList($url, array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10, $param = []){
        $curlOptions = [];
        foreach($accountIds as $accountId){
            $p = array_merge([
                'advertiser_id' => $accountId,
                'filtering' => $filtering,
                'page' => $page,
                'page_size' => $pageSize,
            ], $param);

            $curlOptions[] = [
                'url' => $url,
                'param' => $p,
                'method' => 'GET',
                'header' => [
                    'Access-Token:'. $accessToken,
                    'Content-Type: application/json; charset=utf-8',
                ]
            ];
        }

        return $this->multiPublicRequest($curlOptions);
    }
}
