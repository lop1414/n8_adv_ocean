<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Services\TaskOceanVideoSyncService;
use App\Services\TaskOceanVideoUploadService;

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
        $taskOceanVideoSyncService = new TaskOceanVideoSyncService();
        $option = ['log' => true];
        $this->lockRun(
            [$taskOceanVideoSyncService, 'run'],
            'task_ocean_video_sync',
            3600,
            $option
        );
    }
}
