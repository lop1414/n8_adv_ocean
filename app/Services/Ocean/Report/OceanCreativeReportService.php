<?php

namespace App\Services\Ocean\Report;

use App\Models\Ocean\Report\OceanCreativeReportModel;

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
}
