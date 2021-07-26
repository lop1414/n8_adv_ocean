<?php

namespace App\Models\Ocean;

class OceanCreativeLogModel extends OceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_creative_logs';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 关联巨量账户模型 一对一
     */
    public function ocean_account(){
        return $this->belongsTo('App\Models\Ocean\OceanAccountModel', 'account_id', 'account_id');
    }
}
