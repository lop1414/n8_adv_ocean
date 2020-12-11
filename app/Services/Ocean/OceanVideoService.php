<?php

namespace App\Services\Ocean;

use App\Common\Enums\TaskTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountVideoModel;
use App\Models\Ocean\OceanVideoModel;
use App\Services\TaskOceanVideoSyncService;
use App\Services\TaskService;

class OceanVideoService extends OceanService
{
    /**
     * OceanVideoService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $accountId
     * @param $signature
     * @param $file
     * @param string $filename
     * @return mixed
     * @throws CustomException
     * 上传
     */
    public function uploadVideo($accountId, $signature, $file, $filename = ''){
        $this->setAccessToken();

        $ret = $this->sdk->uploadVideo($accountId, $signature, $file, $filename);
Functions::consoleDump($ret);
        // 同步
        if(!empty($ret['video_id'])){
            $taskService = new TaskService();
            $taskData = [
                'name' => '同步巨量视频',
                'task_type' => TaskTypeEnum::OCEAN_VIDEO_SYNC,
                'admin_id' => 0,
            ];
            $taskRet = $taskService->create($taskData);
            if(!$taskRet){
                throw new CustomException([
                    'code' => 'CREATE_TASK_ERROR',
                    'message' => '创建任务失败',
                    'data' => [
                        'task_data' => $taskData,
                    ],
                ]);
            }
            $taskId = $taskService->getModel()->id;

            $taskOceanVideoSyncService = new TaskOceanVideoSyncService();
            $subTaskData = [
                'task_id' => $taskId,
                'app_id' => $this->sdk->getAppId(),
                'account_id' => $accountId,
                'video_id' => $ret['video_id'],
                'admin_id' => 0,
            ];
            $taskOceanVideoSyncService->create($subTaskData);
        }

        return $ret;
    }

    /**
     * @param $accounts
     * @param $filtering
     * @param $pageSize
     * @return array
     * @throws CustomException
     * 并发获取
     */
    public function multiGetVideoList($accounts, $filtering, $pageSize){
        return $this->multiGetPageList('video', $accounts, $filtering, $pageSize);
    }

    /**
     * @param array $option
     * @throws CustomException
     * 同步
     */
    public function syncVideo($option = []){
        ini_set('memory_limit', '2048M');

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = explode(",", $option['account_ids']);
        }

        $filtering = [];
        if(!empty($option['date'])){
            $filtering['start_time'] = Functions::getDate($option['date']);
            $filtering['end_time'] = Functions::getDate($option['date']);
        }

        if(!empty($option['video_ids'])){
            $filtering['video_ids'] = explode(",", $option['video_ids']);
        }

        $accountGroup = $this->getSubAccountGroup($accountIds);

        $t = microtime(1);

        $pageSize = 100;
        $videos = [];
        foreach($accountGroup as $pid => $g){
            $tmp = $this->multiGetVideoList($g, $filtering, $pageSize);
            $videos = array_merge($videos, $tmp);

            // 保存
            foreach($videos as $video) {
                $this->saveVideo($video);
            }
        }

        $t = microtime(1) - $t;
        var_dump($t);
    }

    /**
     * @param $video
     * @return bool
     * 保存
     */
    public function saveVideo($video){
        $oceanVideoModel = new OceanVideoModel();
        $oceanVideo = $oceanVideoModel->where('video_id', $video['id'])->first();

        if(empty($oceanVideo)){
            $oceanVideo = new OceanVideoModel();
        }

        $oceanVideo->video_id = $video['id'];
        $oceanVideo->size = $video['size'];
        $oceanVideo->width = $video['width'];
        $oceanVideo->height = $video['height'];
        $oceanVideo->format = $video['format'];
        $oceanVideo->signature = $video['signature'];
        $oceanVideo->poster_url = $video['poster_url'];
        $oceanVideo->bit_rate = $video['bit_rate'];
        $oceanVideo->duration = $video['duration'];
        $oceanVideo->material_id = $video['material_id'];
        $oceanVideo->source = $video['source'];
        $oceanVideo->create_time = $video['create_time'];
        $oceanVideo->filename = $video['filename'];

        $ret = $oceanVideo->save();

        if($ret){
            // 添加关联关系
            $oceanAccountVideoModel = new OceanAccountVideoModel();
            $oceanAccountVideo = $oceanAccountVideoModel->where('account_id', $video['advertiser_id'])
                ->where('video_id', $video['id'])
                ->first();

            if(empty($oceanAccountVideo)){
                $oceanAccountVideo = new OceanAccountVideoModel();
                $oceanAccountVideo->account_id = $video['advertiser_id'];
                $oceanAccountVideo->video_id = $video['id'];
                $oceanAccountVideo->save();
            }
        }

        return $ret;
    }
}
