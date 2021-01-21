<?php

namespace App\Http\Controllers\Admin\SubTask;

use App\Common\Controllers\Admin\SubTaskController;
use App\Sdks\OceanEngine\OceanEngine;

class SubTaskOceanController extends SubTaskController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        $this->curdService->selectQueryAfter(function(){
            foreach($this->curdService->responseData['list'] as $item){
                $item->fail_reason = $this->getFailReason($item->fail_data);
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        parent::readPrepare();

        $this->curdService->findAfter(function(){
            $this->curdService->findData->fail_reason = $this->getFailReason($this->curdService->findData->fail_data);
        });
    }

    /**
     * @param $failData
     * @return string
     * 获取失败原因
     */
    public function getFailReason($failData){
        if(empty($failData['result'])){
            return '';
        }

        $code = $failData['result']['code'] ?? '';
        $message = $failData['result']['message'] ?? '';

        $sdk = new OceanEngine();
        $map = $sdk->getCodeMessageMap();

        if(isset($map[$code])){
            $failReason = $map[$code];
        }else{
            $failReason = $message;
        }

        return $failReason;
    }
}
