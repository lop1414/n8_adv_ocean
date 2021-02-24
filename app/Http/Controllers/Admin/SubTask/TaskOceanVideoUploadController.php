<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Common\Services\SystemApi\MaterialApiService;
use App\Models\Task\TaskOceanVideoUploadModel;
use Illuminate\Http\Request;

class TaskOceanVideoUploadController extends SubTaskOceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new TaskOceanVideoUploadModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        $this->curdService->selectQueryAfter(function(){
            $videoIds = $this->curdService->responseData['list']->pluck('n8_material_video_id')->toArray();
            $videoMap = !empty($videoIds) ? $this->getVideoMap($videoIds) : [];
            foreach($videoIds = $this->curdService->responseData['list'] as $item){
                $item->video = $videoMap[$item->n8_material_video_id] ?? null;
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        parent::readPrepare();

        $this->curdService->findAfter(function(){
            $videoMap = $this->getVideoMap([$this->curdService->findData->n8_material_video_id]);
            $this->curdService->findData->video = $videoMap[$this->curdService->findData->n8_material_video_id] ?? null;
        });
    }

    /**
     * @param $videoIds
     * @return array
     * @throws \App\Common\Tools\CustomException
     * 获取视频映射
     */
    private function getVideoMap($videoIds){
        $materialApiService = new MaterialApiService();
        $videos = $materialApiService->apiGetVideos($videoIds);
        $videoMap = array_column($videos, null, 'id');
        return $videoMap;
    }
}
