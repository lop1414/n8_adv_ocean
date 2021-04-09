<?php

namespace App\Http\Controllers\Front;

use App\Common\Controllers\Front\FrontController;
use App\Services\ConvertMatchService;
use Illuminate\Http\Request;

class ConvertController extends FrontController
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
     * 匹配
     */
    public function match(Request $request){
        $this->validRule($request->post(), [
            'converts' => 'required|array|max:10'
        ]);

        ini_set('max_execution_time', 60);

        $converts = $request->post('converts');

        $convertMatchService = new ConvertMatchService();
        $data = $convertMatchService->match($converts);

        return $this->success($data);
    }
}
