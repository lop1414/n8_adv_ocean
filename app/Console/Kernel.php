<?php

namespace App\Console;

use App\Console\Commands\Ocean\Report\OceanSyncAccountReportCommand;
use App\Console\Commands\Ocean\OceanSyncAdCommand;
use App\Console\Commands\Ocean\OceanSyncAdConvertCommand;
use App\Console\Commands\Ocean\OceanSyncCityCommand;
use App\Console\Commands\Ocean\OceanSyncIndustryCommand;
use App\Console\Commands\Ocean\OceanSyncRegionCommand;
use App\Console\Commands\Ocean\OceanSyncVideoCommand;
use App\Console\Commands\SecondVersion\SyncJrttAccountCommand;
use App\Console\Commands\Ocean\OceanSyncCampaignCommand;
use App\Console\Commands\Task\TaskOceanAdCreativeCreateCommand;
use App\Console\Commands\Task\TaskOceanImageUploadCommand;
use App\Console\Commands\Task\TaskOceanSyncCommand;
use App\Console\Commands\Task\TaskOceanVideoUploadCommand;
use App\Console\Commands\Task\TaskReWaitingCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 二版
        SyncJrttAccountCommand::class,

        // 任务重执行
        TaskReWaitingCommand::class,

        // 巨量上传任务
        TaskOceanVideoUploadCommand::class,
        TaskOceanImageUploadCommand::class,

        // 巨量同步任务
        TaskOceanSyncCommand::class,

        // 巨量计划创意创建任务
        TaskOceanAdCreativeCreateCommand::class,

        // 巨量
        OceanSyncCampaignCommand::class,
        OceanSyncVideoCommand::class,
        OceanSyncRegionCommand::class,
        OceanSyncCityCommand::class,
        OceanSyncIndustryCommand::class,
        OceanSyncAdCommand::class,
        OceanSyncAdConvertCommand::class,
        OceanSyncAccountReportCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 二版
        $schedule->command('second_version:sync_jrtt_account')->cron('5 * * * *');

        // 任务重执行
        $schedule->command('task:re_waiting')->cron('* * * * *');

        // 巨量上传任务
        $schedule->command('task:ocean_image_upload')->cron('* * * * *');
        $schedule->command('task:ocean_video_upload')->cron('* * * * *');

        // 巨量同步任务
        $schedule->command('task:ocean_sync --type=video')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=campaign')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=ad')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=ad_convert')->cron('* * * * *');

        // 巨量计划创意创建任务
        $schedule->command('task:ocean_ad_creative_create')->cron('* * * * *');
    }
}
