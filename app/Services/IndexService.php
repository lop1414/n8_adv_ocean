<?php

namespace App\Services;

use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\CenterApiService;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Enums\Ocean\OceanCreativeStatusEnum;
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

        $monthAgo = date('Y-m-d 00:00:00', strtotime("-1 months"));

        $orderBy = $param['order_by'] ?? 'cost';
        $orderType = $param['order_type'] ?? 'desc';

        $okStatus = OceanAdStatusEnum::AD_STATUS_DELIVERY_OK;
        $deleteStatus = OceanAdStatusEnum::AD_STATUS_DELETE;

        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        $where = ' 1 ';
        if(!$adminUserInfo['is_admin']){
            $childrenAdminIdsStr = implode(",", $adminUserInfo['children_admin_ids']);
            $where .= " AND ocean_accounts.admin_id IN ({$childrenAdminIdsStr})";
        }

        $result = [];

        // 在跑账户
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                count(DISTINCT ocean_ads.account_id) run_accounts
            FROM
                ocean_ads
            LEFT JOIN ocean_accounts ON ocean_ads.account_id = ocean_accounts.account_id
            WHERE
                {$where}
                AND ocean_ads.`status` = '{$okStatus}'
                AND ocean_ads.ad_modify_time >= '{$monthAgo}'
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

        // 在跑计划
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                count(DISTINCT ocean_ads.id) run_ads
            FROM
                ocean_ads
            LEFT JOIN ocean_accounts ON ocean_ads.account_id = ocean_accounts.account_id
            WHERE
                {$where}
                AND ocean_ads.`status` = '{$okStatus}'
                AND ocean_ads.ad_modify_time >= '{$monthAgo}'
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

        // 在跑创意
        $creativeOkStatus = OceanCreativeStatusEnum::CREATIVE_STATUS_DELIVERY_OK;
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                count(DISTINCT ocean_creatives.id) run_creatives
            FROM
                ocean_creatives
            LEFT JOIN ocean_ads ON ocean_creatives.ad_id = ocean_ads.id
            LEFT JOIN ocean_accounts ON ocean_creatives.account_id = ocean_accounts.account_id
            WHERE
                {$where}
                AND ocean_creatives.`status` = '{$creativeOkStatus}'
                AND ocean_ads.`status` = '{$okStatus}'
                AND (ocean_ads.ad_modify_time >= '{$monthAgo}' OR ocean_creatives.creative_modify_time >= '{$monthAgo}')
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
                run_creatives DESC
        ";
        $result['run_creatives'] = array_map('get_object_vars', DB::select($sql));

        // 在跑素材
        $creativeOkStatus = OceanCreativeStatusEnum::CREATIVE_STATUS_DELIVERY_OK;
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                count(DISTINCT ocean_material_creatives.n8_material_id) run_materials
            FROM
                ocean_creatives
            LEFT JOIN ocean_ads ON ocean_creatives.ad_id = ocean_ads.id
            LEFT JOIN ocean_accounts ON ocean_creatives.account_id = ocean_accounts.account_id
            LEFT JOIN ocean_material_creatives ON ocean_creatives.id = ocean_material_creatives.creative_id
            WHERE
                {$where}
                AND ocean_creatives.`status` = '{$creativeOkStatus}'
                AND ocean_ads.`status` = '{$okStatus}'
                AND (ocean_ads.ad_modify_time >= '{$monthAgo}' OR ocean_creatives.creative_modify_time >= '{$monthAgo}')
                AND ocean_material_creatives.n8_material_id > 0
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
                run_materials DESC
        ";
        $result['run_materials'] = array_map('get_object_vars', DB::select($sql));

        // 新建计划
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                COUNT(*) created_ads
            FROM
                ocean_ads
            LEFT JOIN ocean_accounts ON ocean_ads.account_id = ocean_accounts.account_id
            WHERE
                {$where}
                AND ocean_ads.ad_create_time BETWEEN '{$date} 00:00:00' AND '{$date} 23:59:59'
            /*AND ocean_ads.`status` != '{$deleteStatus}'*/
            GROUP BY
                ocean_accounts.admin_id
            ORDER BY
                created_ads DESC
        ";
        $result['created_ads'] = array_map('get_object_vars', DB::select($sql));

        // 新建创意
        $creativeDeleteStatus = OceanCreativeStatusEnum::CREATIVE_STATUS_DELETE;
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                COUNT(*) created_creatives
            FROM
                ocean_creatives
            LEFT JOIN ocean_accounts ON ocean_creatives.account_id = ocean_accounts.account_id
            WHERE
                {$where}
                AND ocean_creatives.creative_create_time BETWEEN '{$date} 00:00:00' AND '{$date} 23:59:59'
            /*AND ocean_creatives.`status` != '{$creativeDeleteStatus}'*/
            GROUP BY
                ocean_accounts.admin_id
            ORDER BY
                created_creatives DESC
        ";
        $result['created_creatives'] = array_map('get_object_vars', DB::select($sql));

        // 有消耗账户
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
            WHERE 
                {$where}
            GROUP BY
                ocean_accounts.admin_id
        ";
        $result['has_cost_accounts'] = array_map('get_object_vars', DB::select($sql));

        // 有消耗计划
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
            WHERE
                {$where}
            GROUP BY
                ocean_accounts.admin_id
        ";
        $result['has_cost_ads'] = array_map('get_object_vars', DB::select($sql));

        // 有消耗创意
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                COUNT(*) has_cost_creatives
            FROM
                (
                    SELECT
                        account_id,
                        creative_id
                    FROM
                        ocean_creative_reports
                    WHERE
                        stat_datetime BETWEEN '{$date} 00:00:00'
                    AND '{$date} 23:59:59'
                    AND cost > 0
                    GROUP BY
                        account_id,
                        creative_id
                ) report
            LEFT JOIN ocean_accounts ON report.account_id = ocean_accounts.account_id
            WHERE
                {$where}
            GROUP BY
                ocean_accounts.admin_id
        ";
        $result['has_cost_creatives'] = array_map('get_object_vars', DB::select($sql));

        // 有消耗素材
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                COUNT(DISTINCT ocean_material_creatives.n8_material_id) has_cost_materials
            FROM
                (
                    SELECT
                        account_id,
                        creative_id
                    FROM
                        ocean_creative_reports
                    WHERE
                        stat_datetime BETWEEN '{$date} 00:00:00'
                    AND '{$date} 23:59:59'
                    AND cost > 0
                    GROUP BY
                        account_id,
                        creative_id
                ) report
            LEFT JOIN ocean_material_creatives ON report.creative_id = ocean_material_creatives.creative_id
            LEFT JOIN ocean_accounts ON report.account_id = ocean_accounts.account_id
            WHERE 
                {$where}
                AND ocean_material_creatives.n8_material_id > 0
            GROUP BY
                ocean_accounts.admin_id
        ";
        $result['has_cost_materials'] = array_map('get_object_vars', DB::select($sql));

        // 消耗
        $sql = "
            SELECT
                ocean_accounts.admin_id,
                SUM(report.cost) cost
            FROM
                (
                    SELECT
                        account_id,
                        SUM(cost) cost
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
            WHERE 
                {$where}
            GROUP BY
                ocean_accounts.admin_id
        ";
        $result['cost'] = array_map('get_object_vars', DB::select($sql));

        // 管理员映射
        $centerApiService = new CenterApiService();
        $adminUsers = $centerApiService->apiGetAdminUsers();
        $adminUserMap = array_column($adminUsers, null, 'id');

        // 默认
        $default = [
            'run_accounts' => 0,
            'run_ads' => 0,
            'run_creatives' => 0,
            'run_materials' => 0,
            'created_ads' => 0,
            'created_creatives' => 0,
            'has_cost_accounts' => 0,
            'has_cost_ads' => 0,
            'has_cost_creatives' => 0,
            'has_cost_materials' => 0,
            'cost' => 0,
        ];

        // 映射
        $map = [];
        $total = $default;
        $total['admin_name'] = '汇总';
        foreach($result as $k => $v){
            foreach($v as $kk => $vv){
                // 单位
                if($k == 'cost'){
                    $vv[$k] = round($vv[$k] / 100, 2);
                }

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

        // 集合
        $collect = collect($data);

        // 排序
        if(strtoupper($orderType) == 'ASC'){
            $collect = $collect->sortBy($orderBy);
        }else{
            $collect = $collect->sortByDesc($orderBy);
        }

        $data = $collect->values()->toArray();

        return [
            'items' => $data,
            'total' => $total,
        ];
    }
}
