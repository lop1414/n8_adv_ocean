<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanIndustryService;

class OceanSyncIndustryCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_industry';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步巨量行业';

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
        $oceanIndustryService = new OceanIndustryService();
        $oceanIndustryService->syncIndustry();
    }
}
