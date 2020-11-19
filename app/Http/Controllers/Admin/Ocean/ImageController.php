<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\MaterialApiService;
use App\Common\Tools\CustomException;
use App\Models\OceanAccountModel;
use App\Services\TaskOceanImageUploadService;
use App\Services\TaskOceanVideoUploadService;
use App\Services\TaskService;
use Illuminate\Http\Request;

class ImageController extends AdminController
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
            'image_ids' => 'required|array'
        ]);

        $appId = $request->post('app_id');
        $accountIds = $request->post('account_ids');
        $imageIds = $request->post('image_ids');

        // 获取图片
        $materialApiService = new MaterialApiService();
        $images = $materialApiService->apiGetImages($imageIds);
        if(empty($images)){
            throw new CustomException([
                'code' => 'NOT_FOUND_IMAGE',
                'message' => '找不到对应图片',
            ]);
        }

        // 获取后台用户信息
        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        // 获取账户
        $oceanAccountModel = new OceanAccountModel();
        $builder = $oceanAccountModel->where('app_id', $appId)
            ->whereIn('account_id', $accountIds);

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
        $taskService = new TaskService();
        $task = [
            'name' => '批量上传巨量图片',
            'task_type' => TaskTypeEnum::OCEAN_IMAGE_UPLOAD,
            'admin_id' => $adminUserInfo['admin_user']['id'],
        ];
        $ret = $taskService->create($task);
        if(!$ret){
            throw new CustomException([
                'code' => 'CREATE_UPLOAD_VIDEO_TASK_ERROR',
                'message' => '创建上传图片任务失败',
            ]);
        }
        $taskId = $taskService->getModel()->id;

        // 创建子任务
        $taskOceanImageUploadService = new TaskOceanImageUploadService();
        foreach($accounts as $account){
            foreach($images as $image){
                $taskOceanImageUploadService->create([
                    'task_id' => $taskId,
                    'app_id' => $account->app_id,
                    'account_id' => $account->account_id,
                    'n8_material_image_path' => $image['path'],
                    'n8_material_image_name' => $image['name'],
                    'admin_id' => $adminUserInfo['admin_user']['id'],
                ]);
            }
        }

        return $this->success([
            'task_id' => $taskId,
            'account_count' => $accounts->count(),
            'image_count' => count($images),
        ], [], '批量上传任务已提交【任务id:'. $taskId .'】');
    }
}
