<?php

namespace App\Console\Commands\Queue;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanClickService;

class QueueOceanClickCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'queue:ocean_click';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量点击队列';

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
        $oceanClickService = new OceanClickService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanClickService, 'pull'],
            "queue_ocean_click",
            3600,
            $option
        );
    }
}
