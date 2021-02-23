<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Common\Services\SystemApi\MaterialApiService;
use App\Models\Task\TaskOceanImageUploadModel;
use Illuminate\Http\Request;

class TaskOceanImageUploadController extends SubTaskOceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new TaskOceanImageUploadModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        $this->curdService->selectQueryAfter(function(){
            $imageIds = $this->curdService->responseData['list']->pluck('n8_material_image_id');
            $imageMap = $this->getImageMap($imageIds);
            foreach($imageIds = $this->curdService->responseData['list'] as $item){
                $item->image = $imageMap[$item->n8_material_image_id] ?? null;
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        parent::readPrepare();

        $this->curdService->findAfter(function(){
            $imageMap = $this->getImageMap([$this->curdService->findData->n8_material_image_id]);
            $this->curdService->findData->image = $imageMap[$this->curdService->findData->n8_material_image_id] ?? null;
        });
    }

    /**
     * @param $imageIds
     * @return array
     * @throws \App\Common\Tools\CustomException
     * 获取图片映射
     */
    private function getImageMap($imageIds){
        $materialApiService = new MaterialApiService();
        $images = $materialApiService->apiGetImages($imageIds);
        $imageMap = array_column($images, null, 'id');
        return $imageMap;
    }
}
