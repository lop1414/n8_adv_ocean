<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Enums\TaskTypeEnum;
use App\Common\Tools\CustomException;
use App\Services\Ocean\OceanImageService;

class TaskOceanImageUploadService extends TaskOceanService
{
    /**
     * TaskOceanImageUploadService constructor.
     * @throws CustomException
     */
    public function __construct()
    {
        parent::__construct(TaskTypeEnum::OCEAN_IMAGE_UPLOAD);
    }

    /**
     * @param $taskId
     * @param $data
     * @return bool|void
     * @throws CustomException
     * 创建
     */
    public function createSub($taskId, $data){
        // 验证
        $this->validRule($data, [
            'app_id' => 'required',
            'account_id' => 'required',
            'n8_material_image_id' => 'required',
            'n8_material_image_path' => 'required',
            'n8_material_image_name' => 'required',
            'n8_material_image_signature' => 'required',
        ]);

        $subModel = new $this->subModelClass();
        $subModel->task_id = $taskId;
        $subModel->app_id = $data['app_id'];
        $subModel->account_id = $data['account_id'];
        $subModel->n8_material_image_id = $data['n8_material_image_id'];
        $subModel->n8_material_image_path = $data['n8_material_image_path'];
        $subModel->n8_material_image_name = $data['n8_material_image_name'];
        $subModel->n8_material_image_signature = $data['n8_material_image_signature'];
        $subModel->exec_status = ExecStatusEnum::WAITING;
        $subModel->admin_id = $data['admin_id'] ?? 0;
        $subModel->extends = $data['extends'] ?? [];

        return $subModel->save();
    }

    /**
     * @param $taskId
     * @return mixed
     * 获取待执行子任务
     */
    public function getWaitingSubTasks($taskId){
        $subModel = new $this->subModelClass();

        $subTasks = $subModel->where('task_id', $taskId)
            ->where('exec_status', ExecStatusEnum::WAITING)
            ->orderBy('id', 'asc')
            ->get();

        return $subTasks;
    }

    /**
     * @param $subTask
     * @return bool|void
     * @throws CustomException
     * 执行子任务
     */
    public function runSub($subTask){
        // 下载
        $file = $this->download($subTask->n8_material_image_path);

        // 上传
        $oceanImageService = new OceanImageService($subTask->app_id);
        $oceanImageService->setAccountId($subTask->account_id);
        $oceanImageService->uploadImage($subTask->account_id, $file['signature'], $file['curl_file'], $subTask->n8_material_image_name);

        // 删除临时文件
        unlink($file['path']);

        return true;
    }

    /**
     * @param $fileUrl
     * @param $storageDir
     * @return array
     * 下载
     */
    private function download($fileUrl){
        $content = file_get_contents($fileUrl);

        $fileName = basename($fileUrl);
        $tmp = explode(".", $fileName);
        $suffix = end($tmp);

        // 临时文件保存目录
        $storageDir = storage_path('app/temp');
        if(!is_dir($storageDir)){
            mkdir($storageDir, 0755, true);
        }

        // 文件存放地址
        $path = $storageDir .'/'. md5(uniqid()) .'.'. $suffix;

        // 保存
        file_put_contents($path, $content);

        // 设置 mime_type
        $curlFile = new \CURLFile($path);

        return [
            'path' => $path,
            'signature' => md5($content),
            'curl_file' => $curlFile,
        ];
    }
}
