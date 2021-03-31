<?php

namespace App\Enums\Ocean;

class OceanConvertCallbackStatusEnum
{
    const WAITING_CALLBACK = 'WAITING_CALLBACK';
    const DOT_NEED_CALLBACK = 'DOT_NEED_CALLBACK';
    const DOT_NEED_CALLBACK_BY_CALLBACK_TIME = 'DOT_NEED_CALLBACK_BY_CALLBACK_TIME';
    const DOT_NEED_CALLBACK_BY_CONVERT_TIMES = 'DOT_NEED_CALLBACK_BY_CONVERT_TIMES';
    const DOT_NEED_CALLBACK_BY_RATE = 'DOT_NEED_CALLBACK_BY_RATE';
    const DOT_NEED_CALLBACK_BY_STAT_DATA = 'DOT_NEED_CALLBACK_BY_STAT_DATA';
    const MACHINE_CALLBACK = 'MACHINE_CALLBACK';
    const MANUAL_CALLBACK = 'MANUAL_CALLBACK';
    const CALLBACK_FAIL = 'CALLBACK_FAIL';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量转化回传状态';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::WAITING_CALLBACK, 'name' => '待回传'],
        ['id' => self::DOT_NEED_CALLBACK, 'name' => '无需回传'],
        ['id' => self::DOT_NEED_CALLBACK_BY_CALLBACK_TIME, 'name' => '无需回传(转化时间扣除)'],
        ['id' => self::DOT_NEED_CALLBACK_BY_CONVERT_TIMES, 'name' => '无需回传(转化次数扣除)'],
        ['id' => self::DOT_NEED_CALLBACK_BY_RATE, 'name' => '无需回传(回传比例扣除)'],
        ['id' => self::DOT_NEED_CALLBACK_BY_STAT_DATA, 'name' => '无需回传(统计数据扣除)'],
        ['id' => self::MACHINE_CALLBACK, 'name' => '已回传(系统)'],
        ['id' => self::MANUAL_CALLBACK, 'name' => '已回传(手动)'],
        ['id' => self::CALLBACK_FAIL, 'name' => '回传失败'],
    ];
}
