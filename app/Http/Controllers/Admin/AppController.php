<?php

namespace App\Http\Controllers\Admin;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Tools\CustomException;
use App\Models\AppModel;
use Illuminate\Http\Request;

class AppController extends AdminController
{

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new AppModel();

        parent::__construct();
    }


    public function exist($advAlias,$appId){
        $tmp = $this->model
            ->where('adv_alias',$advAlias)
            ->where('app_id',$appId)
            ->first();
        if(!!$tmp){
            throw new CustomException([
                'code' => 'APPID_EXIST',
                'message' => 'App ID已存在',
            ]);
        }
    }


    public function createPrepare(){
        $this->curdService->addField('name')->addValidRule('required|max:16|min:2');
        $this->curdService->addField('adv_alias')->addValidEnum(AdvAliasEnum::class);
        $this->curdService->addField('app_id')->addValidRule('required|alpha_num|max:32|min:1');
        $this->curdService->addField('secret')->addValidRule('required');
        $this->curdService->addField('status')->addValidEnum(StatusEnum::class)->addDefaultValue(StatusEnum::ENABLE);

        $this->curdService->saveBefore(function(){

            $this->exist($this->curdService->handleData['adv_alias'],$this->curdService->handleData['app_id']);
        });
    }


    public function updatePrepare(){
        $this->curdService->addField('name')->addValidRule('required|max:16|min:2');
        $this->curdService->addField('adv_alias')->addValidEnum(AdvAliasEnum::class);
        $this->curdService->addField('app_id')->addValidRule('required|alpha_num|max:32|min:1');
        $this->curdService->addField('secret')->addValidRule('required');
        $this->curdService->addField('status')->addValidEnum(StatusEnum::class)->addDefaultValue(StatusEnum::ENABLE);

        $this->curdService->saveBefore(function (){
            if($this->curdService->handleData['app_id'] != $this->curdService->findData['app_id']
                ||  $this->curdService->handleData['adv_alias'] != $this->curdService->findData['adv_alias']
            ){

                $this->exist($this->curdService->handleData['adv_alias'],$this->curdService->handleData['app_id']);
            }

        });
    }

    public function readPrepare(){
        $this->curdService->findAfter(function (){

            $this->curdService->findData->makeVisible('secret');
        });
    }


}
