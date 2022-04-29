<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\MaterialApiService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Sdks\OceanEngine\OceanEngine;
use App\Services\Ocean\OceanVideoService;
use App\Services\Task\TaskOceanVideoUploadService;
use Illuminate\Http\Request;

class VideoController extends OceanController
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
        $oceanVideoService = new OceanVideoService($oceanAccount->app_id);
        $oceanVideoService->setAccountId($oceanAccount->account_id);
        $data = $oceanVideoService->uploadVideo($oceanAccount->account_id, $signature, $curlFile);

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
            'video_ids' => 'required|array'
        ]);

        $accountIds = $request->post('account_ids');
        $videoIds = $request->post('video_ids');

        $maxAccount = 10;
        if(count($accountIds) > $maxAccount){
            throw new CustomException([
                'code' => 'MORE_THAN_MAX_ACCOUNT',
                'message' => "每次最多同步{$maxAccount}个账户",
            ]);
        }

        // 获取后台用户信息
        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        $taskOceanVideoUploadService = new TaskOceanVideoUploadService();
        $has = $taskOceanVideoUploadService->hasAdminUserWaitingTask($adminUserInfo['admin_user']['id']);
        if($has){
            throw new CustomException([
                'code' => 'HAS_WAITING_TASK',
                'message' => '有待执行任务尚未完成,无法继续提交任务',
            ]);
        }

        // 获取视频
        $materialApiService = new MaterialApiService();
        $videos = $materialApiService->apiGetVideos($videoIds);
        if(empty($videos)){
            throw new CustomException([
                'code' => 'NOT_FOUND_VIDEO',
                'message' => '找不到对应视频',
            ]);
        }

        // 视频校验
        $invalidVideos = [];
        $oceanEngine = new OceanEngine('');
        foreach($videos as $video){
            $valid = $oceanEngine->validVideo($video['width'], $video['height'], $video['size'], $video['duration']);
            if(!$valid){
                $invalidVideos[] = $video;
            }
        }

        if(!empty($invalidVideos)){
            $invalidVideoNames = array_column($invalidVideos, 'name');
            $invalidVideoNamesStr = implode(",", $invalidVideoNames);
            throw new CustomException([
                'code' => 'VIDEO_INVALID',
                'message' => "非法尺寸视频 ({$invalidVideoNamesStr})",
                'data' => [
                    'invalid_videos' => $invalidVideos,
                ],
            ]);
        }

        // 获取账户
        $oceanAccountModel = new OceanAccountModel();
        $builder = $oceanAccountModel->withPermission()->whereIn('account_id', $accountIds);

        // 非管理员
        if(!$adminUserInfo['is_admin']){
            //$builder->where('admin_id', $adminUserInfo['admin_user']['id']);
        }

        $accounts = $builder->get();
        if(!$accounts->count()){
            throw new CustomException([
                'code' => 'NOT_FOUND_ACCOUNT',
                'message' => '找不到对应账户',
            ]);
        }

        // 创建任务
        $task = [
            'name' => '批量上传巨量视频',
            'admin_id' => $adminUserInfo['admin_user']['id'],
        ];
        $subs = [];
        foreach($accounts as $account){
            foreach($videos as $video){
                if(!empty($video['source_path'])){
                    $videoPath = $video['path'];
                    $videoSignature = $video['signature'];
                    // 源视频
                    //$videoPath = $video['source_path'];
                    //$videoSignature = $video['source_signature'];
                }else{
                    $videoPath = $video['path'];
                    $videoSignature = $video['signature'];
                }

                $subs[] = [
                    'app_id' => $account->app_id,
                    'account_id' => $account->account_id,
                    'n8_material_video_id' => $video['id'],
                    'n8_material_video_path' => $videoPath,
                    'n8_material_video_name' => $video['name'],
                    'n8_material_video_signature' => $videoSignature,
                    'admin_id' => $adminUserInfo['admin_user']['id'],
                ];
            }
        }
        $taskOceanVideoUploadService->create($task, $subs);

        return $this->success([
            'task_id' => $taskOceanVideoUploadService->taskId,
            'account_count' => $accounts->count(),
            'video_count' => count($videos),
        ], [], '批量上传任务已提交【任务id:'. $taskOceanVideoUploadService->taskId .'】，执行结果后续同步到飞书，请注意查收！');
    }
}
