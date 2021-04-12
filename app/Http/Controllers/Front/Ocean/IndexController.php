<?php

namespace App\Http\Controllers\Front\Ocean;

use App\Common\Controllers\Front\FrontController;
use App\Common\Enums\ExceptionTypeEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\AdvOceanApiService;
use App\Datas\Ocean\OceanAccountData;
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

    public function test(Request $request){
        $key = $request->input('key');
        if($key != 'aut'){
            return $this->forbidden();
        }
//        $this->testModelData();
        $this->testConvertMatch();
//        $this->testConvertCallbackGet();
    }

    private function testConvertMatch(){
        $a = new AdvOceanApiService();
        $ret = $a->apiConvertMatch([]);
        dd($ret, 'testConvertMatch');
    }

    private function testConvertCallbackGet(){
        $a = new AdvOceanApiService();
        $ret = $a->apiGetConvertCallbacks([]);
        dd($ret, 'testConvertCallbackGet');
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
