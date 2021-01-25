<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait Industry
{
    /**
     * @param $industryType
     * @param int $level
     * @return mixed
     * 获取行业列表
     */
    public function getIndustryList($industryType = '', $level = 0){
        $url = $this->getUrl('2/tools/industry/get/');

        $param = [];

        if(!empty($industryType)){
            $param['type'] = $industryType;
        }

        if(!empty($level)){
            $param['level'] = $level;
        }

        $ret = $this->authRequest($url, $param, 'GET');

        return $ret['list'];
    }
}
