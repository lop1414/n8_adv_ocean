<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanAccountService;

class OceanRefreshAccessTokenCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:refresh_access_token {--key_suffix=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量刷新access_token';

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

        // 锁 key
        $lockKey = 'ocean_refresh_access_token';

        // key 后缀
        if(!empty($param['key_suffix'])){
            $lockKey .= '_'. trim($param['key_suffix']);
        }

        $oceanAccountService = new OceanAccountService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanAccountService, 'refreshAccessToken'],
            $lockKey,
            43200,
            $option,
            $param
        );
    }
}
