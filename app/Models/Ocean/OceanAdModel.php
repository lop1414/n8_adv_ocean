<?php

namespace App\Models\Ocean;

class OceanAdModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_ads';

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
     * @var array
     * 批量更新忽略字段
     */
    protected $updateIgnoreFields = ['created_at'];

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


    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getBidAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setBidAttribute($value){
        $this->attributes['bid'] = $value * 100;
    }


    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getCpaBidAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setCpaBidAttribute($value){
        $this->attributes['cpa_bid'] = $value * 100;
    }


    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getDeepCpabidAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setDeepCpabidAttribute($value){
        $this->attributes['deep_cpabid'] = $value * 100;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 关联巨量账户模型 一对一
     */
    public function ocean_account(){
        return $this->belongsTo('App\Models\Ocean\OceanAccountModel', 'account_id', 'account_id');
    }
}
