<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Task\TaskOceanAdCreativeCreateService;
use App\Services\Task\TaskOceanImageUploadService;
use App\Services\Task\TaskOceanSyncService;
use App\Services\Task\TaskOceanVideoUploadService;
use mysql_xdevapi\Exception;

class TaskReWaitingCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:re_waiting';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '任务重执行';

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
        $option = ['log' => true];
        $this->lockRun(
            [$this, 'reWaiting'],
            'task_re_waiting',
            43200,
            $option
        );
    }

    /**
     * @return bool
     * @throws CustomException
     * 重执行
     */
    public function reWaiting(){
        $taskOceanVideoUploadService = new TaskOceanVideoUploadService();
        $taskOceanVideoUploadService->reWaiting();

        $taskOceanImageUploadService = new TaskOceanImageUploadService();
        $taskOceanImageUploadService->reWaiting();

        $taskOceanSyncService = new TaskOceanSyncService(OceanSyncTypeEnum::VIDEO);
        $taskOceanSyncService->reWaiting();

        $taskOceanAdCreativeCreateService = new TaskOceanAdCreativeCreateService();
        $taskOceanAdCreativeCreateService->reWaiting();

        return true;
    }
}
