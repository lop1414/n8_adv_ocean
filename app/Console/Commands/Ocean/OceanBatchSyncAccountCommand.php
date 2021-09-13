<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanAccountService;

class OceanBatchSyncAccountCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:batch_sync_account {--key_suffix=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '批量同步头条账户';

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

        // 锁 key
        $lockKey = 'ocean_batch_sync_account';

        // key 后缀
        if(!empty($param['key_suffix'])){
            $lockKey .= '_'. trim($param['key_suffix']);
        }

        $oceanAccountService = new OceanAccountService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanAccountService, 'batchSync'],
            $lockKey,
            43200,
            $option,
            $param
        );
    }
}
