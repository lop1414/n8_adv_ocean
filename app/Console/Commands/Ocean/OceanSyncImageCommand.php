<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanImageService;

class OceanSyncImageCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:sync_image  {--date=} {--account_ids=} {--ids=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步头条图片';

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

        // id
        if(!empty($param['ids'])){
            $param['ids'] = explode(",", $param['ids']);
        }

        $oceanImageService = new OceanImageService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanImageService, 'sync'],
            'ocean_sync_image',
            43200,
            $option,
            $param
        );
    }
}