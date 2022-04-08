<?php

namespace App\Models\Ocean;

class OceanAccountFundDailyStatModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_account_fund_daily_stats';

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
    public function getCashCostAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setCashCostAttribute($value){
        $this->attributes['cash_cost'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getFrozenAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setFrozenAttribute($value){
        $this->attributes['frozen'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getIncomeAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setIncomeAttribute($value){
        $this->attributes['income'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getRewardCostAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setRewardCostAttribute($value){
        $this->attributes['reward_cost'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getSharedWalletCostAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setSharedWalletCostAttribute($value){
        $this->attributes['shared_wallet_cost'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getTransferInAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function settTransferInAttribute($value){
        $this->attributes['transfer_in'] = $value * 100;
    }

    /**
     * 属性访问器
     * @param $value
     * @return mixed
     */
    public function getTransferOutAttribute($value){
        return $value / 100;
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setTransferOutAttribute($value){
        $this->attributes['transfer_out'] = $value * 100;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 关联巨量账户模型 一对一
     */
    public function ocean_account(){
        return $this->belongsTo('App\Models\Ocean\OceanAccountModel', 'account_id', 'account_id');
    }
}
