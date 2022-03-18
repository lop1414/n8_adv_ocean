<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Services\AdvRoiConvertCallbackService;

class RoiConvertCallbackCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'roi_convert_callback';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = 'roi转化回传';

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

        $advRoiConvertCallbackService = new AdvRoiConvertCallbackService();
        $option = ['log' => true];
        $this->lockRun(
            [$advRoiConvertCallbackService, 'run'],
            'roi_convert_callback',
            3600,
            $option,
            $param
        );
    }
}
