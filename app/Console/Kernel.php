<?php

namespace App\Console;

use App\Console\Commands\Ocean\OceanSyncAdCommand;
use App\Console\Commands\Ocean\OceanSyncCityCommand;
use App\Console\Commands\Ocean\OceanSyncIndustryCommand;
use App\Console\Commands\Ocean\OceanSyncRegionCommand;
use App\Console\Commands\Ocean\OceanSyncVideoCommand;
use App\Console\Commands\SecondVersion\SyncJrttAccountCommand;
use App\Console\Commands\Ocean\OceanSyncCampaignCommand;
use App\Console\Commands\Task\TaskOceanImageUploadCommand;
use App\Console\Commands\Task\TaskOceanSyncCommand;
use App\Console\Commands\Task\TaskOceanVideoUploadCommand;
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

        // 巨量上传任务
        TaskOceanVideoUploadCommand::class,
        TaskOceanImageUploadCommand::class,

        // 巨量同步任务
        TaskOceanSyncCommand::class,

        // 巨量
        OceanSyncCampaignCommand::class,
        OceanSyncVideoCommand::class,
        OceanSyncRegionCommand::class,
        OceanSyncCityCommand::class,
        OceanSyncIndustryCommand::class,
        OceanSyncAdCommand::class,
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

        // 巨量上传任务
        $schedule->command('task:ocean_image_upload')->cron('* * * * *');
        $schedule->command('task:ocean_video_upload')->cron('* * * * *');

        // 巨量同步任务
        $schedule->command('task:ocean_sync --type=video')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=campaign')->cron('* * * * *');
    }
}
