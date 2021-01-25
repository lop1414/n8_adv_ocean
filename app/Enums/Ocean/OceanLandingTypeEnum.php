<?php

namespace App\Enums\Ocean;

class OceanLandingTypeEnum
{
    const EXTERNAL = 'EXTERNAL';
    const ARTICLE = 'ARTICLE';
    const GOODS = 'GOODS';
    const DPA = 'DPA';
    const STORE = 'STORE';
    const AWEME = 'AWEME';
    const SHOP = 'SHOP';
    const APP_ANDROID = 'APP_ANDROID';
    const APP_IOS = 'APP_IOS';

    /**
     * @var string
     * 名称
     */
    static public $name = '巨量推广类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        ['id' => self::EXTERNAL, 'name' => '落地页'],
        ['id' => self::ARTICLE, 'name' => '文章推广'],
        ['id' => self::GOODS, 'name' => '商品推广'],
        ['id' => self::DPA, 'name' => '商品目录'],
        ['id' => self::STORE, 'name' => '门店推广'],
        ['id' => self::AWEME, 'name' => '抖音号推广'],
        ['id' => self::SHOP, 'name' => '店铺直投'],
        ['id' => self::APP_ANDROID, 'name' => '应用下载-安卓'],
        ['id' => self::APP_IOS, 'name' => '应用下载-IOS'],
    ];
}
