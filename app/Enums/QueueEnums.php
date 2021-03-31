<?php

namespace App\Enums;

class QueueEnums
{
    const OCEAN_CLICK = 'ocean_click';

    /**
     * @var string
     * 名称
     */
    static public $name = '队列枚举';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::OCEAN_CLICK, 'name' => '巨量点击'],
    ];
}
