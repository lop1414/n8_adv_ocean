<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanAdService;

class OceanSyncAdCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_ad  {--create_date=} {--update_date=} {--account_ids=} {--status=} {--ids=} {--multi_chunk_size=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步头条广告计划';

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

        // id
        if(!empty($param['ids'])){
            $param['ids'] = explode(",", $param['ids']);
        }

        $oceanAdService = new OceanAdService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanAdService, 'sync'],
            'ocean_sync_ad',
            5400,
            $option,
            $param
        );
    }
}
