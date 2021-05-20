<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Services\Task\TaskOceanAdUpdateService;

class TaskOceanAdUpdateCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:ocean_ad_update';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量计划更新任务';

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
        $taskOceanAdUpdateService = new TaskOceanAdUpdateService();
        $option = ['log' => true];
        $this->lockRun(
            [$taskOceanAdUpdateService, 'run'],
            'task_ocean_ad_update',
            43200,
            $option
        );
    }
}
