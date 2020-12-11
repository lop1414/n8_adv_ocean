<?php

namespace App\Console;

use App\Console\Commands\SecondVersion\SyncJrttAccountCommand;
use App\Console\Commands\Ocean\SyncOceanCampaignCommand;
use App\Console\Commands\Task\OceanImageUploadCommand;
use App\Console\Commands\Task\OceanVideoSyncCommand;
use App\Console\Commands\Task\OceanVideoUploadCommand;
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
        SyncJrttAccountCommand::class,
        OceanVideoUploadCommand::class,
        OceanImageUploadCommand::class,
        SyncOceanCampaignCommand::class,
        OceanVideoSyncCommand::class,
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

        // 任务
        $schedule->command('task:ocean_image_upload')->cron('* * * * *');
        $schedule->command('task:ocean_video_upload')->cron('* * * * *');
        $schedule->command('task:ocean_video_sync')->cron('* * * * *');
    }
}
