<?php

namespace App\Http\Controllers\Front\Ocean;

use App\Common\Controllers\Front\FrontController;
use App\Common\Enums\ExceptionTypeEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\AdvOceanApiService;
use App\Datas\Ocean\OceanAccountData;
use App\Services\ConvertMatchService;
use App\Services\Ocean\OceanClickService;
use Illuminate\Http\Request;

class IndexController extends FrontController
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
     * spi
     */
    public function spi(Request $request){
        $data = $request->all();
        $errorLogService = new ErrorLogService();
        $errorLogService->create('OCEAN_SPI_LOG', '巨量spi日志', $data, ExceptionTypeEnum::CUSTOM);
        return $this->success();
    }

    /**
     * @param Request $request
     * @return false|string
     * 点击
     */
    public function click(Request $request){
        $data = $request->all();

        $oceanClickService = new OceanClickService();
        $oceanClickService->push($data);

        return json_encode([
            'code' => 0,
            'message' => 'SUCCESS'
        ]);
    }

    public function test(){
        //        $this->testModelData();
        $this->testConvertMatch();
    }

    private function testConvertMatch(){
        $a = new AdvOceanApiService();
        $ret = $a->apiConvertMatch([]);
        dd($ret, 11);
    }

    public function testModelData(){
        $a = new OceanAccountData();
//        $a->setParams(['account_id' => 1672178687569997, 'app_id' => '1646386495126539']);
        $a->setParams(['id' => 123, 'app_id' => 1646386495126539]);
        $item123 = $a->read();
        $a->setParams(['id' => 124]);
        $item124 = $a->read();
        $a->setParams(['id' => 1234]);
        $item1233 = $a->read();
//        $a->clear();
//        $a->clearAll();
//        dd($a->where('id', '>=', 123)->orderBy('id', 'asc')->first());
        dd($item123, $item124, $item1233, 'item');
    }
}
