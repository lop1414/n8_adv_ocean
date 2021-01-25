<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Services\Task\TaskOceanImageUploadService;
use mysql_xdevapi\Exception;

class TaskOceanImageUploadCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:ocean_image_upload';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量图片上传任务';

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
        $taskOceanImageUploadService = new TaskOceanImageUploadService();
        $option = ['log' => true];
        $this->lockRun(
            [$taskOceanImageUploadService, 'run'],
            'task_ocean_image_upload',
            3600,
            $option
        );
    }
}
