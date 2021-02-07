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
    protected $signature = 'ocean:sync_ad  {--create_date=} {--update_date=} {--account_ids=} {--status=} {--ids=} {--multi_chunk_size=} {--key_suffix=}';

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

        // 锁 key
        $lockKey = 'ocean_sync_ad';

        // key 后缀
        if(!empty($param['key_suffix'])){
            $lockKey .= '_'. trim($param['key_suffix']);
        }

        $oceanAdService = new OceanAdService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanAdService, 'sync'],
            $lockKey,
            43200,
            $option,
            $param
        );
    }
}
