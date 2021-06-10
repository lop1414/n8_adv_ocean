<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class AppModel extends BaseModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'apps';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 隐藏字段
     *
     * @var array
     */
    protected $hidden = [
        'secret'
    ];


    /**
     * 关联角色模型   一对多
     */
    public function ocean_account(){
        return $this->hasMany('App\Models\Ocean\OceanAccountModel', 'app_id', 'app_id');
    }
}
