<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Enums\StatusEnum;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAdExtendModel;
use App\Models\Ocean\OceanAdModel;
use App\Common\Models\ConvertCallbackStrategyModel;
use Illuminate\Http\Request;

class AdExtendController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanAdExtendModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        $this->curdService->selectQueryAfter(function(){
            foreach($this->curdService->responseData['list'] as $v){
                $v->convert_callback_strategy;
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        $this->curdService->findAfter(function(){
            $this->curdService->findData->convert_callback_strategy;
        });
    }

    /**
     * 创建预处理
     */
    public function createPrepare(){
        $this->saveValid();
    }

    /**
     * 更新预处理
     */
    public function updatePrepare(){
        $this->saveValid();
    }

    /**
     * 保存校验
     */
    private function saveValid(){
        $this->curdService->addField('ad_id')->addValidRule('required');
        $this->curdService->addField('convert_callback_strategy_id')->addValidRule('required|integer');

        $this->curdService->saveBefore(function(){
            $ad = OceanAdModel::find($this->curdService->requestData['ad_id']);
            if(empty($ad)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_AD',
                    'message' => '找不到该计划',
                ]);
            }
            $this->curdService->handleData['ad_id'] = $this->curdService->requestData['ad_id'];

            // 回传规则是否存在
            if(!empty($this->curdService->requestData['convert_callback_strategy_id'])){
                $convertCallbackStrategyModel = new ConvertCallbackStrategyModel();
                $strategy = $convertCallbackStrategyModel->find($this->curdService->requestData['convert_callback_strategy_id']);
                if(empty($strategy)){
                    throw new CustomException([
                        'code' => 'NOT_FOUND_CONCERT_CALLBACK_STRATEGY',
                        'message' => '找不到对应回传策略',
                    ]);
                }

                if($strategy->status != StatusEnum::ENABLE){
                    throw new CustomException([
                        'code' => 'CONCERT_CALLBACK_STRATEGY_IS_NOT_ENABLE',
                        'message' => '该回传策略已被禁用',
                    ]);
                }
            }
        });
    }
}
