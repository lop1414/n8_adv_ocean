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
     * 单条
     */
    protected function read($n8MaterialId, $materialType){
        $sql = "SELECT omc.material_id,omc.creative_id,omc.n8_material_id,
                oc.account_id,oc.ad_id,oc.status,oc.creative_create_time
            FROM ocean_material_creatives omc
            LEFT JOIN ocean_creatives oc ON omc.creative_id = oc.id
            WHERE omc.n8_material_id = {$n8MaterialId}
                 AND omc.material_type = '{$materialType}'
        ";
        $items = DB::select($sql);

        $day7 = date('Y-m-d 00:00:00', strtotime('-7 days'));
        $day30 = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $today = date('Y-m-d 00:00:00', strtotime('today'));

        $materialId = '';
        $audit = $ok = $deny = $total = $creativeDay7 = $creativeDay30 = $creativeRunningToday = 0;
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

            if($item->creative_create_time > $today && $originStatus == OceanCreativeStatusEnum::CREATIVE_STATUS_DELIVERY_OK){
                $creativeRunningToday += 1;
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

        $reportDay7 = $this->getMaterialReport($materialId, $day7);

        $reportDay30 = $this->getMaterialReport($materialId, $day30);

        return [
            'audit' => $audit,
            'deny' => $deny,
            'ok' => $ok,
            'total' => $total,
            'ok_rate' => $okRate .'%',
            'creative_day_7' => $creativeDay7,
            'creative_day_30' => $creativeDay30,
            'creative_running_today' => $creativeRunningToday,
            'report_day_7' => $reportDay7,
            'report_day_30' => $reportDay30,
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
            WHERE updated_at BETWEEN '{$param['start_time']}' AND '{$param['end_time']}'
                OR creative_id IN (
                    SELECT id FROM ocean_creatives
                        WHERE creative_modify_time BETWEEN '{$param['start_time']}' AND '{$param['end_time']}'
                )
            AND n8_material_id > 0
        ";
        $items = DB::select($sql);

        $n8MaterialIds = [];
        foreach($items as $item){
            $n8MaterialIds[$item->n8_material_id] = 1;
        }
        $n8MaterialIds = array_keys($n8MaterialIds);

        return $n8MaterialIds;
    }
}
