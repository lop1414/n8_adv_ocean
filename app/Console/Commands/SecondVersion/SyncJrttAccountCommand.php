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
        // 已同步账户
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccounts = $oceanAccountModel->where('belong_platform', AdvAccountBelongTypeEnum::SECOND_VERSION)->get();

        // 映射
        $accountMap = [];
        foreach($oceanAccounts as $oceanAccount){
            $accountMap[$oceanAccount['app_id']][$oceanAccount['account_id']] = $oceanAccount->toArray();
        }

        // 获取二版账户
        $secondVersionService = new SecondVersionService();
        if(!Functions::isDebug()){
            $tmp = $secondVersionService->getJrttAdvAccounts(1, 10);
            $secondVersionAccounts = $tmp['list'];
        }else{
            $secondVersionAccounts = $secondVersionService->getJrttAllAdvAccount();
        }

        // 管理员映射
        $centerApiService = new CenterApiService();
        $adminUsers = $centerApiService->apiGetAdminUsers();
        $adminUserMap = array_column($adminUsers, 'id', 'name');

        $data = [];
        foreach($secondVersionAccounts as $account){
            if(isset($accountMap[$account['app_id']][$account['adv_id']])){
                // 已同步过的账户, 只更新部分字段
                $tmp = $accountMap[$account['app_id']][$account['adv_id']];
                $tmp['access_token'] = $account['token'];
                $tmp['fail_at'] = $account['fail_at'];
                $tmp['extend'] = json_encode($tmp['extend']);
                $tmp['admin_id'] = $adminUserMap[$account['admin_name']] ?? 0;
                $data[] = $tmp;
            }else{
                // 未同步过的账户
                $data[] = [
                    'app_id' => $account['app_id'],
                    'name' => $account['name'],
                    'account_role' => '',
                    'belong_platform' => AdvAccountBelongTypeEnum::SECOND_VERSION,
                    'account_id' => $account['adv_id'],
                    'access_token' => $account['token'],
                    'refresh_token' => '',
                    'fail_at' => $account['fail_at'],
                    'created_at' => date('Y-m-d H:i:s', TIMESTAMP),
                    'updated_at' => date('Y-m-d H:i:s', TIMESTAMP),
                    'extend' => json_encode([]),
                    'parent_id' => $account['parent_adv_id'],
                    'status' => StatusEnum::ENABLE,
                    'admin_id' => $adminUserMap[$account['admin_name']] ?? 0,
                ];
            }
        }

        // 批量插入更新
        $oceanAccountModel->batchInsertOrUpdate($data);

        // 更新账户角色
        //$this->updateAccountRole();
    }

    /**
     * @throws CustomException
     * 更新账户角色
     */
    private function updateAccountRole(){
        // 未设置角色账户
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccounts = $oceanAccountModel->where('belong_platform', AdvAccountBelongTypeEnum::SECOND_VERSION)
            ->where('account_role', '')
            ->get();

        // 获取账户角色
        foreach($oceanAccounts as $oceanAccount){
            $OceanService = new OceanService($oceanAccount->app_id);
            $OceanService->setAccountId($oceanAccount->account_id);
            dd($OceanService->getAccountInfo([$oceanAccount->account_id]));
            #TODO:更新账户角色
        }
    }
}
