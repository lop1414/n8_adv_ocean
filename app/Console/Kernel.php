<?php

namespace App\Console;

use App\Common\Console\CustomConvertCallbackCommand;
use App\Common\Helpers\Functions;
use App\Common\Console\ConvertCallbackCommand;
use App\Console\Commands\Ocean\OceanBatchSyncAccountCommand;
use App\Console\Commands\Ocean\OceanCreativeNoticeCommand;
use App\Console\Commands\Ocean\OceanMaterialCreativeSyncCommand;
use App\Console\Commands\Ocean\OceanRefreshAccessTokenCommand;
use App\Console\Commands\Ocean\OceanSyncAccountFundCommand;
use App\Console\Commands\Ocean\OceanSyncCreativeCommand;
use App\Console\Commands\Ocean\OceanSyncImageCommand;
use App\Console\Commands\Ocean\Report\OceanSyncAccountReportCommand;
use App\Console\Commands\Ocean\OceanSyncAdCommand;
use App\Console\Commands\Ocean\OceanSyncAdConvertCommand;
use App\Console\Commands\Ocean\OceanSyncCityCommand;
use App\Console\Commands\Ocean\OceanSyncIndustryCommand;
use App\Console\Commands\Ocean\OceanSyncRegionCommand;
use App\Console\Commands\Ocean\OceanSyncVideoCommand;
use App\Console\Commands\Ocean\Report\OceanSyncCreativeReportCommand;
use App\Common\Console\Queue\QueueClickCommand;
use App\Console\Commands\Ocean\Report\OceanSyncMaterialReportCommand;
use App\Console\Commands\RoiConvertCallbackCommand;
use App\Console\Commands\SecondVersion\SyncJrttAccountCommand;
use App\Console\Commands\Ocean\OceanSyncCampaignCommand;
use App\Console\Commands\SyncChannelAdCommand;
use App\Console\Commands\SyncSecondVersionCostCommand;
use App\Console\Commands\Task\TaskOceanAdCreativeCreateCommand;
use App\Console\Commands\Task\TaskOceanAdUpdateCommand;
use App\Console\Commands\Task\TaskOceanImageUploadCommand;
use App\Console\Commands\Task\TaskOceanSyncCommand;
use App\Console\Commands\Task\TaskOceanVideoUploadCommand;
use App\Console\Commands\Task\TaskReWaitingCommand;
use App\Console\Commands\TestCommand;
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
        // 任务重执行
        TaskReWaitingCommand::class,

        // 巨量上传任务
        TaskOceanVideoUploadCommand::class,
        TaskOceanImageUploadCommand::class,

        // 巨量同步任务
        TaskOceanSyncCommand::class,

        // 巨量计划创意创建任务
        TaskOceanAdCreativeCreateCommand::class,

        // 巨量计划更新任务
        TaskOceanAdUpdateCommand::class,

        // 巨量
        OceanSyncCampaignCommand::class,
        OceanSyncVideoCommand::class,
        OceanSyncImageCommand::class,
        OceanSyncRegionCommand::class,
        OceanSyncCityCommand::class,
        OceanSyncIndustryCommand::class,
        OceanSyncAdCommand::class,
        OceanSyncCreativeCommand::class,
        OceanSyncAdConvertCommand::class,
        OceanSyncAccountReportCommand::class,
        OceanSyncCreativeReportCommand::class,
        OceanSyncMaterialReportCommand::class,
        OceanCreativeNoticeCommand::class,
        OceanMaterialCreativeSyncCommand::class,
        OceanBatchSyncAccountCommand::class,
        OceanSyncAccountFundCommand::class,

        // 巨量刷新access_token
        OceanRefreshAccessTokenCommand::class,

        // 转化回传
        ConvertCallbackCommand::class,
        RoiConvertCallbackCommand::class,

        // 自定义转化回传
        CustomConvertCallbackCommand::class,

        // 同步渠道-计划关联
        SyncChannelAdCommand::class,

        // 队列
        QueueClickCommand::class,

        // 测试
        TestCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 任务重执行
        $schedule->command('task:re_waiting')->cron('* * * * *');

        // 巨量上传任务
        $schedule->command('task:ocean_image_upload')->cron('* * * * *');
        $schedule->command('task:ocean_video_upload')->cron('* * * * *');

        // 巨量同步任务
        $schedule->command('task:ocean_sync --type=account')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=video')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=campaign')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=ad')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=creative')->cron('* * * * *');
        $schedule->command('task:ocean_sync --type=ad_convert')->cron('* * * * *');

        // 巨量计划创意创建任务
        $schedule->command('task:ocean_ad_creative_create')->cron('* * * * *');

        // 巨量计划更新任务
        $schedule->command('task:ocean_ad_update')->cron('* * * * *');

        // 队列
        $schedule->command('queue:click')->cron('* * * * *');

        // 转化上报
        $schedule->command('convert_callback')->cron('* * * * *');
        $schedule->command('roi_convert_callback')->cron('* * * * *');

        // 自定义转化上报
        $schedule->command('custom_convert_callback')->cron('* * * * *');

        // 同步渠道-计划
        $schedule->command('sync_channel_ad --date=today')->cron('*/2 * * * *');

        // 正式
        if(Functions::isProduction()){
            // 巨量创意通知
            $schedule->command('ocean:creative_notice')->cron('* * * * *');

            // 同步素材-创意关联
            $schedule->command('ocean:material_creative_sync --date=today')->cron('*/20 * * * *');

            // 巨量刷新 access_token
            $schedule->command('ocean:refresh_access_token')->cron('0 */8 * * *');

            // 批量同步巨量账户
            $schedule->command('ocean:batch_sync_account')->cron('5 * * * *');

            // 巨量账户余额
            $schedule->command('ocean:sync_account_fund --has_history_cost=1 --key_suffix=has_history_cost')->cron('*/2 * * * *');

            // 巨量广告组同步
            $schedule->command('ocean:sync_campaign --create_date=today --multi_chunk_size=1')->cron('*/30 * * * *');

            // 巨量计划同步
            $schedule->command('ocean:sync_ad --update_date=today')->cron('*/2 * * * *');
            $schedule->command('ocean:sync_ad --update_date=yesterday --key_suffix=yesterday')->cron('25-30 2 * * *');

            // 巨量创意同步
            $schedule->command('ocean:sync_creative --update_date=today --create_log=1')->cron('*/2 * * * *');
            $schedule->command('ocean:sync_creative --update_date=yesterday --create_log=1 --key_suffix=yesterday')->cron('25-30 1 * * *');

            // 巨量视频同步
            $schedule->command('ocean:sync_video --date=today --multi_chunk_size=2')->cron('10-12 * * * *');
            $schedule->command('ocean:sync_video --date=yesterday  --key_suffix=yesterday')->cron('10-12 3 * * *');

            // 巨量图片同步
            $schedule->command('ocean:sync_image --date=today --multi_chunk_size=2')->cron('40-42 * * * *');
            $schedule->command('ocean:sync_image --date=yesterday  --key_suffix=yesterday')->cron('40-42 3 * * *');

            // 巨量转化跟踪同步
            $schedule->command('ocean:sync_ad_convert')->cron('30-32 3 * * *');

            // 巨量账户报表同步
            $schedule->command('ocean:sync_account_report --date=today --has_history_cost=1 --key_suffix=has_history_cost')->cron('*/2 * * * *');
            $schedule->command('ocean:sync_account_report --date=today')->cron('15 * * * *');
            $schedule->command('ocean:sync_account_report --date=yesterday --key_suffix=yesterday')->cron('25-30 10 * * *');

            // 巨量创意报表同步
            $schedule->command('ocean:sync_creative_report --date=today --run_by_account_cost=1 --multi_chunk_size=5')->cron('*/2 * * * *');
            $schedule->command('ocean:sync_creative_report --date=yesterday --key_suffix=yesterday')->cron('10-15 9,14 * * *');

            // 巨量素材报表同步
            $schedule->command('ocean:sync_material_report --date=yesterday --key_suffix=yesterday')->cron('30-35 9,14 * * *');
        }
    }
}
