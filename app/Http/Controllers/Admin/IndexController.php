<?php

namespace App\Http\Controllers\Admin;

use App\Common\Controllers\Admin\AdminController;
use App\Enums\Ocean\OceanAdStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends AdminController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return mixed
     * 首页-监控台
     */
    public function adDashboard(Request $request){
        $requestData = $request->post();

        $date = $requestData['date'] ?? date('Y-m-d');
        $okStatus = OceanAdStatusEnum::AD_STATUS_DELIVERY_OK;
        $deleteStatus = OceanAdStatusEnum::AD_STATUS_DELETE;

        $sql = "SELECT
                ocean_accounts.admin_id,
                n8_center.admin_users.`name`,
                COUNT(
                    DISTINCT IF(ocean_ads.`status` = '{$okStatus}',ocean_accounts.account_id, NULL)
                ) run_accounts,
                COUNT(
                    IF(ocean_ads.`status` != '{$deleteStatus}', '*', NULL)
                ) created_ads
            FROM
                ocean_ads
            LEFT JOIN ocean_accounts ON ocean_ads.account_id = ocean_accounts.account_id
            LEFT JOIN n8_center.admin_users ON ocean_accounts.admin_id = n8_center.admin_users.id
            WHERE
                ocean_ads.ad_create_time BETWEEN '{$date} 00:00:00' AND '{$date} 23:59:59'
            AND ocean_accounts.admin_id > 0
            GROUP BY
                ocean_accounts.admin_id
            ORDER BY
                created_ads DESC,run_accounts DESC
        ";
        $result = DB::select($sql);

        return $this->success($result);
    }
}
