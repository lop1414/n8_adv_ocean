<?php

namespace App\Services\Ocean\Report;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Services\Ocean\OceanService;

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

        // 获取子账户组
        $accountGroup = $this->getSubAccountGroup($accountIds);

        foreach($dateList as $date){
            $param = [
                'start_date' => $date,
                'end_date' => $date,
                'time_granularity' => 'STAT_TIME_GRANULARITY_HOURLY',
            ];

            $pageSize = 100;
            foreach($accountGroup as $g){
                $items = $this->multiGetPageList($g, [], $pageSize, $param);
                Functions::consoleDump('count:'. count($items));

                $cost = 0;

                // 保存
                $data = [];
                foreach($items as $item) {
                    $cost += $item['cost'];

                    if(
                        empty($item['cost']) &&
                        empty($item['show']) &&
                        empty($item['click']) &&
                        empty($item['convert'])
                    ){
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
     * @param $data
     * @return bool
     * 批量保存
     */
    public function batchSave($data){
        $model = new $this->modelClass();
        $model->chunkInsertOrUpdate($data);
        return true;
    }
}
