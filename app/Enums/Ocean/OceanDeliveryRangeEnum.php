<?php

namespace App\Enums\Ocean;

class OceanDeliveryRangeEnum
{
    const DEFAULT = 'DEFAULT';
    const UNION = 'UNION';
    const UNIVERSAL = 'UNIVERSAL';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量推广类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::DEFAULT, 'name' => '默认'],
        ['id' => self::UNION, 'name' => '穿山甲联盟'],
        ['id' => self::UNIVERSAL, 'name' => '通投智选'],
    ];
}
