<?php

namespace App\Enums\Ocean;

class OceanIndustryTypeEnum
{
    const ADVERTISER = 'ADVERTISER';
    const AGENT = 'AGENT';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量行业类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::ADVERTISER, 'name' => '广告3.0行业'],
        ['id' => self::AGENT, 'name' => '代理商行业'],
    ];
}
