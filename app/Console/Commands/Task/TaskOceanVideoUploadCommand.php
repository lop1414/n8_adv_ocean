<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Services\Task\TaskOceanVideoUploadService;

class TaskOceanVideoUploadCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:ocean_video_upload';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量视频上传任务';

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
        $oceanVideoUploadTaskService = new TaskOceanVideoUploadService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanVideoUploadTaskService, 'run'],
            'task_ocean_video_upload',
            43200,
            $option
        );
    }
}
