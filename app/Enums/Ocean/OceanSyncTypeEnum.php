<?php

namespace App\Enums\Ocean;

class OceanSyncTypeEnum
{
    const CAMPAIGN = 'CAMPAIGN';
    const AD = 'AD';
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
        ['id' => self::AD, 'name' => '广告计划'],
        ['id' => self::VIDEO, 'name' => '视频'],
    ];
}
