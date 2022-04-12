<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanAccountFundDailyStatService;

class OceanSyncAccountFundDailyStatCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_account_fund_daily_stat {--date=} {--account_ids=} {--has_history_cost=} {--key_suffix=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步头条账户日流水';

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

        // 账户
        if(!empty($param['account_ids'])){
            $param['account_ids'] = explode(",", $param['account_ids']);
        }

        // 锁 key
        $lockKey = 'ocean_sync_account_fund_daily_stat';

        // key 后缀
        if(!empty($param['key_suffix'])){
            $lockKey .= '_'. trim($param['key_suffix']);
        }

        $oceanAccountFundDailyStatService = new OceanAccountFundDailyStatService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanAccountFundDailyStatService, 'sync'],
            $lockKey,
            43200,
            $option,
            $param
        );
    }
}
