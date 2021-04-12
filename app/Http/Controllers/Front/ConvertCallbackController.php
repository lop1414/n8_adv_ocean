<?php

namespace App\Http\Controllers\Front;

use App\Common\Controllers\Front\FrontController;
use App\Services\AdvConvertCallbackService;
use Illuminate\Http\Request;

class ConvertCallbackController extends FrontController
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
        $converts = $request->post('converts');

        $advConvertCallbackService = new AdvConvertCallbackService();
        $items = $advConvertCallbackService->getItemsByConverts($converts);

        return $this->success($items);
    }
}
