<?php

namespace App\Models\Ocean;

class OceanCreativeGroupTempleteModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_creative_group_templetes';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getDataAttribute($value){
        return json_decode($value);
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setDataAttribute($value){
        $this->attributes['data'] = json_encode($value);
    }
}
