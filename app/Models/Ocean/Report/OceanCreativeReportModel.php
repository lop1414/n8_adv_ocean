<?php

namespace App\Models\Ocean\Report;

use Illuminate\Support\Facades\DB;

class OceanCreativeReportModel extends OceanReportModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'ocean_creative_reports';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @param $query
     * @return mixed
     * 计算
     */
    public function scopeCompute($query){
        return $query->select(DB::raw("
                SUM(`cost`) `cost`,
                SUM(`click`) `click`,
                SUM(`show`) `show`,
                SUM(`convert`) `convert`,
                ROUND(SUM(`cost` / 100) / SUM(`show`) * 1000, 2) `show_cost`,
                ROUND(SUM(`cost` / 100) / SUM(`click`), 2) `click_cost`,
                CONCAT(ROUND(SUM(`click`) / SUM(`show`) * 100, 2), '%') `click_rate`,
                ROUND(SUM(`cost` / 100) / SUM(`convert`), 2) `convert_cost`,
                CONCAT(ROUND(SUM(`convert`) / SUM(`click`) * 100, 2), '%') `convert_rate`
            "));
    }
}
