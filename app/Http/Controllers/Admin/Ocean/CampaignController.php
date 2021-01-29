<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanCampaignModel;
use App\Services\Ocean\OceanService;
use Illuminate\Http\Request;

class CampaignController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCampaignModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        $this->curdService->selectQueryAfter(function(){
            foreach($this->curdService->responseData['list'] as $v){
                // 关联巨量账户
                $v->ocean_account;
            }
        });
    }

    /**
     * 列表(无分页)预处理
     */
    public function getPrepare(){
        parent::getPrepare();

        $this->curdService->getQueryAfter(function(){
            foreach($this->curdService->responseData as $v){
                // 关联巨量账户
                $v->ocean_account;
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        parent::readPrepare();

        $this->curdService->findAfter(function(){
            // 关联巨量账户
            $this->curdService->findData->ocean_account;
        });
    }
}
