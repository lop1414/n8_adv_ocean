<?php

namespace App\Models\Ocean;

use App\Models\Ocean\Report\OceanCreativeReportModel;

class OceanCreativeModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_creatives';

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
        $creativeTable = (new OceanCreativeReportModel())->getTable();
        return $this->hasMany('App\Models\Ocean\Report\OceanCreativeReportModel', 'creative_id', 'id')
            ->whereBetween("{$creativeTable}.stat_datetime", ["{$today} 00:00:00", "{$today} 23:59:59"])
            ->groupBy('creative_id');
    }
}
