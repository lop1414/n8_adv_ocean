<?php

namespace App\Console\Commands\Task;

use App\Common\Console\BaseCommand;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Task\TaskOceanSyncService;

class TaskOceanCampaignSyncCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'task:ocean_campaign_sync';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量广告组同步任务';

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
        $param = $this->option();
        $taskOceanSyncService = new TaskOceanSyncService(OceanSyncTypeEnum::CAMPAIGN);
        $option = ['log' => true];
        $this->lockRun(
            [$taskOceanSyncService, 'run'],
            'task_ocean_campaign_sync',
            3600,
            $option,
            $param
        );
    }
}
