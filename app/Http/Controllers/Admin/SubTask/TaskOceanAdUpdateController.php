<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\MaterialApiService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanAdUpdateTypeEnum;
use App\Models\Task\TaskOceanAdCreativeCreateModel;
use App\Models\Task\TaskOceanAdUpdateModel;
use App\Models\Task\TaskOceanVideoUploadModel;
use App\Services\Task\TaskOceanAdUpdateService;
use Illuminate\Http\Request;

class TaskOceanAdUpdateController extends SubTaskOceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new TaskOceanAdUpdateModel();

        parent::__construct();
    }

    public function create(Request $request){
        $this->validRule($request->post(), [
            'ocean_ad_creative_create_task_id' => 'required',
            'ad_update_type' => 'required',
            'data' => 'required',
        ]);

        $data = $request->post('data');

        $adUpdateType = $request->post('ad_update_type');
        Functions::hasEnum(OceanAdUpdateTypeEnum::class, $adUpdateType);

        $oceanAdCreativeCreateTaskId = $request->post('ocean_ad_creative_create_task_id');
        $taskOceanAdCreativeCreateModel = new TaskOceanAdCreativeCreateModel();
        $taskOceanAdCreativeCreates = $taskOceanAdCreativeCreateModel->where('task_id', $oceanAdCreativeCreateTaskId)
            ->where('ad_id', '>', '0')
            ->get();

        if($taskOceanAdCreativeCreates->isEmpty()){
            throw new CustomException([
                'code' => 'OCEAN_AD_CREATIVE_CREATE_TASK_AD_IS_EMPTY',
                'message' => '巨量批量创建任务内计划为空',
            ]);
        }

        // 获取后台用户信息
        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        $taskOceanAdUpdateService = new TaskOceanAdUpdateService();
        $has = $taskOceanAdUpdateService->hasAdminUserWaitingTask($adminUserInfo['admin_user']['id']);
        if($has){
            throw new CustomException([
                'code' => 'HAS_WAITING_TASK',
                'message' => '有待执行任务尚未完成,无法继续提交任务',
            ]);
        }

        // 创建任务
        $task = [
            'name' => "巨量计划批量更新",
            'admin_id' => $adminUserInfo['admin_user']['id'],
        ];
        $subs = [];
        foreach($taskOceanAdCreativeCreates as $taskOceanAdCreativeCreate){
            $subs[] = [
                'app_id' => $taskOceanAdCreativeCreate->app_id,
                'account_id' => $taskOceanAdCreativeCreate->account_id,
                'ad_id' => $taskOceanAdCreativeCreate->ad_id,
                'ad_update_type' => $adUpdateType,
                'data' => $data,
                'admin_id' => $adminUserInfo['admin_user']['id'],
            ];
        }

        $taskOceanAdUpdateService->create($task, $subs);

        return $this->success([
            'task_id' => $taskOceanAdUpdateService->taskId,
        ], [], '批量上传任务已提交【任务id:'. $taskOceanAdUpdateService->taskId .'】，执行结果后续同步到飞书，请注意查收！');
    }
}
