<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Common\Enums\TaskStatusEnum;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Models\TaskOceanVideoUploadModel;
use App\Services\Ocean\OceanMaterialService;
use App\Services\Ocean\OceanVideoService;
use Illuminate\Support\Facades\DB;

class TaskOceanVideoUploadService extends BaseService
{
    /**
     * OceanVideoUploadTaskService constructor.
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
        $this->model = new TaskOceanVideoUploadModel();
        $this->model->task_id = $data['task_id'];
        $this->model->app_id = $data['app_id'];
        $this->model->account_id = $data['account_id'];
        $this->model->n8_material_video_id = $data['n8_material_video_id'];
        $this->model->n8_material_video_path = $data['n8_material_video_path'];
        $this->model->n8_material_video_name = $data['n8_material_video_name'];
        $this->model->n8_material_video_signature = $data['n8_material_video_signature'];
        $this->model->exec_status = ExecStatusEnum::WAITING;
        $this->model->admin_id = $data['admin_id'];
        $this->model->extends = $data['extends'] ?? [];
        return $this->model->save();
    }

    /**
     * @param $taskId
     * @return mixed
     * 获取待执行子任务
     */
    public function getWaitingSubTasks($taskId){
        $taskOceanVideoUploadModel = new TaskOceanVideoUploadModel();

        $subTasks = $taskOceanVideoUploadModel->where('task_id', $taskId)
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
        $tasks = $taskService->getWaitingTasks(TaskTypeEnum::OCEAN_VIDEO_UPLOAD);

        foreach($tasks as $task){
            try{
                // 执行子任务
                $this->runSubTask($task);

                // 更改任务状态
                $taskService->updateTaskStatus($task, TaskStatusEnum::SUCCESS);
            }catch(CustomException $e){
                $taskStatus = TaskStatusEnum::FAIL;
                $errorInfo = $e->getErrorInfo(true);

                // 公共请求返回空, 任务状态修改为待执行
                if(
                    $errorInfo['code'] == 'PUBLIC_REQUEST_ERROR' &&
                    empty($errorInfo['data']['result'])
                ){
                    $taskStatus = TaskStatusEnum::WAITING;
                }

                // 更改任务状态
                $taskService->updateTaskStatus($task, $taskStatus);

                throw new CustomException($errorInfo);
            }catch(\Exception $e){
                // 更改任务状态
                $taskService->updateTaskStatus($task, TaskStatusEnum::FAIL);

                throw new \Exception($e->getMessage());
            }
        }

        return true;
    }

    /**
     * @param $task
     * @return bool
     * @throws CustomException
     * 执行子任务
     */
    public function runSubTask($task){
        // 获取子任务
        $subTasks = $this->getWaitingSubTasks($task->id);

        foreach($subTasks as $subTask){
            // 获取账户信息
            $oceanAccountModel = new OceanAccountModel();
            $oceanAccount = $oceanAccountModel->where('app_id', $subTask->app_id)
                ->where('account_id', $subTask->account_id)
                ->first();

            // 获取可推送视频
            $video = $this->getCanPushVideo($subTask->n8_material_video_signature, $oceanAccount->company);

            if(!empty($video)){
                // 推送
                $uploadType = 'push';

                $oceanMaterialService = new OceanMaterialService($subTask->app_id);
                $oceanMaterialService->setAccountId($video->account_id);
                $oceanMaterialService->pushMaterial($video->account_id, [$subTask->account_id], [$video->video_id]);
            }else{
                // 上传
                $uploadType = 'upload';

                // 下载
                $file = $this->download($subTask->n8_material_video_path);

                // 上传
                $oceanVideoService = new OceanVideoService($subTask->app_id);
                $oceanVideoService->setAccountId($subTask->account_id);
                $oceanVideoService->uploadVideo($subTask->account_id, $file['signature'], $file['curl_file'], $subTask->n8_material_video_name);

                // 删除临时文件
                unlink($file['path']);
            }

            $subTask->exec_status = ExecStatusEnum::SUCCESS;
            // 上传类型
            $subTask->extends = array_merge($subTask->extends, ['upload_type' => $uploadType]);
            $subTask->save();
        }

        return true;
    }

    /**
     * @param $signature
     * @param $company
     * @return bool|mixed
     * 获取可推送视频
     */
    public function getCanPushVideo($signature, $company){
        $items = DB::select("
            SELECT
                v.video_id, v.signature, a.account_id
            FROM
                ocean_videos v
            LEFT JOIN ocean_accounts_videos av ON v.video_id = av.video_id
            LEFT JOIN ocean_accounts a ON av.account_id = a.account_id
            WHERE
                v.signature = '{$signature}'
            AND a.company = '{$company}'
            LIMIT 1
        ");

        if(empty($items)){
            return false;
        }

        return current($items);
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

        // 获取 mime_type
        $finfo = finfo_open(FILEINFO_MIME);
        $mimeType = finfo_file($finfo, $path);

        // 设置 mime_type
        $curlFile = new \CURLFile($path);
        $curlFile->setMimeType($mimeType);

        return [
            'path' => $path,
            'signature' => md5($content),
            'curl_file' => $curlFile,
        ];
    }
}
