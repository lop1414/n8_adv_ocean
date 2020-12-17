<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Task\TaskOceanSyncService;

class TaskOceanVideoSyncCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:ocean_video_sync';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量视频同步任务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * 处理
     */
    public function handle(){
        $taskOceanSyncService = new TaskOceanSyncService(OceanSyncTypeEnum::VIDEO);
        $option = ['log' => true];
        $this->lockRun(
            [$taskOceanSyncService, 'run'],
            'task_ocean_video_sync',
            3600,
            $option
        );
    }
}
