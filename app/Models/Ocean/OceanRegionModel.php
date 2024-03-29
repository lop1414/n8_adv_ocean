<?php

namespace App\Models\Ocean;

class OceanRegionModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_regions';

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
