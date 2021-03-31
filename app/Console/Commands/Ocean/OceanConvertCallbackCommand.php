<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanConvertCallbackService;

class OceanConvertCallbackCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:convert_callback';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量转化回传';

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

        $oceanConvertCallbackService = new OceanConvertCallbackService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanConvertCallbackService, 'run'],
            'ocean_convert_callback',
            3600,
            $option,
            $param
        );
    }
}
