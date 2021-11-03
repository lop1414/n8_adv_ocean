<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Enums\StatusEnum;
use App\Common\Models\ConvertCallbackStrategyGroupModel;
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
                $v->convert_callback_strategy_group;
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        $this->curdService->findAfter(function(){
            $this->curdService->findData->convert_callback_strategy;
            $this->curdService->findData->convert_callback_strategy_group;
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
        $this->curdService->addField('convert_callback_strategy_id')->addValidRule('integer');
        $this->curdService->addField('convert_callback_strategy_group_id')->addValidRule('integer');

        $this->curdService->saveBefore(function(){
            if(empty($this->curdService->requestData['convert_callback_strategy_id']) && empty($this->curdService->requestData['convert_callback_strategy_group_id'])){
                throw new CustomException([
                    'code' => 'CONVERT_CALLBACK_STRATEGY_AND_STRATEGY_GROUP_IS_EMPTY',
                    'message' => '请选择回传策略或回传策略组',
                ]);
            }

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

            if(!empty($this->curdService->requestData['convert_callback_strategy_group_id'])){
                $convertCallbackStrategyGroupModel = new ConvertCallbackStrategyGroupModel();
                $strategyGroup = $convertCallbackStrategyGroupModel->find($this->curdService->requestData['convert_callback_strategy_group_id']);
                if(empty($strategyGroup)){
                    throw new CustomException([
                        'code' => 'NOT_FOUND_CONCERT_CALLBACK_STRATEGY_GROUP',
                        'message' => '找不到对应回传策略组',
                    ]);
                }

                if($strategyGroup->status != StatusEnum::ENABLE){
                    throw new CustomException([
                        'code' => 'CONCERT_CALLBACK_STRATEGY_GROUP_IS_NOT_ENABLE',
                        'message' => '该回传策略组已被禁用',
                    ]);
                }
            }
        });
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws CustomException
     * 批量更新
     */
    public function batchUpdate(Request $request){
        $this->validRule($request->post(), [
            'ad_ids' => 'required|array',
            'convert_callback_strategy_id' => 'integer',
            'convert_callback_strategy_group_id' => 'integer',
        ]);

        $adIds = $request->post('ad_ids');
        $convertCallbackStrategyId = $request->post('convert_callback_strategy_id', 0);
        $convertCallbackStrategyGroupId = $request->post('convert_callback_strategy_group_id', 0);

        if(empty($convertCallbackStrategyId) && empty($convertCallbackStrategyGroupId)){
            throw new CustomException([
                'code' => 'CONVERT_CALLBACK_STRATEGY_AND_STRATEGY_GROUP_IS_EMPTY',
                'message' => '请选择回传策略或回传策略组',
            ]);
        }

        if(!empty($convertCallbackStrategyId)){
            // 回传规则是否存在
            $convertCallbackStrategyModel = new ConvertCallbackStrategyModel();
            $strategy = $convertCallbackStrategyModel->find($convertCallbackStrategyId);
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

        if(!empty($convertCallbackStrategyGroupId)){
            // 回传规则是否存在
            $convertCallbackStrategyGroupModel = new ConvertCallbackStrategyGroupModel();
            $strategyGroup = $convertCallbackStrategyGroupModel->find($convertCallbackStrategyGroupId);
            if(empty($strategyGroup)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_CONCERT_CALLBACK_STRATEGY_GROUP',
                    'message' => '找不到对应回传策略组',
                ]);
            }

            if($strategyGroup->status != StatusEnum::ENABLE){
                throw new CustomException([
                    'code' => 'CONCERT_CALLBACK_STRATEGY_GROUP_IS_NOT_ENABLE',
                    'message' => '该回传策略组已被禁用',
                ]);
            }
        }

        $ads = [];
        foreach($adIds as $adId){
            $ad = OceanAdModel::find($adId);
            if(empty($ad)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_AD',
                    'message' => "找不到该计划{{$adId}}",
                ]);
            }
            $ads[] = $ad;
        }

        foreach($ads as $ad){
            $oceanAdExtend = OceanAdExtendModel::find($ad->id);

            if(empty($oceanAdExtend)){
                $oceanAdExtend = new OceanAdExtendModel();
                $oceanAdExtend->ad_id = $ad->id;
            }

            $oceanAdExtend->convert_callback_strategy_id = $convertCallbackStrategyId;
            $oceanAdExtend->convert_callback_strategy_group_id = $convertCallbackStrategyGroupId;
            $oceanAdExtend->save();
        }

        return $this->success();
    }
}
