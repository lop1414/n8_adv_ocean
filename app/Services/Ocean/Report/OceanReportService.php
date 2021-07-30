<?php

namespace App\Services\Ocean\Report;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Services\Ocean\OceanService;
use Illuminate\Support\Facades\DB;

class OceanReportService extends OceanService
{
    /**
     * @var string
     * 模型类
     */
    public $modelClass;

    /**
     * OceanAccountReportService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option = []){
        ini_set('memory_limit', '2048M');

        $t = microtime(1);

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
        }

        // 并发分片大小
        if(!empty($option['multi_chunk_size'])){
            $multiChunkSize = min(intval($option['multi_chunk_size']), 8);
            $this->sdk->setMultiChunkSize($multiChunkSize);
        }

        // 在跑账户
        if(!empty($option['running'])){
            $runningAccountIds = $this->getRunningAccountIds();
            if(!empty($accountIds)){
                $accountIds = array_intersect($accountIds, $runningAccountIds);
            }else{
                $accountIds = $runningAccountIds;
            }
        }

        $dateRange = Functions::getDateRange($option['date']);
        $dateList = Functions::getDateListByRange($dateRange);

        // 删除
        if(!empty($option['delete'])){
            $between = [
                $dateRange[0] .' 00:00:00',
                $dateRange[1] .' 23:59:59',
            ];

            $model = new $this->modelClass();

            $builder = $model->whereBetween('stat_datetime', $between);

            if(!empty($accountIds)){
                $builder->whereIn('account_id', $accountIds);
            }

            $builder->delete();
        }

        if(!empty($option['run_by_account_cost'])){
            // 处理广告账户id
            $accountIds = $this->runByAccountCost($accountIds);
        }

        // 获取子账户组
        $accountGroup = $this->getSubAccountGroup($accountIds);

        $filtering = $this->getFiltering();

        foreach($dateList as $date){
            $param = [
                'start_date' => $date,
                'end_date' => $date,
                'time_granularity' => 'STAT_TIME_GRANULARITY_HOURLY',
            ];

            if(!empty($this->getGroupBy())){
                $param['group_by'] = $this->getGroupBy();
            }

            $pageSize = 200;
            foreach($accountGroup as $g){
                $items = $this->multiGetPageList($g, $filtering, $pageSize, $param);

                Functions::consoleDump('count:'. count($items));

                $cost = 0;

                // 保存
                $data = [];
                foreach($items as $item) {
                    $cost += $item['cost'];

                    if(!$this->itemValid($item)){
                        continue;
                    }

                    $item['cost'] *= 100;
                    $item['extends'] = json_encode($item);

                    $data[] = $item;
                }

                // 批量保存
                $this->batchSave($data);

                Functions::consoleDump('cost:'. $cost);
            }
        }

        $t = microtime(1) - $t;
        Functions::consoleDump($t);

        return true;
    }

    /**
     * @param $item
     * @return bool
     * 校验
     */
    protected function itemValid($item){
        $valid = true;

        if(
            empty($item['cost']) &&
            empty($item['show']) &&
            empty($item['click']) &&
            empty($item['convert'])
        ){
            $valid = false;
        }

        return $valid;
    }

    /**
     * @return array
     * 获取分组
     */
    protected function getGroupBy(){
        return ['STAT_GROUP_BY_FIELD_ID', 'STAT_GROUP_BY_FIELD_STAT_TIME'];
    }

    /**
     * @return array
     * 获取过滤条件
     */
    protected function getFiltering(){
        return [];
    }

    /**
     * @param $accountIds
     * @return mixed
     * 按账户消耗执行
     */
    protected function runByAccountCost($accountIds){
        return $accountIds;
    }

    /**
     * @param $data
     * @return bool
     * 批量保存
     */
    public function batchSave($data){
        $model = new $this->modelClass();
        $model->chunkInsertOrUpdate($data);
        return true;
    }

    /**
     * @param string $date
     * @return mixed
     * @throws CustomException
     * 按日期获取账户报表
     */
    public function getAccountReportByDate($date = 'today'){
        $date = Functions::getDate($date);
        Functions::dateCheck($date);

        $model = new $this->modelClass();
        $report = $model->whereBetween('stat_datetime', ["{$date} 00:00:00", "{$date} 23:59:59"])
            ->groupBy('account_id')
            ->orderBy('cost', 'DESC')
            ->select(DB::raw("account_id, SUM(cost) cost"))
            ->get();

        return $report;
    }
}
