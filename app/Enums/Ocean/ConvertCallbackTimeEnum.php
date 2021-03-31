<?php

namespace App\Enums\Ocean;

class ConvertCallbackTimeEnum
{
    const NEVER = 'NEVER';
    const TODAY = 'TODAY';
    const HOUR_6 = 'HOUR_6';
    const HOUR_12 = 'HOUR_12';
    const HOUR_24 = 'HOUR_24';
    const HOUR_36 = 'HOUR_36';
    const HOUR_48 = 'HOUR_48';
    const HOUR_72 = 'HOUR_72';

    /**
     * @var string
     * 名称
     */
    static public $name = '转化回传时间';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::NEVER, 'name' => '从不'],
        ['id' => self::TODAY, 'name' => '当天'],
        ['id' => self::HOUR_6, 'name' => '6小时'],
        ['id' => self::HOUR_12, 'name' => '12小时'],
        ['id' => self::HOUR_24, 'name' => '24小时'],
        ['id' => self::HOUR_36, 'name' => '36小时'],
        ['id' => self::HOUR_48, 'name' => '48小时'],
        ['id' => self::HOUR_72, 'name' => '72小时'],
    ];
}
