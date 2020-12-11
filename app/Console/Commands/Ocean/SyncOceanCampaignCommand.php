<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanCampaignService;

class SyncOceanCampaignCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_campaign  {--date=} {--account_ids=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步头条广告组';

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
        $option = $this->option();
        $oceanService = new OceanCampaignService();
        $oceanService->syncCampaign($option);
    }
}
