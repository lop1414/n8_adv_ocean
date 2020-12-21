<?php

namespace App\Models\Ocean;

class OceanCampaignModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_campaigns';

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
    public function getBudgetAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setBudgetAttribute($value){
        $this->attributes['budget'] = $value * 100;
    }
}
