<?php

namespace App\Enums\Ocean;

class AccountRoleEnum
{
    const ADVERTISER = 'ADVERTISER';
    const CUSTOMER_ADMIN = 'CUSTOMER_ADMIN';
    const AGENT = 'AGENT';
    const CHILD_AGENT = 'CHILD_AGENT';
    const CUSTOMER_OPERATOR = 'CUSTOMER_OPERATOR';

    /**
     * @var string
     * 名称
     */
    static public $name = '账户角色';

    /**
     * 列表
     */
    static public $list = [
        ['id' => self::ADVERTISER, 'name' => '广告主'],
        ['id' => self::CUSTOMER_ADMIN, 'name' => '管家'],
        ['id' => self::AGENT, 'name' => '代理商'],
        ['id' => self::CHILD_AGENT, 'name' => '二级代理商'],
        ['id' => self::CUSTOMER_OPERATOR, 'name' => '管家操作者'],
    ];
}
