<?php

namespace App\Models\Ocean;

class OceanAudienceTempleteModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_audience_templetes';

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
    public function getAudienceAttribute($value){
        return json_decode($value);
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setAudienceAttribute($value){
        $this->attributes['audience'] = json_encode($value);
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getEstimateAttribute($value){
        return json_decode($value);
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setEstimateAttribute($value){
        $this->attributes['estimate'] = json_encode($value);
    }
}
