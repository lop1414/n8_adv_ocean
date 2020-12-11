<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Skds\OceanEngine\Enums\OceanRegionLevelEnum;
use App\Skds\OceanEngine\Enums\OceanRegionTypeEnum;

trait Region
{
    /**
     * @param $regionType
     * @param string $regionLevel
     * @return mixed
     * @throws CustomException
     * 获取地域列表
     */
    public function getRegionList($regionType, $regionLevel = ''){
        $url = $this->getUrl('2/tools/region/get/');

        Functions::hasEnum(OceanRegionTypeEnum::class, $regionType);
        $param = [
            'region_type' => $regionType,
        ];

        if(!empty($regionLevel)){
            Functions::hasEnum(OceanRegionLevelEnum::class, $regionLevel);
            $param['region_type'] = $regionLevel;
        }

        $ret = $this->authRequest($url, $param, 'GET');

        return $ret['list'];
    }
}
