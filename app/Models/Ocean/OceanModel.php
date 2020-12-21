<?php

namespace App\Models\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Models\BaseModel;

class OceanModel extends BaseModel
{
    public function scopeWithPermission($query){
        $adminUserInfo = Functions::getGlobalData('admin_user_info');
        //if(!$adminUserInfo['is_admin']){
            $query->whereRaw("
                account_id IN (
                    SELECT account_id FROM ocean_accounts
                        WHERE admin_id = {$adminUserInfo['admin_user']['id']}
                )
            ");
        //}
    }
}
