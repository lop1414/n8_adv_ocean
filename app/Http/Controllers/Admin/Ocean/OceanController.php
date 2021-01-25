<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Models\Ocean\OceanAccountModel;
use App\Services\Task\TaskOceanSyncService;
use Illuminate\Http\Request;

class OceanController extends AdminController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        $this->curdService->selectQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $builder->withPermission();
            });
        });
    }

    /**
     * 查询（无分页）预处理
     */
    public function getPrepare(){
        $this->curdService->getQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $builder->withPermission();
            });
        });
    }

    /**
     * 树预处理
     */
    public function treePrepare(){
        $this->curdService->treeQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $builder->withPermission();
            });
        });
    }

    /**
     * 同步前
     */
    public function syncBefore(){}

    /**
     * @param Request $request
     * @return mixed
     * @throws CustomException
     * 同步
     */
    public function sync(Request $request){
        $this->validRule($request->post(), [
            'account_ids' => 'required|array',
            'type' => 'required',
        ]);

        $accountIds = $request->post('account_ids');
        $syncType = $request->post('type');

        Functions::hasEnum(OceanSyncTypeEnum::class, $syncType);

        // 获取后台用户信息
        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        // 获取账户
        $oceanAccountModel = new OceanAccountModel();
        $builder = $oceanAccountModel->whereIn('account_id', $accountIds);

        // 非管理员
        if(!$adminUserInfo['is_admin']){
            $builder->where('admin_id', $adminUserInfo['admin_user']['id']);
        }

        $accounts = $builder->get();
        if(!$accounts->count()){
            throw new CustomException([
                'code' => 'NOT_FOUND_ACCOUNT',
                'message' => '找不到对应账户',
            ]);
        }

        $this->syncBefore();

        // 创建任务
        $taskOceanSyncService = new TaskOceanSyncService($syncType);
        $syncTypeName = Functions::getEnumMapName(OceanSyncTypeEnum::class, $syncType);
        $task = [
            'name' => "巨量{$syncTypeName}同步",
            'admin_id' => $adminUserInfo['admin_user']['id'],
        ];

        $subs = [];
        foreach($accounts as $account){
            $subs[] = [
                'app_id' => $account->app_id,
                'account_id' => $account->account_id,
                'admin_id' => $adminUserInfo['admin_user']['id'],
            ];
        }
        $taskOceanSyncService->create($task, $subs);

        $this->syncAfter();

        return $this->success([
            'task_id' => $taskOceanSyncService->taskId,
            'account_count' => $accounts->count(),
        ], [], "批量同步{$syncTypeName}任务已提交【任务id:{$taskOceanSyncService->taskId}】，执行结果后续同步到飞书，请注意查收！");
    }

    public function syncAfter(){}

    /**
     * @return mixed
     * 获取当前管理员账户
     */
    public function getCurrentAdminUserAccount(){
        $adminUserInfo = Functions::getGlobalData('admin_user_info');
        return $this->getAdminUserAccount($adminUserInfo);
    }

    /**
     * @param $adminUserInfo
     * @return mixed
     * 获取管理员账户
     */
    public function getAdminUserAccount($adminUserInfo){
        $oceanAccountModel = new OceanAccountModel();
        //if(!$adminUserInfo['is_admin']){
            $oceanAccountModel = $oceanAccountModel->where('admin_id', $adminUserInfo['admin_user']['id']);
        //}
        $oceanAccounts = $oceanAccountModel->get();

        return $oceanAccounts;
    }

    /**
     * @param $accountId
     * @return mixed
     * @throws CustomException
     * 获取已授权账户
     */
    public function getAccessAccount($accountId){
        // 获取当前管理员账户
        $oceanAccounts = $this->getCurrentAdminUserAccount();

        // 查找
        $oceanAccount = $oceanAccounts->where('account_id', $accountId)->first();
        if(empty($oceanAccount)){
            throw new CustomException([
                'code' => 'NOT_ACCESS_ACCOUNT',
                'message' => '你不能操作该账户',
            ]);
        }

        return $oceanAccount;
    }
}
