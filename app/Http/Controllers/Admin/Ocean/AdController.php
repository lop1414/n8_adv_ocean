<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAdModel;
use App\Models\Ocean\OceanCampaignModel;
use App\Services\Ocean\OceanService;
use Illuminate\Http\Request;

class AdController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanAdModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        parent::selectPrepare();

        $this->curdService->selectQueryAfter(function(){
            foreach($this->curdService->responseData['list'] as $v){
                //$this->curdService->getModel()->expandExtendsField($v);
                unset($v->extends);
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        parent::readPrepare();

        $this->curdService->findAfter(function(){
            $this->curdService->getModel()->expandExtendsField($this->curdService->findData);
        });
    }
}
