<?php

namespace App\Enums\Ocean;

class OceanAdStatusEnum
{
    const AD_STATUS_DELIVERY_OK = 'AD_STATUS_DELIVERY_OK';
    const AD_STATUS_DISABLE = 'AD_STATUS_DISABLE';
    const AD_STATUS_AUDIT = 'AD_STATUS_AUDIT';
    const AD_STATUS_REAUDIT = 'AD_STATUS_REAUDIT';
    const AD_STATUS_DONE = 'AD_STATUS_DONE';
    const AD_STATUS_CREATE = 'AD_STATUS_CREATE';
    const AD_STATUS_AUDIT_DENY = 'AD_STATUS_AUDIT_DENY';
    const AD_STATUS_BALANCE_EXCEED = 'AD_STATUS_BALANCE_EXCEED';
    const AD_STATUS_BUDGET_EXCEED = 'AD_STATUS_BUDGET_EXCEED';
    const AD_STATUS_NOT_START = 'AD_STATUS_NOT_START';
    const AD_STATUS_NO_SCHEDULE = 'AD_STATUS_NO_SCHEDULE';
    const AD_STATUS_CAMPAIGN_DISABLE = 'AD_STATUS_CAMPAIGN_DISABLE';
    const AD_STATUS_CAMPAIGN_EXCEED = 'AD_STATUS_CAMPAIGN_EXCEED';
    const AD_STATUS_DELETE = 'AD_STATUS_DELETE';
    const AD_STATUS_ALL = 'AD_STATUS_ALL';
    const AD_STATUS_NOT_DELETE = 'AD_STATUS_NOT_DELETE';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量广告计划投放状态';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::AD_STATUS_DELIVERY_OK, 'name' => '投放中'],
        ['id' => self::AD_STATUS_DISABLE, 'name' => '计划暂停'],
        ['id' => self::AD_STATUS_AUDIT, 'name' => '新建审核中'],
        ['id' => self::AD_STATUS_REAUDIT, 'name' => '修改审核中'],
        ['id' => self::AD_STATUS_DONE, 'name' => '已完成（投放达到结束时间）'],
        ['id' => self::AD_STATUS_CREATE, 'name' => '计划新建'],
        ['id' => self::AD_STATUS_AUDIT_DENY, 'name' => '审核不通过'],
        ['id' => self::AD_STATUS_BALANCE_EXCEED, 'name' => '账户余额不足'],
        ['id' => self::AD_STATUS_BUDGET_EXCEED, 'name' => '超出预算'],
        ['id' => self::AD_STATUS_NOT_START, 'name' => '未到达投放时间'],
        ['id' => self::AD_STATUS_NO_SCHEDULE, 'name' => '不在投放时段'],
        ['id' => self::AD_STATUS_CAMPAIGN_DISABLE, 'name' => '已被广告组暂停'],
        ['id' => self::AD_STATUS_CAMPAIGN_EXCEED, 'name' => '广告组超出预算'],
        ['id' => self::AD_STATUS_DELETE, 'name' => '已删除'],
        ['id' => self::AD_STATUS_ALL, 'name' => '所有包含已删除'],
        ['id' => self::AD_STATUS_NOT_DELETE, 'name' => '所有不包含已删除（状态过滤默认值）'],
    ];
}
