<?php

namespace App\Models\Ocean\Report;

use App\Models\Ocean\OceanModel;

class OceanReportModel extends OceanModel
{
    /**
     * @var bool
     * 关闭自动更新时间戳
     */
    public $timestamps= false;

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getExtendsAttribute($value){
        return json_decode($value);
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setExtendsAttribute($value){
        $this->attributes['extends'] = json_encode($value);
    }

    /**
     * @param $value
     * @return float|int
     * 属性访问器
     */
    public function getCostAttribute($value)
    {
        return $value / 100;
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setCostAttribute($value)
    {
        $this->attributes['cost'] = $value * 100;
    }
}
