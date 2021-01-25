<?php

namespace App\Enums\Ocean;

class OceanCampaignStatusEnum
{
    const CAMPAIGN_STATUS_ENABLE = 'CAMPAIGN_STATUS_ENABLE';
    const CAMPAIGN_STATUS_DISABLE = 'CAMPAIGN_STATUS_DISABLE';
    const CAMPAIGN_STATUS_DELETE	 = 'CAMPAIGN_STATUS_DELETE	';
    const CAMPAIGN_STATUS_ALL = 'CAMPAIGN_STATUS_ALL';
    const CAMPAIGN_STATUS_NOT_DELETE = 'CAMPAIGN_STATUS_NOT_DELETE';
    const CAMPAIGN_STATUS_ADVERTISER_BUDGET_EXCEED = 'CAMPAIGN_STATUS_ADVERTISER_BUDGET_EXCEED';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量广告组状态';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::CAMPAIGN_STATUS_ENABLE, 'name' => '启用'],
        ['id' => self::CAMPAIGN_STATUS_DISABLE, 'name' => '暂停'],
        ['id' => self::CAMPAIGN_STATUS_DELETE, 'name' => '删除'],
        ['id' => self::CAMPAIGN_STATUS_ALL, 'name' => '所有包含已删除'],
        ['id' => self::CAMPAIGN_STATUS_NOT_DELETE, 'name' => '所有不包含已删除（状态过滤默认值）'],
        ['id' => self::CAMPAIGN_STATUS_ADVERTISER_BUDGET_EXCEED, 'name' => '超出广告主日预算'],
    ];
}
