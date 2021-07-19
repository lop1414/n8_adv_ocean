<?php

namespace App\Services\Ocean\Report;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
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
     * @throws CustomException
     * 按账户消耗执行
     */
    protected function runByAccountCost($accountIds){
        $oceanAccountReportService = new OceanAccountReportService();
        $accountReportMap = $oceanAccountReportService->getAccountReportByDate()->pluck('cost', 'account_id');

        $creativeReportMap = $this->getAccountReportByDate()->pluck('cost', 'account_id');

        $creativeAccountIds = ['xx'];
        foreach($accountReportMap as $accountId => $cost){
            if(isset($creativeReportMap[$accountId]) && bcsub($creativeReportMap[$accountId] * 100, $cost * 100) >= 0){
                continue;
            }
            $creativeAccountIds[] = $accountId;
        }

        return $creativeAccountIds;
    }
}
