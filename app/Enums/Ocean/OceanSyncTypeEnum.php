<?php

namespace App\Enums\Ocean;

class OceanSyncTypeEnum
{
    const CAMPAIGN = 'CAMPAIGN';
    const AD = 'AD';
    const CREATIVE = 'CREATIVE';
    const VIDEO = 'VIDEO';
    const AD_CONVERT = 'AD_CONVERT';

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
        ['id' => self::CREATIVE, 'name' => '广告创意'],
        ['id' => self::VIDEO, 'name' => '视频'],
        ['id' => self::AD_CONVERT, 'name' => '转化目标'],
    ];
}
