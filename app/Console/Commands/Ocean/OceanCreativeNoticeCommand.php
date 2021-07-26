<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanCreativeLogService;

class OceanCreativeNoticeCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:creative_notice';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量创意通知';

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

        $oceanCreativeLogService = new OceanCreativeLogService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanCreativeLogService, 'notice'],
            'ocean_creative_notice',
            43200,
            $option,
            $param
        );
    }
}
