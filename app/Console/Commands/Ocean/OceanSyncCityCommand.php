<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanCityService;
use App\Services\Ocean\OceanRegionService;

class OceanSyncCityCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_city';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步巨量城市';

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
        $oceanCityService = new OceanCityService();
        $oceanCityService->syncCity();
    }
}
