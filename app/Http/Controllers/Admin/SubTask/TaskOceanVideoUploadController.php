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
            $videoIds = $this->curdService->responseData['list']->pluck('n8_material_video_id');
            $materialApiService = new MaterialApiService();
            $videos = $materialApiService->apiGetVideos($videoIds);
            $videoMap = array_column($videos, null, 'id');
            foreach($videoIds = $this->curdService->responseData['list'] as $item){
                $item->video = $videoMap[$item->n8_material_video_id] ?? null;
            }
        });
    }
}
