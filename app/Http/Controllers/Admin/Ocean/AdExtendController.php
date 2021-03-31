<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Enums\StatusEnum;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAdExtendModel;
use App\Models\Ocean\OceanAdModel;
use App\Models\Ocean\OceanConvertCallbackStrategyModel;
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

    public function selectPrepare(){

    }

    public function readPrepare(){

    }

    public function createPrepare(){
        $this->saveValid();
    }

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
                $oceanConvertCallbackStrategyModel = new OceanConvertCallbackStrategyModel();
                $strategy = $oceanConvertCallbackStrategyModel->find($this->curdService->requestData['convert_callback_strategy_id']);
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
