<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Models\Ocean\OceanCityModel;
use Illuminate\Http\Request;

class CityController extends OceanController
{
    /**
     * @var string
     * 默认排序字段
     */
    protected $defaultOrderBy = 'id';

    /**
     * @var string
     * 默认排序方式
     */
    protected $defaultOrderType = 'asc';

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new OceanCityModel();

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

    /**
     * 树预处理
     */
    public function treePrepare(){

    }
}
