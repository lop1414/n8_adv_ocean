<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Services\SecondVersionService;

class SyncSecondVersionCostCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'sync_second_version_cost  {--date=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步二版消耗';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * @throws \App\Common\Tools\CustomException
     * 处理
     */
    public function handle(){
        $param = $this->option();

        $lockKey = 'sync_second_version_cost_'. $param['date'];

        $option = ['log' => true];
        $this->lockRun(
            [$this, 'exec'],
            $lockKey,
            43200,
            $option,
            $param
        );
    }

    /**
     * @param $param
     * @return bool
     * @throws CustomException
     * 执行
     */
    public function exec($param){
        // 获取日期范围
        list($startDate,$endDate) = Functions::getDateRange($param['date']);
        if($endDate > '2021-07-01'){
            $endDate = '2021-07-01';
            if($endDate < $startDate){
                echo "日期错误\n";
                return false;
            }
        }

        $secondVersionService = new SecondVersionService();
        $secondVersionService->syncJrttCreativeCost($startDate,$endDate);
        $secondVersionService->syncJrttMaterialCost($startDate,$endDate);
        return true;
    }
}
