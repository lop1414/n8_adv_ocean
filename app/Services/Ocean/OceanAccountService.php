<?php

namespace App\Services\Ocean;

use App\Common\Enums\AdvAccountBelongTypeEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Models\AppModel;
use App\Models\Ocean\OceanAccountModel;
use App\Services\Task\TaskOceanSyncService;

class OceanAccountService extends OceanService
{
    /**
     * OceanVideoService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $authCode
     * @return bool
     * @throws CustomException
     * 授权
     */
    public function grant($authCode){
        $appId = $this->sdk->getAppId();

        $appModel = new AppModel();
        $app = $appModel->where('app_id', $appId)->first();
        if(empty($app)){
            throw new CustomException([
                'code' => 'NOT_FOUND_APP',
                'message' => '找不到对应app',
                'data' => [
                    'app_id' => $appId,
                ],
            ]);
        }

        if(!Functions::isLocal()){
            $info = $this->sdk->grant($appId, $app->secret, $authCode);
        }else{
            $info = [
                'advertiser_id' => '1687583265567758',
                'access_token' => '111',
                'refresh_token' => '222',
                'expires_in' => 86399,
            ];
        }

        $oceanAccountModel = new OceanAccountModel();
        $oceanAccount = $oceanAccountModel->where('app_id', $appId)
            ->where('account_id', $info['advertiser_id'])
            ->first();

        $oceanAccount->belong_platform = AdvAccountBelongTypeEnum::LOCAL;
        $oceanAccount->access_token = $info['access_token'];
        $oceanAccount->refresh_token = $info['refresh_token'];
        $oceanAccount->fail_at = date('Y-m-d H:i:s', time() + $info['expires_in'] - 2000);
        $oceanAccount->save();

        // 创建任务
        $taskOceanSyncService = new TaskOceanSyncService(OceanSyncTypeEnum::ACCOUNT);
        $task = [
            'name' => "巨量账户同步",
            'admin_id' => 0,
        ];

        $subs = [];
        $subs[] = [
            'app_id' => $appId,
            'account_id' => $info['advertiser_id'],
            'admin_id' => $task['admin_id'],
        ];
        $taskOceanSyncService->create($task, $subs);

        return true;
    }

    /**
     * @param $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option){
        $this->setAccountId($option['account_id']);
        $this->setAccessToken();
        $accounts = $this->sdk->getAccountList($option['account_id']);

        $oceanAccountModel = new OceanAccountModel();
        $parentAccount = $oceanAccountModel->where('app_id', $option['app_id'])
            ->where('account_id', $option['account_id'])
            ->first();

        $datetime = date('Y-m-d H:i:s');

        $data = [];
        foreach($accounts['list'] as $account){
            $data[] = [
                'app_id' => $option['app_id'],
                'name' => $account['advertiser_name'],
                'company' => '',
                'account_role' => '',
                'belong_platform' => AdvAccountBelongTypeEnum::LOCAL,
                'account_id' => $account['advertiser_id'],
                'extend' => json_encode([]),
                'parent_id' => $parentAccount->account_id,
                'status' => StatusEnum::ENABLE,
                'admin_id' => 0,
                'access_token' => $parentAccount->access_token,
                'refresh_token' => '',
                'fail_at' => $parentAccount->fail_at,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ];
        }

        $oceanAccountModel = new OceanAccountModel();
        $oceanAccountModel->batchInsertOrUpdate($data);

        if(!Functions::isLocal()){
            $this->syncCompany();
        }

        return true;
    }

    /**
     * @return bool
     * 同步公司
     */
    public function syncCompany(){
        $datetime = date('Y-m-d H:i:s', time() - 86400);

        // 获取公司为空的账户
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccounts = $oceanAccountModel->where('company', '')
            ->where('parent_id', '<>', 0)
            ->where('fail_at', '>', $datetime)
            ->get();

        foreach($oceanAccounts as $oceanAccount){
            try{
                // 获取账户信息
                $oceanService = new OceanService($oceanAccount->app_id);
                $oceanService->setAccountId($oceanAccount->account_id);
                $accountInfoList = $oceanService->getAccountInfoList([$oceanAccount->account_id]);
                $accountInfo = current($accountInfoList);

                // 保存
                $oceanAccount->company = $accountInfo['company'] ?? '';
                $oceanAccount->save();
            }catch(CustomException $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);
            }catch(\Exception $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);
            }
        }

        return true;
    }

    /**
     * @return bool
     * 刷新 access token
     */
    public function refreshAccessToken(){
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccounts = $oceanAccountModel->where('belong_platform', AdvAccountBelongTypeEnum::LOCAL)
            ->where('parent_id', 0)
            ->get();
        foreach($oceanAccounts as $oceanAccount){
            $this->sdk->setAppId($oceanAccount->app_id);
            $this->sdk->setAccountId($oceanAccount->account_id);

            if(!Functions::isLocal()){
                $info = $this->sdk->refreshAccessToken($oceanAccount->app_id, $oceanAccount->app->secret, $oceanAccount->refresh_token);
            }else{
                $info = [
                    'advertiser_id' => '1687583265567758',
                    'access_token' => '111',
                    'refresh_token' => '222',
                    'expires_in' => 86399,
                ];
            }

            $oceanAccount->access_token = $info['access_token'];
            $oceanAccount->refresh_token = $info['refresh_token'];
            $oceanAccount->fail_at = date('Y-m-d H:i:s', time() + $info['expires_in'] - 2000);
            $oceanAccount->save();

            $oceanAccountModel = new OceanAccountModel();
            $oceanAccountModel->where('parent_id', $oceanAccount->account_id)->update([
                'belong_platform' => AdvAccountBelongTypeEnum::LOCAL,
                'access_token' => $oceanAccount->access_token,
                'refresh_token' => $oceanAccount->refresh_token,
                'fail_at' => $oceanAccount->fail_at,
            ]);
        }

        return true;
    }
}
