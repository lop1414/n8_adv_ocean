<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Enums\ConvertTypeEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Models\Ocean\OceanConvertCallbackStrategyModel;
use Illuminate\Http\Request;

class ConvertCallbackStrategyController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanConvertCallbackStrategyModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        $this->curdService->selectQueryBefore(function(){
            $this->privateFilter();
        });
    }

    /**
     * 列表(无分页)预处理
     */
    public function getPrepare(){
        $this->curdService->getQueryBefore(function(){
            $this->privateFilter();
        });
    }

    /**
     * 私有过滤
     */
    private function privateFilter(){
        $this->curdService->customBuilder(function($builder){
            $adminUserInfo = Functions::getGlobalData('admin_user_info');
            if(!$adminUserInfo['is_admin']){
                $builder->whereRaw("
                        (is_private = 0 OR admin_id = {$adminUserInfo['admin_user']['id']})
                    ");
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){}

    /**
     * 创建预处理
     */
    public function createPrepare(){
        $this->saveHandle();

        $this->curdService->addField('status')->addDefaultValue(StatusEnum::ENABLE);
    }

    /**
     * 更新预处理
     */
    public function updatePrepare(){
        $this->saveHandle();
    }

    /**
     * 保存处理
     */
    private function saveHandle(){
        $this->curdService->addField('name')->addValidRule('required|max:100');
        $this->curdService->addField('is_private')->addValidRule('required|boolean');
        $this->curdService->addField('status')->addValidEnum(StatusEnum::class);

        $this->curdService->saveBefore(function(){
            // 充值转化
            $pay = ConvertTypeEnum::PAY;

            $this->validRule($this->curdService->requestData, [
                "{$pay}.time_range" => 'required|string',
                "{$pay}.convert_times" => 'required|integer',
                "{$pay}.callback_rate" => 'required|integer|max:100|min:0',
            ]);

            $extends = [
                "{$pay}" => $this->curdService->requestData["{$pay}"],
            ];

            $this->curdService->handleData['extends'] = $extends;

            $adminUserInfo = Functions::getGlobalData('admin_user_info');
            $this->curdService->handleData['admin_id'] = $adminUserInfo['admin_user']['id'];
        });
    }
}
