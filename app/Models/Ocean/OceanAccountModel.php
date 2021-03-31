<?php

namespace App\Models\Ocean;

class OceanAccountModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_accounts';

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
    public function getExtendAttribute($value){
        return json_decode($value);
    }

    /**
     * 属性修饰器
     * @param $value
     */
    public function setExtendAttribute($value){
        $this->attributes['extend'] = json_encode($value);
    }


    /**
     * 关联应用模型   多对一
     */
    public function app(){
        return $this->belongsTo('App\Models\AppModel', 'app_id', 'app_id');
    }
}
