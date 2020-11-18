<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class OceanAccountModel extends BaseModel
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
     * 关联角色模型   多对一
     */
    public function ocean_account(){
        return $this->belongsTo('App\Models\AppModel', 'adv_app_id', 'id');
    }
}
