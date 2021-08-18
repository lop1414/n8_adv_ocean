<?php

namespace App\Http\Controllers\Front\Ocean;

use App\Common\Controllers\Front\FrontController;
use App\Services\Ocean\OceanMaterialStatService;
use Illuminate\Http\Request;

class MaterialStatController extends FrontController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 列表
     */
    public function get(Request $request){
        $requestData = $request->post();

        $oceanMaterialStatService = new OceanMaterialStatService();
        $data = $oceanMaterialStatService->get($requestData);

        return $this->success($data);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 最新
     */
    public function newest(Request $request){
        $requestData = $request->post();

        $oceanMaterialStatService = new OceanMaterialStatService();
        $data = $oceanMaterialStatService->newest($requestData);

        return $this->success($data);
    }
}
