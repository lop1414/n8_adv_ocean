<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Enums\ResponseCodeEnum;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanDeliveryRangeEnum;
use App\Enums\Ocean\OceanLandingTypeEnum;
use App\Models\Ocean\OceanAudienceTempleteModel;
use App\Models\Ocean\OceanCityModel;
use Illuminate\Http\Request;

class AudienceTempleteController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanAudienceTempleteModel();

        parent::__construct();
    }

    /**
     * 创建预处理
     */
    public function createPrepare(){
        $this->saveHandle();

        $this->curdService->saveBefore(function(){
            $adminUserInfo = Functions::getGlobalData('admin_user_info');

            $oceanAudienceTempleteModel = new OceanAudienceTempleteModel();
            $oceanAudienceTemplete = $oceanAudienceTempleteModel->where('admin_id', $adminUserInfo['admin_user']['id'])
                ->where('name', $this->curdService->handleData['name'])
                ->first();

            if(!empty($oceanAudienceTemplete)){
                throw new CustomException([
                    'code' => 'MAME_EXIST',
                    'message' => '名称已存在',
                ]);
            }
        });
    }

    /**
     * 更新预处理
     */
    public function updatePrepare(){
        $this->saveHandle();

        $this->curdService->saveBefore(function(){
            $adminUserInfo = Functions::getGlobalData('admin_user_info');

            $oceanAudienceTempleteModel = new OceanAudienceTempleteModel();
            $oceanAudienceTemplete = $oceanAudienceTempleteModel->where('admin_id', $adminUserInfo['admin_user']['id'])
                ->where('name', $this->curdService->handleData['name'])
                ->where('id', '<>', $this->curdService->handleData['id'])
                ->first();

            if(!empty($oceanAudienceTemplete)){
                throw new CustomException([
                    'code' => 'MAME_EXIST',
                    'message' => '名称已存在',
                ]);
            }
        });
    }

    /**
     * 保存处理
     */
    private function saveHandle(){
        $this->curdService->addField('audience')->addValidRule('required');
        $this->curdService->addField('audience.name')->addValidRule('required|max:100');
        $this->curdService->addField('audience.landing_type')->addValidRule('required');
        $this->curdService->addField('audience.delivery_range')->addValidRule('required');
        $this->curdService->addField('estimate')->addValidRule('required');

        $this->curdService->saveBefore(function(){
            $audience = $this->curdService->requestData['audience'];

            // 名称
            $this->curdService->handleData['name'] = $audience['name'];

            // 描述
            $this->curdService->handleData['description'] = $audience['description'] ?? '';

            // 推广类型
            Functions::hasEnum(OceanLandingTypeEnum::class, $audience['landing_type']);
            $this->curdService->handleData['landing_type'] = $audience['landing_type'];

            // 投放范围
            Functions::hasEnum(OceanDeliveryRangeEnum::class, $audience['delivery_range']);
            $this->curdService->handleData['delivery_range'] = $audience['delivery_range'];

            $adminUserInfo = Functions::getGlobalData('admin_user_info');
            $this->curdService->handleData['admin_id'] = $adminUserInfo['admin_user']['id'];
        });
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        $this->curdService->selectQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $adminUserInfo = Functions::getGlobalData('admin_user_info');
                $builder->where('admin_id', $adminUserInfo['admin_user']['id']);
            });
        });

        $this->curdService->selectQueryAfter(function(){
            $cityIds = [];
            foreach($this->curdService->responseData['list'] as $item){
                if(!empty($item->audience->city)){
                    $cityIds = array_merge($item->audience->city, $cityIds);
                }
            }
            $oceanCityModel = new OceanCityModel();
            $cityMap = $oceanCityModel->whereIn('id', $cityIds)->pluck('name', 'id');
            foreach($this->curdService->responseData['list'] as $item){
                if(!empty($item->audience->city)){
                    $cityNames = [];
                    foreach($item->audience->city as $cityId){
                        $cityNames[] = $cityMap[$cityId];
                    }
                    $item->city_names = $cityNames;
                }
            }
        });
    }

    /**
     * 详情预处理
     */
    public function readPrepare(){
        $this->curdService->findAfter(function(){
            if(!$this->isSelf($this->curdService->findData)){
                throw new CustomException([
                    'code' => ResponseCodeEnum::FORBIDDEN,
                    'message' => '无权限操作',
                ]);
            }
        });
    }

    /**
     * 删除预处理
     */
    public function deletePrepare(){
        $this->curdService->removeBefore(function(){
            if(!$this->isSelf($this->curdService->findData)){
                throw new CustomException([
                    'code' => ResponseCodeEnum::FORBIDDEN,
                    'message' => '无权限操作',
                ]);
            }
        });
    }

    /**
     * @param $audienceTemplete
     * @return bool
     * 是否自身
     */
    private function isSelf($audienceTemplete){
        $adminUserInfo = Functions::getGlobalData('admin_user_info');
        return $adminUserInfo['admin_user']['id'] == $audienceTemplete->admin_id;
    }
}
