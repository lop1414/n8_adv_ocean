<?php

namespace App\Enums\Ocean;

class OceanAdUpdateTypeEnum
{
    const STATUS = 'STATUS';
    const BUDGET = 'BUDGET';
    const BID = 'BID';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量计划更新类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::STATUS, 'name' => '状态'],
        ['id' => self::BUDGET, 'name' => '预算'],
        ['id' => self::BID, 'name' => '出价'],
    ];
}
