<?php

namespace App\Models\Ocean;

class OceanAccountFundModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_account_funds';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'account_id';

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
    public function getBalanceAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setBalanceAttribute($value){
        $this->attributes['balance'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getValidBalanceAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setValidBalanceAttribute($value){
        $this->attributes['valid_balance'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getCashAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setCashAttribute($value){
        $this->attributes['cash'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getValidCashAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setValidCashAttribute($value){
        $this->attributes['valid_cash'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getGrantAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setGrantAttribute($value){
        $this->attributes['grant'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getValidGrantAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setValidGrantAttribute($value){
        $this->attributes['valid_grant'] = $value * 100;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 关联巨量账户模型 一对一
     */
    public function ocean_account(){
        return $this->belongsTo('App\Models\Ocean\OceanAccountModel', 'account_id', 'account_id');
    }
}
