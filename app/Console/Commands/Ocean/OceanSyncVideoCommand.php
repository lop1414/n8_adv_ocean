<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanVideoService;

class OceanSyncVideoCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_video  {--date=} {--account_ids=} {--video_ids=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步头条视频';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * @throws \App\Common\Tools\CustomException
     * 处理
     */
    public function handle(){
        $param = $this->option();
        $oceanVideoService = new OceanVideoService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanVideoService, 'syncVideo'],
            'ocean_sync_video',
            3600,
            $option,
            $param
        );
    }
}
