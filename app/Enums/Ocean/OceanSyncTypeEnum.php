<?php

namespace App\Enums\Ocean;

class OceanSyncTypeEnum
{
    const CAMPAIGN = 'CAMPAIGN';
    const VIDEO = 'VIDEO';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量同步类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::CAMPAIGN, 'name' => '广告组'],
        ['id' => self::VIDEO, 'name' => '视频'],
    ];
}
