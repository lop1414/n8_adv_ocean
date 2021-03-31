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

    public function selectPrepare(){

    }

    public function readPrepare(){

    }

    public function createPrepare(){
        $this->saveHandle();

        $this->curdService->addField('status')->addDefaultValue(StatusEnum::ENABLE);
    }

    public function updatePrepare(){
        $this->saveHandle();
    }

    /**
     * 保存校验
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
