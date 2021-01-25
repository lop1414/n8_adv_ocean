<?php

namespace App\Models\Ocean;

class OceanAdConvertModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_ad_converts';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     * 主键数据类型
     */
    public $keyType = 'string';

    /**
     * @var bool
     * 是否自增
     */
    public $incrementing = false;

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getIgnoreParamsAttribute($value){
        return json_decode($value);
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setIgnoreParamsAttribute($value){
        $this->attributes['ignore_params'] = json_encode($value);
    }
}
