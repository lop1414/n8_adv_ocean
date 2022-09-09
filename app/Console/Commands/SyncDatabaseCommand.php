<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Tools\CustomException;
use App\Services\SyncDatabaseService;

class SyncDatabaseCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'sync_database  {--table=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步数据表';

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

        $lockKey = 'sync_database_'. $param['table'];

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
        $syncDatabaseService = new SyncDatabaseService();
        $syncDatabaseService->run($param);
        return true;
    }
}
