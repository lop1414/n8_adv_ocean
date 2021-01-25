<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Services\Task\TaskOceanAdCreativeCreateService;

class TaskOceanAdCreativeCreateCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:ocean_ad_creative_create';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量计划创意创建任务';

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
        $taskOceanAdCreativeCreateService = new TaskOceanAdCreativeCreateService();
        $option = ['log' => true];
        $this->lockRun(
            [$taskOceanAdCreativeCreateService, 'run'],
            'task_ocean_ad_creative_create',
            3600,
            $option
        );
    }
}
