<?php

namespace App\Models\Task;

use App\Common\Models\SubTaskModel;

class TaskOceanModel extends SubTaskModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * 关联巨量账户模型 一对一
     */
    public function ocean_account(){
        return $this->belongsTo('App\Models\Ocean\OceanAccountModel', 'account_id', 'account_id');
    }
}
