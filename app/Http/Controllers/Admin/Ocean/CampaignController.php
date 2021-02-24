<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanCampaignModel;
use App\Services\Ocean\OceanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CampaignController extends OceanController
{
    protected $defaultOrderBy = 'cost';

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCampaignModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        $this->curdService->selectQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $date = date('Y-m-d');

                $report = DB::table('ocean_creative_reports')
                    ->whereBetween('stat_datetime', ["{$date} 00:00:00", "{$date} 23:59:59"])
                    ->select(DB::raw("
                        campaign_id,
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
                    ->groupBy('campaign_id');

                $builder->LeftjoinSub($report, 'report', function($join){
                    $join->on('ocean_campaigns.id', '=', 'report.campaign_id');
                });
            });
        });

        $this->curdService->selectQueryAfter(function(){
            foreach($this->curdService->responseData['list'] as $v){
                // 关联巨量账户
                $v->ocean_account;
            }
        });
    }

    /**
     * 列表(无分页)预处理
     */
    public function getPrepare(){
        parent::getPrepare();

        $this->curdService->getQueryAfter(function(){
            foreach($this->curdService->responseData as $v){
                // 关联巨量账户
                $v->ocean_account;
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
        });
    }
}
