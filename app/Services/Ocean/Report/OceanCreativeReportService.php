<?php

namespace App\Services\Ocean\Report;

use App\Common\Helpers\Functions;
use App\Models\Ocean\Report\OceanCreativeReportModel;
use Illuminate\Support\Facades\DB;

class OceanCreativeReportService extends OceanReportService
{
    /**
     * OceanAccountReportService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);

        $this->modelClass = OceanCreativeReportModel::class;
    }

    /**
     * @param $accountIds
     * @param $accessToken
     * @param $filtering
     * @param $page
     * @param $pageSize
     * @param array $param
     * @return mixed|void
     * sdk批量获取列表
     */
    public function sdkMultiGetList($accountIds, $accessToken, $filtering, $page, $pageSize, $param = []){
        return $this->sdk->multiGetCreativeReportList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }

    /**
     * @return array
     * 获取过滤条件
     */
    protected function getFiltering(){
        return ['status' => 'CREATIVE_STATUS_ALL'];
    }

    /**
     * @param $accountIds
     * @return array|mixed
     * @throws \App\Common\Tools\CustomException
     * 按账户消耗执行
     */
    protected function runByAccountCost($accountIds){
        $oceanAccountReportService = new OceanAccountReportService();
        $accountReportMap = $oceanAccountReportService->getAccountReportByDate()->pluck('cost', 'account_id');

        $creativeReportMap = $this->getAccountReportByDate()->pluck('cost', 'account_id');

        $creativeAccountIds = ['xx'];
        foreach($accountReportMap as $accountId => $cost){
            if(isset($creativeReportMap[$accountId]) && bcsub($creativeReportMap[$accountId], $cost) >= 0){
                continue;
            }
            $creativeAccountIds[] = $accountId;
        }

        return $creativeAccountIds;
    }

    public function getReportByHour($date, $hour, $groupBy){
        Functions::dateCheck($date);

        $dateRange = [
            "{$date} {$hour}:00:00",
            "{$date} {$hour}:59:59",
        ];

        $sql = "
            SELECT {$groupBy}, SUM(`cost`) `cost`, SUM(`show`) `show`, SUM(`click`) `click`, SUM(`convert`) `convert`
                FROM ocean_creative_reports
                WHERE stat_datetime BETWEEN '{$dateRange[0]}' AND '{$dateRange[1]}'
                GROUP BY {$groupBy}
        ";
        $reports = DB::select($sql);

        return $reports;
    }
}
