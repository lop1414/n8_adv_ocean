<?php

namespace App\Models\Ocean;

use App\Common\Models\BaseModel;

class OceanCityModel extends BaseModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_citys';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var bool
     * 是否自增
     */
    public $incrementing = false;
}
