<?php

namespace App\Models\Ocean;

class OceanAdExtendModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_ad_extends';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'ad_id';

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
    public function getCallbackConvertTypesAttribute($value){
        return json_decode($value);
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setCallbackConvertTypesAttribute($value){
        $this->attributes['callback_convert_types'] = json_encode($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 关联巨量点击模型 一对一
     */
    public function convert_callback_strategy(){
        return $this->belongsTo('App\Common\Models\ConvertCallbackStrategyModel', 'convert_callback_strategy_id', 'id');
    }
}
