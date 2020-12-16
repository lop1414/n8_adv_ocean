<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait Region
{
    /**
     * @param $regionType
     * @param string $regionLevel
     * @return mixed
     * 获取地域列表
     */
    public function getRegionList($regionType, $regionLevel = ''){
        $url = $this->getUrl('2/tools/region/get/');

        $param = [
            'region_type' => $regionType,
        ];

        if(!empty($regionLevel)){
            $param['region_type'] = $regionLevel;
        }

        $ret = $this->authRequest($url, $param, 'GET');

        return $ret['list'];
    }
}
