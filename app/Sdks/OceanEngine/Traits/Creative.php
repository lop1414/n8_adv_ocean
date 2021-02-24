<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;

trait Creative
{
    /**
     * @param array $accountIds
     * @param $accessToken
     * @param array $filtering
     * @param int $page
     * @param int $pageSize
     * @param array $param
     * @return mixed
     * 并发获取创意列表
     */
    public function multiGetCreativeList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10, $param = []){
        $url = $this->getUrl('2/creative/get/');
Functions::consoleDump($accountIds);
        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }
}
