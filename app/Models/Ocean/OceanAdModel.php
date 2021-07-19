<?php

namespace App\Models\Ocean;

use App\Models\Ocean\Report\OceanCreativeReportModel;
use Illuminate\Support\Facades\DB;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * 关联创意报表模型 一对多
     */
    public function ocean_creative_reports(){
        $today = date('Y-m-d', TIMESTAMP);
$today = '2021-02-03';
        $creativeTable = (new OceanCreativeReportModel())->getTable();
        return $this->hasMany('App\Models\Ocean\Report\OceanCreativeReportModel', 'ad_id', 'id')
            ->whereBetween("{$creativeTable}.stat_datetime", ["{$today} 00:00:00", "{$today} 23:59:59"])
            ->groupBy('ad_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * 关联计划扩展模型 一对一
     */
    public function ocean_ad_extends(){
        return $this->hasOne('App\Models\Ocean\OceanAdExtendModel', 'ad_id', 'id');
    }
}
