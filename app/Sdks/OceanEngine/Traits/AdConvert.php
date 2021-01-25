<?php

namespace App\Sdks\OceanEngine\Traits;

trait AdConvert
{
    /**
     * @param array $accountIds
     * @param $accessToken
     * @param array $filtering
     * @param int $page
     * @param int $pageSize
     * @return mixed
     * 并发获取转化目标列表
     */
    public function multiGetAdConvertList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10){
        $url = $this->getUrl('2/tools/adv_convert/select/');

        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize);
    }

    /**
     * @param $accountId
     * @param $adConvertid
     * @return mixed
     * 转化详情
     */
    public function readAdConvert($accountId, $adConvertid){
        $url = $this->getUrl('2/tools/ad_convert/read/');

        $param = [
            'advertiser_id' => $accountId,
            'convert_id' => $adConvertid,
        ];

        return $this->authRequest($url, $param, 'GET');
    }
}
