<?php

namespace App\Datas\Ocean;

use App\Common\Datas\BaseData;
use App\Models\Ocean\OceanAccountModel;

class OceanAccountData extends BaseData
{
    /**
     * @var array
     * 字段
     */
    protected $fields = [
        'id',
        'name',
        'company',
        'app_id',
        'account_id',
        'belong_platform'
    ];

    /**
     * @var array
     * 唯一键数组
     */
    protected $uniqueKeys = [
        ['app_id', 'account_id'],
    ];

    /**
     * @var int
     * 缓存有效期
     */
    protected $ttl = 60;

    /**
     * @var bool
     * 缓存开关
     */
    protected $cacheSwitch = false;

    /**
     * constructor.
     */
    public function __construct(){
        parent::__construct(OceanAccountModel::class);
    }
}
