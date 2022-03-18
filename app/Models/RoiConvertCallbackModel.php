<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class RoiConvertCallbackModel extends BaseModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'roi_convert_callbacks';


    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'convert_callback_id';


    /**
     * @var bool
     * 关闭自动更新时间戳
     */
    public $timestamps= false;



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
    public function getFailDataAttribute($value){
        return json_decode($value);
    }


    /**
     * 属性修饰器
     * @param $value
     */
    public function setFailDataAttribute($value){
        $this->attributes['fail_data'] = json_encode($value);
    }


}
