<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\CenterApiService;
use App\Enums\Ocean\OceanAdStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexService extends BaseService
{
    /**
     * constructor.
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * @param $param
     * @return array
     * @throws \App\Common\Tools\CustomException
     * 获取投放监控
     */
    public function getAdDashboard($param){
        $date = $param['date'] ?? date('Y-m-d');
        $okStatus = OceanAdStatusEnum::AD_STATUS_DELIVERY_OK;
        $deleteStatus = OceanAdStatusEnum::AD_STATUS_DELETE;

        $result = [];

        $sql = "
            SELECT
                ocean_accounts.admin_id,
                count(DISTINCT ocean_ads.id) run_ads
            FROM
                ocean_ads
            LEFT JOIN ocean_accounts ON ocean_ads.account_id = ocean_accounts.account_id
            WHERE
                ocean_ads.`status` = '{$okStatus}'
                AND ocean_accounts.admin_id > 0
                AND (
                    ocean_accounts.account_id IN (
                        SELECT
                            account_id
                        FROM
                            ocean_account_funds
                        WHERE
                            valid_balance > 0
                    )
                    OR ocean_accounts.account_id IN (
                        SELECT
                            account_id
                        FROM
                            ocean_account_reports
                        WHERE
                            stat_datetime BETWEEN '{$date} 00:00:00'
                        AND '{$date} 23:59:59'
                        AND cost > 0
                    )
                )
            GROUP BY
                ocean_accounts.admin_id
            ORDER BY
                run_ads DESC
        ";

        $result['run_ads'] = array_map('get_object_vars', DB::select($sql));

        $sql = "
            SELECT
                ocean_accounts.admin_id,
                count(DISTINCT ocean_ads.account_id) run_accounts
            FROM
                ocean_ads
            LEFT JOIN ocean_accounts ON ocean_ads.account_id = ocean_accounts.account_id
            WHERE
                ocean_ads.`status` = '{$okStatus}'
                AND ocean_accounts.admin_id > 0
                AND (
                    ocean_accounts.account_id IN (
                        SELECT
                            account_id
                        FROM
                            ocean_account_funds
                        WHERE
                            valid_balance > 0
                    )
                    OR ocean_accounts.account_id IN (
                        SELECT
                            account_id
                        FROM
                            ocean_account_reports
                        WHERE
                            stat_datetime BETWEEN '{$date} 00:00:00'
                        AND '{$date} 23:59:59'
                        AND cost > 0
                    )
                )
            GROUP BY ocean_accounts.admin_id
            ORDER BY
                run_accounts DESC
        ";
        $result['run_accounts'] = array_map('get_object_vars', DB::select($sql));

        $sql = "
            SELECT
                ocean_accounts.admin_id,
                COUNT(*) created_ads
            FROM
                ocean_ads
            LEFT JOIN ocean_accounts ON ocean_ads.account_id = ocean_accounts.account_id
            WHERE
                ocean_ads.ad_create_time BETWEEN '{$date} 00:00:00'
            AND '{$date} 23:59:59'
            /*AND ocean_ads.`status` != '{$deleteStatus}'*/
            GROUP BY
                ocean_accounts.admin_id
            ORDER BY
                created_ads DESC
        ";
        $result['created_ads'] = array_map('get_object_vars', DB::select($sql));

        $sql = "
            SELECT
                ocean_accounts.admin_id,
                COUNT(*) has_cost_accounts
            FROM
                (
                    SELECT
                        account_id
                    FROM
                        ocean_creative_reports
                    WHERE
                        stat_datetime BETWEEN '{$date} 00:00:00'
                    AND '{$date} 23:59:59'
                    AND cost > 0
                    GROUP BY
                        account_id
                ) report
            LEFT JOIN ocean_accounts ON report.account_id = ocean_accounts.account_id
            GROUP BY
                ocean_accounts.admin_id
        ";
        $result['has_cost_accounts'] = array_map('get_object_vars', DB::select($sql));

        $sql = "
            SELECT
                ocean_accounts.admin_id,
                COUNT(*) has_cost_ads
            FROM
                (
                    SELECT
                        account_id,
                        ad_id
                    FROM
                        ocean_creative_reports
                    WHERE
                        stat_datetime BETWEEN '{$date} 00:00:00'
                    AND '{$date} 23:59:59'
                    AND cost > 0
                    GROUP BY
                        account_id,
                        ad_id
                ) report
            LEFT JOIN ocean_accounts ON report.account_id = ocean_accounts.account_id
            GROUP BY
                ocean_accounts.admin_id
        ";
        $result['has_cost_ads'] = array_map('get_object_vars', DB::select($sql));

        // 管理员映射
        $centerApiService = new CenterApiService();
        $adminUsers = $centerApiService->apiGetAdminUsers();
        $adminUserMap = array_column($adminUsers, null, 'id');

        // 默认
        $default = [
            'run_ads' => 0,
            'run_accounts' => 0,
            'created_ads' => 0,
            'has_cost_accounts' => 0,
            'has_cost_ads' => 0,
        ];

        // 映射
        $map = [];
        $total = $default;
        $total['admin_id'] = '汇总';
        foreach($result as $k => $v){
            foreach($v as $kk => $vv){
                if(!isset($map[$vv['admin_id']])){
                    $map[$vv['admin_id']] = $default;
                }

                $map[$vv['admin_id']]['admin_id'] = $vv['admin_id'];
                $map[$vv['admin_id']]['admin_name'] = $adminUserMap[$vv['admin_id']]['name'] ?? '';

                $map[$vv['admin_id']][$k] = $vv[$k];
                $total[$k] += $vv[$k];
            }
        }

        $data = array_column($map, null);

        return [
            'items' => $data,
            'total' => $total,
        ];
    }
}
