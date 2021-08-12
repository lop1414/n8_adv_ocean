<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Services\Ocean\OceanMaterialCreativeService;

class OceanMaterialCreativeSyncCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:material_creative_sync {--date=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量素材创意关联同步';

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

        $oceanMaterialCreativeService = new OceanMaterialCreativeService();
        $option = ['log' => true];
        $this->lockRun(
            [$oceanMaterialCreativeService, 'sync'],
            'ocean_material_creative_sync',
            43200,
            $option,
            $param
        );
    }
}
