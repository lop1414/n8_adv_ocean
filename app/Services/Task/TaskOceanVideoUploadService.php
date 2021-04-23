<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Task\TaskOceanVideoUploadModel;
use App\Services\Ocean\OceanMaterialService;
use App\Services\Ocean\OceanVideoService;
use Illuminate\Support\Facades\DB;

class TaskOceanVideoUploadService extends TaskOceanService
{
    /**
     * OceanVideoUploadTaskService constructor.
     */
    public function __construct()
    {
        parent::__construct(TaskTypeEnum::OCEAN_VIDEO_UPLOAD);
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
            'n8_material_video_id' => 'required',
            'n8_material_video_path' => 'required',
            'n8_material_video_name' => 'required',
            'n8_material_video_signature' => 'required',
        ]);

        $model = new TaskOceanVideoUploadModel();
        $model->task_id = $taskId;
        $model->app_id = $data['app_id'];
        $model->account_id = $data['account_id'];
        $model->n8_material_video_id = $data['n8_material_video_id'];
        $model->n8_material_video_path = $data['n8_material_video_path'];
        $model->n8_material_video_name = $data['n8_material_video_name'];
        $model->n8_material_video_signature = $data['n8_material_video_signature'];
        $model->exec_status = ExecStatusEnum::WAITING;
        $model->admin_id = $data['admin_id'] ?? 0;
        $model->extends = $data['extends'] ?? [];

        return $model->save();
    }

    /**
     * @param $subTask
     * @return bool|void
     * @throws CustomException
     * 执行单个子任务
     */
    public function runSub($subTask){
        // 获取账户信息
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccount = $oceanAccountModel->where('app_id', $subTask->app_id)
            ->where('account_id', $subTask->account_id)
            ->first();

        // 获取可推送视频
        $video = $this->getCanPushVideo($subTask->n8_material_video_signature, $oceanAccount);

        if(!empty($video)){
            // 推送
            $uploadType = 'push';

            $oceanMaterialService = new OceanMaterialService($subTask->app_id);
            $oceanMaterialService->setAccountId($video->account_id);
            $oceanMaterialService->pushMaterial($video->account_id, [$subTask->account_id], [$video->id]);
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

        // 上传类型
        $subTask->extends = array_merge($subTask->extends, ['upload_type' => $uploadType]);

        return true;
    }

    /**
     * @param $signature
     * @param $oceanAccount
     * @return bool|mixed
     * 获取可推送视频
     */
    public function getCanPushVideo($signature, $oceanAccount){
        $enable = StatusEnum::ENABLE;
        $items = DB::select("
            SELECT
                v.id, v.signature, a.account_id
            FROM
                ocean_videos v
            LEFT JOIN ocean_accounts_videos av ON v.id = av.video_id
            LEFT JOIN ocean_accounts a ON av.account_id = a.account_id
            WHERE
                v.signature = '{$signature}'
                AND a.status = '{$enable}'
                AND a.company = '{$oceanAccount->company}'
                AND a.account_id != {$oceanAccount->account_id}
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
