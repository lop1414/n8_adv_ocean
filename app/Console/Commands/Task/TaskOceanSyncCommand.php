<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Task\TaskOceanSyncService;

class TaskOceanSyncCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:ocean_sync {--type=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量同步任务';

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
        $type = strtoupper($this->option('type'));
        Functions::hasEnum(OceanSyncTypeEnum::class, $type);

        $taskOceanSyncService = new TaskOceanSyncService($type);
        $option = ['log' => true];
        $this->lockRun(
            [$taskOceanSyncService, 'run'],
            "task_ocean_sync_{$type}",
            3600,
            $option
        );
    }
}
