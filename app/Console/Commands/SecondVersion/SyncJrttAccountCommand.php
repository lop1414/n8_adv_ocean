<?php

namespace App\Console\Commands\SecondVersion;

use App\Common\Console\BaseCommand;
use App\Common\Enums\AdvAccountBelongTypeEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\CenterApiService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Services\Ocean\OceanService;
use App\Services\SecondVersionService;

class SyncJrttAccountCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'second_version:sync_jrtt_account';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步二版头条账户';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * 处理
     */
    public function handle(){
        $secondVersionService = new SecondVersionService();
        $secondVersionService->syncJrttAccount();
    }
}
