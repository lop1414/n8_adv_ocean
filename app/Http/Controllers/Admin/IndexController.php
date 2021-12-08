<?php

namespace App\Http\Controllers\Admin;

use App\Common\Controllers\Admin\AdminController;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Services\IndexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends AdminController
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
     * 投放监控
     */
    public function adDashboard(Request $request){
        $requestData = $request->post();

        $indexService = new IndexService();
        $data = $indexService->getAdDashboard($requestData);

        return $this->successWithFix($data);
    }
}
