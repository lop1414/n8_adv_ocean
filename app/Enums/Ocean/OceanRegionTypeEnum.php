<?php

namespace App\Enums\Ocean;

class OceanRegionTypeEnum
{
    const BUSINESS_DISTRICT = 'BUSINESS_DISTRICT';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量地域类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::BUSINESS_DISTRICT, 'name' => '商圈'],
    ];
}
