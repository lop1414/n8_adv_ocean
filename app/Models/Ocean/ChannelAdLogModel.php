<?php

namespace App\Models\Ocean;

class ChannelAdLogModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'channel_ad_logs';

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
}
