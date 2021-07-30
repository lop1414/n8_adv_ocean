<?php

namespace App\Services\Ocean\Report;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\Ocean\Report\OceanMaterialReportModel;
use Illuminate\Support\Facades\DB;

class OceanMaterialReportService extends OceanReportService
{
    /**
     * OceanAccountReportService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);

        $this->modelClass = OceanMaterialReportModel::class;
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
        return $this->sdk->multiGetMaterialReportList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }

    /**
     * @return array
     * 获取过滤条件
     */
    protected function getFiltering(){
        return [];
    }

    /**
     * @return array
     * 获取分组
     */
    protected function getGroupBy(){
        return ["STAT_GROUP_BY_AD_ID", "STAT_GROUP_BY_MATERIAL_ID"];
    }

    /**
     * @param $res
     * @return array|void
     * 并发获取分页列表后置处理
     */
    public function multiGetPageListAfter($res){
        $ret = [];
        foreach($res as $k => $v){
            $tmp = [];
            $param = json_decode($v['req']['param'], true);
            foreach($v['data']['list'] as $vv){
                $vv['metrics']['stat_datetime'] = $param['start_date'] .' 00:00:00';
                $tmp[] = array_merge($vv['metrics'], $vv['dimensions']);
            }
            $v['data']['list'] = $tmp;
            $ret[$k] = $v;
        }

        return $ret;
    }
}
