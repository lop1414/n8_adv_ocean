<?php

namespace App\Services\Ocean;

use App\Common\Enums\MaterialTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanCreativeStatusEnum;
use App\Models\Ocean\OceanCreativeLogModel;
use App\Models\Ocean\OceanMaterialCreativeModel;
use App\Models\Ocean\Report\OceanMaterialReportModel;
use Illuminate\Support\Facades\DB;

class OceanMaterialStatService extends OceanService
{
    /**
     * OceanMaterialService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $param
     * @return array
     * @throws CustomException
     * 多条
     */
    public function get($param){
        $this->validRule($param, [
            'material_type' => 'required',
            'n8_material_ids' => 'required|array',
        ]);

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $map = [];
        foreach($param['n8_material_ids'] as $n8MaterialId){
            $map[$n8MaterialId] =  $this->read($n8MaterialId, $param['material_type']);
        }

        return $map;
    }

    /**
     * @param $n8MaterialId
     * @param $materialType
     * @return array
     * @throws CustomException
     * 单条
     */
    protected function read($n8MaterialId, $materialType){
        if(empty($n8MaterialId)){
            throw new CustomException([
                'code' => 'N8_MATERIAL_ID_IS_EMPTY',
                'message' => 'n8素材id不能为空',
            ]);
        }

        $sql = "SELECT omc.material_id,omc.creative_id,omc.n8_material_id,
                oc.account_id,oc.ad_id,oc.status,oc.creative_create_time,
                oa.admin_id
            FROM ocean_material_creatives omc
            LEFT JOIN ocean_creatives oc ON omc.creative_id = oc.id
            LEFT JOIN ocean_accounts oa ON oa.account_id = oc.account_id
            WHERE omc.n8_material_id = {$n8MaterialId}
                 AND omc.material_type = '{$materialType}'
        ";
        $items = DB::select($sql);

        $day7 = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $day30 = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $today = date('Y-m-d 00:00:00', strtotime('today'));

        $materialId = '';
        $audit = $ok = $deny = $total = $creativeDay7 = $creativeDay30 = $creativeRunningToday = 0;
        $runningTodayAdminIds = [];
        foreach($items as $item){
            $materialId = $item->material_id;

            $originStatus = $item->status;
            if($item->status == OceanCreativeStatusEnum::CREATIVE_STATUS_DELETE){
                $status = $this->getCreativeBeforeStatus($item->creative_id, $item->status);
                if($status == OceanCreativeStatusEnum::CREATIVE_STATUS_AUDIT){
                    continue;
                }
            }else{
                $status = $item->status;
            }

            if(empty($status)){
                continue;
            }

            if($item->creative_create_time > $day7){
                $creativeDay7 += 1;
            }

            if($item->creative_create_time > $day30){
                $creativeDay30 += 1;
            }

            if(/*$item->creative_create_time > $today &&*/ $originStatus == OceanCreativeStatusEnum::CREATIVE_STATUS_DELIVERY_OK){
                $creativeRunningToday += 1;

                if(!empty($item->admin_id) && !in_array($item->admin_id, $runningTodayAdminIds)){
                    $runningTodayAdminIds[] = $item->admin_id;
                }
            }

            if($status == OceanCreativeStatusEnum::CREATIVE_STATUS_AUDIT){
                $audit += 1;
            }elseif($status == OceanCreativeStatusEnum::CREATIVE_STATUS_AUDIT_DENY){
                $deny += 1;
            }else{
                $ok += 1;
            }

            $total += 1;
        }

        $okRate = $total > 0 ? round($ok / $total, 4) * 100 : 0;

        return [
            'audit' => $audit,
            'deny' => $deny,
            'ok' => $ok,
            'total' => $total,
            'ok_rate' => $okRate .'%',
            'creative_day_7' => $creativeDay7,
            'creative_day_30' => $creativeDay30,
            'creative_running_today' => $creativeRunningToday,
            'report_day_7' => $this->getMaterialReport($materialId, $day7),
            'report_day_30' => $this->getMaterialReport($materialId, $day30),
            'running_today_admin_ids' => $runningTodayAdminIds,
            'running_today_admin' => count($runningTodayAdminIds),
        ];
    }

    /**
     * @param $creativeId
     * @param $status
     * @return string
     * 获取创意更改前状态
     */
    protected function getCreativeBeforeStatus($creativeId, $status){
        $oceanCreativeLogModel = new OceanCreativeLogModel();
        $oceanCreativeLog = $oceanCreativeLogModel->where('creative_id', $creativeId)
            ->where('after_status', $status)
            ->first();

        if(empty($oceanCreativeLog)){
            return '';
        }

        return $oceanCreativeLog->before_status;
    }

    /**
     * @param $materialId
     * @param $datetime
     * @return array
     * 获取素材报表
     */
    protected function getMaterialReport($materialId, $datetime){
        $oceanMaterialReportModel = new OceanMaterialReportModel();
        $oceanMaterialReports = $oceanMaterialReportModel->where('material_id', $materialId)
            ->where('stat_datetime', '>=', $datetime)
            ->get();

        $cost = $show = $click = $convert = 0;
        foreach($oceanMaterialReports as $oceanMaterialReport){
            $cost += $oceanMaterialReport->cost;
            $show += $oceanMaterialReport->show;
            $click += $oceanMaterialReport->click;
            $convert += $oceanMaterialReport->convert;
        }

        $clickRate = $show > 0 ? round($click / $show, 4) * 100 : 0;
        $convertRate = $click > 0 ? round($convert / $click, 4) * 100 : 0;
        $convertCost = $convert > 0 ? round($cost / $convert, 2) : 0;

        return [
            'cost' => round($cost, 2),
            'show' => $show,
            'click' => $click,
            'click_rate' => $clickRate .'%',
            'convert' => $convert,
            'convert_rate' => $convertRate .'%',
            'convert_cost' => $convertCost,
        ];
    }

    /**
     * @param $param
     * @return array
     * @throws CustomException
     * 最新
     */
    public function newest($param){
        $this->validRule($param, [
            'material_type' => 'required',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s',
        ]);

        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');

        $sql = "SELECT *
            FROM ocean_material_creatives omc
            WHERE n8_material_id > 0 AND (
                updated_at BETWEEN '{$param['start_time']}' AND '{$param['end_time']}'
                OR creative_id IN (
                    SELECT id FROM ocean_creatives
                        WHERE creative_modify_time BETWEEN '{$param['start_time']}' AND '{$param['end_time']}'
                )
            )
        ";
        $items = DB::select($sql);

        $n8MaterialIds = [];
        foreach($items as $item){
            $n8MaterialIds[$item->n8_material_id] = 1;
        }
        $n8MaterialIds = array_keys($n8MaterialIds);

        return $n8MaterialIds;
    }

    /**
     * @param $param
     * @return array
     * @throws CustomException
     * 保护素材
     */
    public function protect($param){
        $this->validRule($param, [
            'material_type' => 'required',
            'protect_cost_day_1' => 'required|integer',
            'protect_cost_day_2' => 'required|integer',
        ]);

        Functions::hasEnum(MaterialTypeEnums::class, $param['material_type']);

        $day1 = date('Y-m-d', strtotime('-1 days'));
        $day2 = date('Y-m-d', strtotime('-2 days'));
        $day3 = date('Y-m-d', strtotime('-3 days'));

        $hour = date('H');

        if($hour >= 10){
            $timeRangeDay1 = [
                "{$day1} 00:00:00",
                "{$day1} 23:59:59",
            ];

            $timeRangeDay2 = [
                "{$day2} 00:00:00",
                "{$day1} 23:59:59",
            ];
        }else{
            $timeRangeDay1 = [
                "{$day2} 00:00:00",
                "{$day2} 23:59:59",
            ];

            $timeRangeDay2 = [
                "{$day3} 00:00:00",
                "{$day2} 23:59:59",
            ];
        }


        $protectDay1 = $this->getProtectMaterialIds($param['material_type'], $param['protect_cost_day_1'], $timeRangeDay1);

        $protectDay2 = $this->getProtectMaterialIds($param['material_type'], $param['protect_cost_day_2'], $timeRangeDay2);

        $data = array_unique(array_merge($protectDay1, $protectDay2));

        return $data;
    }

    /**
     * @param $materialType
     * @param $protectCost
     * @param $timeRange
     * @return array
     * 获取保护素材ids
     */
    public function getProtectMaterialIds($materialType, $protectCost, $timeRange){
        // 元转分
        $protectCost *= 100;

        $sql = "SELECT
                ocean_material_creatives.n8_material_id,
                SUM(ocean_material_reports.cost) cost
            FROM
                ocean_material_reports
            LEFT JOIN ocean_material_creatives ON ocean_material_reports.material_id = ocean_material_creatives.material_id
            WHERE
                ocean_material_reports.stat_datetime BETWEEN '{$timeRange[0]}' AND '{$timeRange[1]}'
            AND ocean_material_creatives.material_type = '{$materialType}'
            AND ocean_material_creatives.n8_material_id > 0
            GROUP BY
                ocean_material_creatives.n8_material_id
            HAVING
                cost > {$protectCost}";
        $items = DB::select($sql);

        $n8MaterialIds = [];
        foreach($items as $item){
            $n8MaterialIds[] = $item->n8_material_id;
        }

        return $n8MaterialIds;
    }
}
