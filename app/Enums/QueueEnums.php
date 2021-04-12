<?php

namespace App\Enums;

class QueueEnums
{
    const CLICK = 'click';

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
        ['id' => self::CLICK, 'name' => '点击'],
    ];
}
