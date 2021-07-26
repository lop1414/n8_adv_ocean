<?php

namespace App\Enums\Ocean;

class OceanCreativeStatusEnum
{
    const CREATIVE_STATUS_DELIVERY_OK = 'CREATIVE_STATUS_DELIVERY_OK';
    const CREATIVE_STATUS_NOT_START = 'CREATIVE_STATUS_NOT_START';
    const CREATIVE_STATUS_NO_SCHEDULE = 'CREATIVE_STATUS_NO_SCHEDULE';
    const CREATIVE_STATUS_DISABLE = 'CREATIVE_STATUS_DISABLE';
    const CREATIVE_STATUS_CAMPAIGN_DISABLE = 'CREATIVE_STATUS_CAMPAIGN_DISABLE';
    const CREATIVE_STATUS_CAMPAIGN_EXCEED = 'CREATIVE_STATUS_CAMPAIGN_EXCEED';
    const CREATIVE_STATUS_AUDIT = 'CREATIVE_STATUS_AUDIT';
    const CREATIVE_STATUS_REAUDIT = 'CREATIVE_STATUS_REAUDIT';
    const CREATIVE_STATUS_DELETE = 'CREATIVE_STATUS_DELETE';
    const CREATIVE_STATUS_DONE = 'CREATIVE_STATUS_DONE';
    const CREATIVE_STATUS_AD_DISABLE = 'CREATIVE_STATUS_AD_DISABLE';
    const CREATIVE_STATUS_AUDIT_DENY = 'CREATIVE_STATUS_AUDIT_DENY';
    const CREATIVE_STATUS_BALANCE_EXCEED = 'CREATIVE_STATUS_BALANCE_EXCEED';
    const CREATIVE_STATUS_BUDGET_EXCEED = 'CREATIVE_STATUS_BUDGET_EXCEED';
    const CREATIVE_STATUS_DATA_ERROR = 'CREATIVE_STATUS_DATA_ERROR';
    const CREATIVE_STATUS_PRE_ONLINE = 'CREATIVE_STATUS_PRE_ONLINE';
    const CREATIVE_STATUS_AD_AUDIT = 'CREATIVE_STATUS_AD_AUDIT';
    const CREATIVE_STATUS_AD_REAUDIT = 'CREATIVE_STATUS_AD_REAUDIT';
    const CREATIVE_STATUS_AD_AUDIT_DENY = 'CREATIVE_STATUS_AD_AUDIT_DENY';
    const CREATIVE_STATUS_ALL = 'CREATIVE_STATUS_ALL';
    const CREATIVE_STATUS_NOT_DELETE = 'CREATIVE_STATUS_NOT_DELETE';
    const CREATIVE_STATUS_ADVERTISER_BUDGET_EXCEED = 'CREATIVE_STATUS_ADVERTISER_BUDGET_EXCEED';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量广告创意投放状态';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::CREATIVE_STATUS_DELIVERY_OK, 'name' => '投放中'],
        ['id' => self::CREATIVE_STATUS_NOT_START, 'name' => '未到达投放时间'],
        ['id' => self::CREATIVE_STATUS_NO_SCHEDULE, 'name' => '不在投放时段'],
        ['id' => self::CREATIVE_STATUS_DISABLE, 'name' => '创意暂停'],
        ['id' => self::CREATIVE_STATUS_CAMPAIGN_DISABLE, 'name' => '已被广告组暂停'],
        ['id' => self::CREATIVE_STATUS_CAMPAIGN_EXCEED, 'name' => '广告组超出预算'],
        ['id' => self::CREATIVE_STATUS_AUDIT, 'name' => '新建审核中'],
        ['id' => self::CREATIVE_STATUS_REAUDIT, 'name' => '修改审核中'],
        ['id' => self::CREATIVE_STATUS_DELETE, 'name' => '已删除'],
        ['id' => self::CREATIVE_STATUS_DONE, 'name' => '已完成（投放达到结束时间）'],
        ['id' => self::CREATIVE_STATUS_AD_DISABLE, 'name' => '广告计划暂停'],
        ['id' => self::CREATIVE_STATUS_AUDIT_DENY, 'name' => '创意审核不通过'],
        ['id' => self::CREATIVE_STATUS_BALANCE_EXCEED, 'name' => '账户余额不足'],
        ['id' => self::CREATIVE_STATUS_BUDGET_EXCEED, 'name' => '超出预算'],
        ['id' => self::CREATIVE_STATUS_DATA_ERROR, 'name' => '数据错误（数据错误时返回，极少出现）'],
        ['id' => self::CREATIVE_STATUS_PRE_ONLINE, 'name' => '预上线'],
        ['id' => self::CREATIVE_STATUS_AD_AUDIT, 'name' => '广告计划新建审核中'],
        ['id' => self::CREATIVE_STATUS_AD_REAUDIT, 'name' => '广告计划修改审核中'],
        ['id' => self::CREATIVE_STATUS_AD_AUDIT_DENY, 'name' => '广告计划审核不通过'],
        ['id' => self::CREATIVE_STATUS_ALL, 'name' => '所有包含已删除'],
        ['id' => self::CREATIVE_STATUS_NOT_DELETE, 'name' => '所有不包含已删除（状态过滤默认值）'],
        ['id' => self::CREATIVE_STATUS_ADVERTISER_BUDGET_EXCEED, 'name' => '超出账户日预算'],
    ];
}
