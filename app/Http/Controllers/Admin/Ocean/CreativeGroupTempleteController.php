<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Enums\ResponseCodeEnum;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanCreativeGroupTempleteModel;
use Illuminate\Http\Request;

class CreativeGroupTempleteController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCreativeGroupTempleteModel();

        parent::__construct();
    }

    /**
     * 创建预处理
     */
    public function createPrepare(){
        $this->saveHandle();

        $this->curdService->saveBefore(function(){
            $adminUserInfo = Functions::getGlobalData('admin_user_info');

            $model = new OceanCreativeGroupTempleteModel();
            $templete = $model->where('admin_id', $adminUserInfo['admin_user']['id'])
                ->where('name', $this->curdService->handleData['name'])
                ->first();

            if(!empty($templete)){
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

            $model = new OceanCreativeGroupTempleteModel();
            $templete = $model->where('admin_id', $adminUserInfo['admin_user']['id'])
                ->where('name', $this->curdService->handleData['name'])
                ->where('id', '<>', $this->curdService->handleData['id'])
                ->first();

            if(!empty($templete)){
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
        $this->curdService->addField('name')->addValidRule('required');
        $this->curdService->addField('data')->addValidRule('required');

        $this->curdService->saveBefore(function(){
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
     * @param $item
     * @return bool
     * 是否自身
     */
    private function isSelf($item){
        $adminUserInfo = Functions::getGlobalData('admin_user_info');
        return $adminUserInfo['admin_user']['id'] == $item->admin_id;
    }
}
