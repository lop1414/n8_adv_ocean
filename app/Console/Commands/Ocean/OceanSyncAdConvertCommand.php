<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanAdConvertService;

class OceanSyncAdConvertCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_ad_convert {--account_ids=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步头条转化目标';

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

        $oceanAdConvertService = new OceanAdConvertService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanAdConvertService, 'sync'],
            'ocean_sync_ad_convert',
            43200,
            $option,
            $param
        );
    }
}
