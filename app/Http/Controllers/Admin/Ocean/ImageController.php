<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\MaterialApiService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Sdks\OceanEngine\OceanEngine;
use App\Services\Ocean\OceanImageService;
use App\Services\Task\TaskOceanImageUploadService;
use App\Services\Task\TaskService;
use Illuminate\Http\Request;

class ImageController extends OceanController
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
     * @throws CustomException
     * 上传
     */
    public function upload(Request $request){
        $this->validRule($request->post(), [
            'account_id' => 'required',
        ]);

        $accountId = $request->post('account_id');
        $file = $request->file('file');

        if(is_null($file)){
            throw new CustomException([
                'code' => 'NOT_FOUND_UPLOAD_FILE',
                'message' => '未找到上传文件',
            ]);
        }

        if(!$file->isValid()){
            throw new CustomException([
                'code' => 'UPLOAD_FILE_FAIL',
                'message' => '上传文件失败',
            ]);
        }

        // 签名
        $signature = md5(file_get_contents($file->getRealPath()));

        $curlFile = new \CURLFile($file->getRealPath());

        $oceanAccount = $this->getAccessAccount($accountId);
        $oceanImageService = new OceanImageService($oceanAccount->app_id);

        $oceanImageService->setAccountId($oceanAccount->account_id);
        $data = $oceanImageService->uploadImage($oceanAccount->account_id, $signature, $curlFile);

        return $this->success($data);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 批量上传
     */
    public function batchUpload(Request $request){
        $this->validRule($request->post(), [
            'account_ids' => 'required|array',
            'image_ids' => 'required|array'
        ]);

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

        // 图片尺寸校验
        $invalidImages = [];
        $oceanEngine = new OceanEngine('');
        foreach($images as $image){
            $valid = $oceanEngine->validImage($image['width'], $image['height'], $image['size']);
            if(!$valid){
                $invalidImages[] = $image;
            }
        }

        if(!empty($invalidImages)){
            $invalidImageNames = array_column($invalidImages, 'name');
            $invalidImageNamesStr = implode(",", $invalidImageNames);
            throw new CustomException([
                'code' => 'IMAGE_INVALID',
                'message' => "非法尺寸图片 ({$invalidImageNamesStr})",
                'data' => [
                    'invalid_images' => $invalidImages,
                ],
            ]);
        }

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
        $taskOceanImageUploadService = new TaskOceanImageUploadService();
        $task = [
            'name' => '批量上传巨量图片',
            'admin_id' => $adminUserInfo['admin_user']['id'],
        ];
        $subs = [];
        foreach($accounts as $account){
            foreach($images as $image){
                $subs[] = [
                    'app_id' => $account->app_id,
                    'account_id' => $account->account_id,
                    'n8_material_image_path' => $image['path'],
                    'n8_material_image_name' => $image['name'],
                    'admin_id' => $adminUserInfo['admin_user']['id'],
                ];
            }
        }
        $taskOceanImageUploadService->create($task, $subs);


        return $this->success([
            'task_id' => $taskOceanImageUploadService->taskId,
            'account_count' => $accounts->count(),
            'image_count' => count($images),
        ], [], '批量上传任务已提交【任务id:'. $taskOceanImageUploadService->taskId .'】，执行结果后续同步到飞书，请注意查收！');
    }
}
