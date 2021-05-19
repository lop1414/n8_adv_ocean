<?php

namespace App\Http\Controllers\Front\Ocean;

use App\Common\Controllers\Front\FrontController;
use App\Services\Ocean\Report\OceanCreativeReportService;
use Illuminate\Http\Request;

class CreativeReportController extends FrontController
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
     * 获取
     */
    public function get(Request $request){
        $startTime = $request->post('start_time');
        $endTime = $request->post('end_time');
        $groupBy = $request->post('group_by');

        $oceanCreativeReportService = new OceanCreativeReportService();
        $reports = $oceanCreativeReportService->getReports($startTime, $endTime, $groupBy);

        return $this->success($reports);
    }
}
