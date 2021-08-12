<?php

namespace App\Models\Ocean;

class OceanMaterialCreativeModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_material_creatives';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var array
     * 批量更新忽略字段
     */
    protected $updateIgnoreFields = ['created_at'];
}
