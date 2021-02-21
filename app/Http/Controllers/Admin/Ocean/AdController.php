<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAdModel;
use App\Services\Ocean\OceanService;
use Illuminate\Http\Request;

class AdController extends OceanController
{
    /**
     * @var string
     * 默认排序字段
     */
    protected $defaultOrderBy = 'ad_modify_time';

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
                // 关联巨量账户
                $v->ocean_account;

                // 关联报表
                $v->report = $v->ocean_creative_reports()->compute()->first();

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
            // 关联巨量账户
            $this->curdService->findData->ocean_account;

            $this->curdService->getModel()->expandExtendsField($this->curdService->findData);
        });
    }
}
