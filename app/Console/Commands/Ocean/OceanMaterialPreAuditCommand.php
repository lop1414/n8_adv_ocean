<?php

namespace App\Console\Commands\Ocean;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Services\Ocean\OceanMaterialPreAuditService;

class OceanMaterialPreAuditCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'ocean:material_pre_audit {--date=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '巨量素材预审';

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

        // 锁 key
        $lockKey = 'ocean_material_pre_audit';

        // key 后缀
        if(!empty($option['date'])){
            $lockKey .= '_'. trim($option['date']);
        }

        $this->lockRun(
            [$this, 'exec'],
            $lockKey,
            43200,
            ['log' => true],
            $option
        );
    }

    /**
     * @param $option
     * @return bool
     * @throws \App\Common\Tools\CustomException
     * 执行
     */
    protected function exec($option){
        // 获取日期范围
        $dateRange = Functions::getDateRange($option['date']);
        $dateList = Functions::getDateListByRange($dateRange);

        $service = new OceanMaterialPreAuditService();
        foreach($dateList as $date){
            $service->run([
                'date' => $date,
            ]);
        }

        return true;
    }
}
