<?php

namespace App\Services;

use App\Common\Enums\ExecStatusEnum;
use App\Common\Enums\TaskStatusEnum;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Models\TaskOceanImageUploadModel;

class TaskOceanImageUploadService extends BaseService
{
    /**
     * TaskOceanImageUploadService constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $data
     * @return bool
     * 创建
     */
    public function create($data){
        $this->model = new TaskOceanImageUploadModel();
        $this->model->task_id = $data['task_id'];
        $this->model->app_id = $data['app_id'];
        $this->model->account_id = $data['account_id'];
        $this->model->n8_material_image_path = $data['n8_material_image_path'];
        $this->model->n8_material_image_name = $data['n8_material_image_name'];
        $this->model->exec_status = ExecStatusEnum::WAITING;
        $this->model->admin_id = $data['admin_id'];
        return $this->model->save();
    }

    /**
     * @param $taskId
     * @return mixed
     * 获取待执行子任务
     */
    public function getWaitingSubTasks($taskId){
        $taskOceanImageUploadModel = new TaskOceanImageUploadModel();

        $subTasks = $taskOceanImageUploadModel->where('task_id', $taskId)
            ->where('exec_status', ExecStatusEnum::WAITING)
            ->orderBy('id', 'asc')
            ->get();

        return $subTasks;
    }

    /**
     * @return bool
     * @throws CustomException
     * 执行
     */
    public function run(){
        $taskService = new TaskService();
        $tasks = $taskService->getWaitingTasks(TaskTypeEnum::OCEAN_IMAGE_UPLOAD);

        foreach($tasks as $task){
            try{
                // 获取子任务
                $subTasks = $this->getWaitingSubTasks($task->id);
                foreach($subTasks as $subTask){
                    // 下载
                    $file = $this->download($subTask->n8_material_image_path);

                    // 上传
                    $oceanEngineService = new OceanEngineService($subTask->app_id);
                    $oceanEngineService->setAccountId($subTask->account_id);
                    $oceanEngineService->uploadImage($subTask->account_id, $file['signature'], $file['curl_file'], $subTask->n8_material_image_name);

                    // 删除临时文件
                    unlink($file['path']);

                    $subTask->exec_status = ExecStatusEnum::SUCCESS;
                    $subTask->save();
                }

                // 更改任务状态
                $taskService->updateTaskStatus($task, TaskStatusEnum::SUCCESS);
            }catch(CustomException $e){
                // 更改任务状态
                $taskService->updateTaskStatus($task, TaskStatusEnum::FAIL);

                throw new CustomException($e->getErrorInfo(true));
            }catch(\Exception $e){
                // 更改任务状态
                $taskService->updateTaskStatus($task, TaskStatusEnum::FAIL);

                throw new \Exception($e->getMessage());
            }
        }

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
