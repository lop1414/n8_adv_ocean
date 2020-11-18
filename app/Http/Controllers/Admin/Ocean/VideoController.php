<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\MaterialApiService;
use App\Common\Tools\CustomException;
use App\Models\OceanAccountModel;
use App\Services\TaskOceanVideoUploadService;
use App\Services\TaskService;
use Illuminate\Http\Request;

class VideoController extends AdminController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 批量上传
     */
    public function batchUpload(Request $request){
        $this->validRule($request->post(), [
            'app_id' => 'required',
            'account_ids' => 'required|array',
            'video_ids' => 'required|array'
        ]);

        $appId = $request->post('app_id');
        $accountIds = $request->post('account_ids');
        $videoIds = $request->post('video_ids');

        // 获取视频
        $materialApiService = new MaterialApiService();
        $videos = $materialApiService->apiGetVideos($videoIds);

        // 获取账户
        $oceanAccountModel = new OceanAccountModel();
        $accounts = $oceanAccountModel->where('app_id', $appId)
            ->whereIn('account_id', $accountIds)
            ->get();

        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        // 创建任务
        $taskService = new TaskService();
        $task = [
            'name' => '批量上传巨量视频',
            'task_type' => TaskTypeEnum::OCEAN_VIDEO_UPLOAD,
            'admin_id' => $adminUserInfo['admin_user']['id'],
        ];
        $ret = $taskService->create($task);
        if(!$ret){
            throw new CustomException([
                'code' => 'CREATE_UPLOAD_VIDEO_TASK_ERROR',
                'message' => '创建上传视频任务失败',
            ]);
        }
        $taskId = $taskService->getModel()->id;

        // 创建子任务
        $oceanVideoUploadTaskService = new TaskOceanVideoUploadService();
        foreach($accounts as $account){
            foreach($videos as $video){
                $oceanVideoUploadTaskService->create([
                    'task_id' => $taskId,
                    'app_id' => $account->app_id,
                    'account_id' => $account->account_id,
                    'n8_material_video_path' => $video['path'],
                    'n8_material_video_name' => $video['name'],
                    'admin_id' => $adminUserInfo['admin_user']['id'],
                ]);
            }
        }

        return $this->success([
            'task_id' => $taskId,
            'account_count' => $accounts->count(),
            'video_count' => count($videos),
        ], [], '批量上传任务已提交【任务id:'. $taskId .'】');
    }
}
