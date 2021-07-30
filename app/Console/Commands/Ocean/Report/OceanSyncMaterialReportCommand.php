<?php

namespace App\Console\Commands\Ocean\Report;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Services\Ocean\Report\OceanCreativeReportService;
use App\Services\Ocean\Report\OceanMaterialReportService;

class OceanSyncMaterialReportCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_material_report  {--date=} {--account_ids=} {--delete=} {--multi_chunk_size=} {--key_suffix=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步头条素材报表';

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

        // 账户
        if(!empty($param['account_ids'])){
            $param['account_ids'] = explode(",", $param['account_ids']);
        }

        // 锁 key
        $lockKey = 'ocean_sync_material_report';

        // key 日期
        if(!empty($param['date'])){
            $lockKey .= '_'. $param['date'];
        }

        // key 后缀
        if(!empty($param['key_suffix'])){
            $lockKey .= '_'. trim($param['key_suffix']);
        }

        $oceanMaterialReportService = new OceanMaterialReportService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanMaterialReportService, 'sync'],
            $lockKey,
            43200,
            $option,
            $param
        );
    }
}