<?php

namespace App\Console;

use App\Common\Helpers\Functions;
use App\Common\Console\ConvertCallbackCommand;
use App\Console\Commands\Ocean\OceanSyncCreativeCommand;
use App\Console\Commands\Ocean\Report\OceanSyncAccountReportCommand;
use App\Console\Commands\Ocean\OceanSyncAdCommand;
use App\Console\Commands\Ocean\OceanSyncAdConvertCommand;
use App\Console\Commands\Ocean\OceanSyncCityCommand;
use App\Console\Commands\Ocean\OceanSyncIndustryCommand;
use App\Console\Commands\Ocean\OceanSyncRegionCommand;
use App\Console\Commands\Ocean\OceanSyncVideoCommand;
use App\Console\Commands\Ocean\Report\OceanSyncCreativeReportCommand;
use App\Common\Console\Queue\QueueClickCommand;
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
        OceanSyncCreativeCommand::class,
        OceanSyncAdConvertCommand::class,
        OceanSyncAccountReportCommand::class,
        OceanSyncCreativeReportCommand::class,
        ConvertCallbackCommand::class,

        // 队列
        QueueClickCommand::class,
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
        $schedule->command('task:ocean_sync --type=creative')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=ad_convert')->cron('* * * * *');

        // 巨量计划创意创建任务
        $schedule->command('task:ocean_ad_creative_create')->cron('* * * * *');

        // 队列
        $schedule->command('queue:click')->cron('* * * * *');

        // 转化上报
        $schedule->command('convert_callback')->cron('* * * * *');

        // 测试
        if(Functions::isStaging()){
            // 巨量广告组同步
            $schedule->command('ocean:sync_campaign --create_date=today --multi_chunk_size=1')->cron('*/15 * * * *');

            // 巨量计划同步
            $schedule->command('ocean:sync_ad --update_date=today')->cron('*/15 * * * *');
//            $schedule->command('ocean:sync_ad --key_suffix=yesterday')->cron('25-30 0 * * *');

            // 巨量转化跟踪同步
            $schedule->command('ocean:sync_ad_convert')->cron('30 3 * * *');

            // 巨量账户报表同步
            $schedule->command('ocean:sync_account_report --date=today --running=1')->cron('*/5 * * * *');
//            $schedule->command('ocean:sync_account_report --date=yesterday --key_suffix=yesterday')->cron('25-30 9 * * *');

            // 巨量创意报表同步
            $schedule->command('ocean:sync_creative_report --date=today --running=1 --run_by_account_cost=1')->cron('*/5 * * * *');
//            $schedule->command('ocean:sync_creative_report --date=yesterday --key_suffix=yesterday')->cron('25-30 10 * * *');
        }

    }
}
