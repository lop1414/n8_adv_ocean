<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\CenterApiService;
use App\Common\Tools\CustomException;
use App\Models\OceanAccountModel;

class AccountController extends AdminController
{

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanAccountModel();

        parent::__construct();
    }

    /**
     * 分页列表预处理
     */
    public function selectPrepare(){
        $this->curdService->selectQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $adminUserInfo = Functions::getGlobalData('admin_user_info');
                if(!$adminUserInfo['is_admin']){
                    $builder->where('admin_id', $adminUserInfo['admin_user']['id']);
                }
                $builder->where('parent_id', '<>', 0);
            });
        });
    }

    /**
     * 列表预处理
     */
    public function getPrepare(){
        $this->curdService->getQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $adminUserInfo = Functions::getGlobalData('admin_user_info');
                if(!$adminUserInfo['is_admin']){
                    $builder->where('admin_id', $adminUserInfo['admin_user']['id']);
                }
            });
        });
    }

    /**
     * 更新预处理
     */
    public function updatePrepare(){
        $this->curdService->addField('name')->addValidRule('required|max:16|min:2');
        $this->curdService->addField('admin_id')->addValidRule('required');

        $this->curdService->saveBefore(function (){
            $this->model->existWithoutSelf('name',$this->curdService->handleData['name'],$this->curdService->handleData['id']);

            // 验证admin id
            $adminInfo = (new CenterApiService())->apiReadAdminUser($this->curdService->handleData['admin_id']);
            if($adminInfo['status'] != StatusEnum::ENABLE){
                throw new CustomException([
                    'code' => 'ADMIN_DISABLE',
                    'message' => '该后台用户已被禁用'
                ]);
            }
        });

        // 限制修改的字段
        $this->curdService->handleAfter(function (){
            foreach($this->curdService->handleData as $field => $val){
                if(!in_array($field,['name','admin_id','id'])){
                    unset($this->curdService->handleData[$field]);
                }
            }
        });
    }
}
