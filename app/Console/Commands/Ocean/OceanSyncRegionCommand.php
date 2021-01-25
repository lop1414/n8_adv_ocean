<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanRegionService;

class OceanSyncRegionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_region';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步巨量地域';

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
        $oceanRegionService = new OceanRegionService();
        $oceanRegionService->syncRegion();
    }
}
