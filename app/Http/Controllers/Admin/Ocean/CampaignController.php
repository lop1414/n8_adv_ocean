<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Ocean\OceanCampaignModel;
use App\Services\Task\TaskOceanSyncService;
use Illuminate\Http\Request;

class CampaignController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCampaignModel();

        parent::__construct();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws CustomException
     * 同步
     */
    public function sync(Request $request){
        $this->validRule($request->post(), [
            'account_ids' => 'required|array'
        ]);

        $accountIds = $request->post('account_ids');

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

        // 创建任务
        $taskOceanSyncService = new TaskOceanSyncService(OceanSyncTypeEnum::CAMPAIGN);
        $task = [
            'name' => '巨量广告组同步',
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

        return $this->success([
            'task_id' => $taskOceanSyncService->taskId,
            'account_count' => $accounts->count(),
        ], [], '批量上传任务已提交【任务id:'. $taskOceanSyncService->taskId .'】，执行结果后续同步到飞书，请注意查收！');
    }
}
