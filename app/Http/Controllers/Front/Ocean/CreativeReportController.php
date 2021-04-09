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

    public function hour(Request $request){
        $date = $request->post('date');
        $hour = $request->post('hour');
        $groupBy = $request->post('group_by');

        $oceanCreativeReportService = new OceanCreativeReportService();
        $reports = $oceanCreativeReportService->getReportByHour($date, $hour, $groupBy);

        return $this->success($reports);
    }
}
