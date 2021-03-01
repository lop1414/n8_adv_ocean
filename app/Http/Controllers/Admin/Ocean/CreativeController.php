<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanCreativeModel;
use App\Services\Ocean\OceanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreativeController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCreativeModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        // 默认排序
        if(empty($this->curdService->requestData['order_by'])){
            $this->curdService->setOrderBy('creative_modify_time', 'desc');
        }

        $this->curdService->selectQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                // 时间范围
                $startDate = $this->curdService->requestData['start_date'] ?? date('Y-m-d');
                $endDate = $this->curdService->requestData['end_date'] ?? date('Y-m-d');
                Functions::dateCheck($startDate);
                Functions::dateCheck($endDate);

                $report = DB::table('ocean_creative_reports')
                    ->whereBetween('stat_datetime', ["{$startDate} 00:00:00", "{$endDate} 23:59:59"])
                    ->select(DB::raw("
                        creative_id,
                        ROUND(SUM(`cost` / 100), 2) `cost`,
                        SUM(`click`) `click`,
                        SUM(`show`) `show`,
                        SUM(`convert`) `convert`,
                        ROUND(SUM(`cost` / 100) / SUM(`show`) * 1000, 2) `show_cost`,
                        ROUND(SUM(`cost` / 100) / SUM(`click`), 2) `click_cost`,
                        ROUND(SUM(`click`) / SUM(`show`), 4) `click_rate`,
                        ROUND(SUM(`cost` / 100) / SUM(`convert`), 2) `convert_cost`,
                        ROUND(SUM(`convert`) / SUM(`click`), 4) `convert_rate`
                    "))
                    ->groupBy('creative_id');

                $builder->LeftjoinSub($report, 'report', function($join){
                    $join->on('ocean_creatives.id', '=', 'report.creative_id');
                });
            });
        });

        $this->curdService->selectQueryAfter(function(){

            //var_dump(DB::getQueryLog());
            foreach($this->curdService->responseData['list'] as $v){
                // 关联巨量账户
                $v->ocean_account;

                // 关联报表
                //$v->report = $v->ocean_creative_reports()->compute()->first();

                unset($v->extends);
            }
        });

    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        parent::readPrepare();

        $this->curdService->findAfter(function(){
            // 关联巨量账户
            $this->curdService->findData->ocean_account;

            $this->curdService->getModel()->expandExtendsField($this->curdService->findData);
        });
    }
}
