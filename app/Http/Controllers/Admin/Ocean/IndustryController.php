<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Models\Ocean\OceanIndustryModel;
use Illuminate\Http\Request;

class IndustryController extends OceanController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanIndustryModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function getPrepare(){
        $this->curdService->addField('parent_id')->addValidRule('present');
        $this->curdService->getQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                $parentId = $this->curdService->requestData['parent_id'];
                $builder->where('parent_id', $parentId);
            });
        });
    }
}
