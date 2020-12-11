<?php

namespace App\Skds\OceanEngine\Enums;

class OceanRegionLevelEnum
{
    const REGION_LEVEL_PROVINCE = 'REGION_LEVEL_PROVINCE';
    const REGION_LEVEL_CITY = 'REGION_LEVEL_CITY';
    const REGION_LEVEL_DISTRICT = 'REGION_LEVEL_DISTRICT';
    const REGION_LEVEL_BUSINESS_DISTRICT = 'REGION_LEVEL_BUSINESS_DISTRICT';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量地域层级';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::REGION_LEVEL_PROVINCE, 'name' => '省级'],
        ['id' => self::REGION_LEVEL_CITY, 'name' => '市级'],
        ['id' => self::REGION_LEVEL_DISTRICT, 'name' => '区县级'],
        ['id' => self::REGION_LEVEL_BUSINESS_DISTRICT, 'name' => '商业区级'],
    ];
}
