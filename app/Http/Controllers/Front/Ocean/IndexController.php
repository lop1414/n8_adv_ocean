<?php

namespace App\Http\Controllers\Front\Ocean;

use App\Common\Controllers\Front\FrontController;
use App\Common\Enums\ConvertTypeEnum;
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
        $ret = $a->apiConvertMatch([
            [
                'convert_type' => ConvertTypeEnum::PAY, // 转化类型
                'convert_id' => 6666, // 转化id
                'convert_at' => '2021-03-31 12:05:00', // 转化时间
                'convert_times' => 1, // 转化次数(包含当前转化)
                'request_id' => '',
                'muid' => '',
                'oaid' => '',
                'oaid_md5' => '',
                'ip' => '127.0.0.1',
                'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.',
                // 联运用户信息
                'n8_union_user' => [
                    'guid' => 1,
                    'channel_id' => 228,
                    'created_at' => '2021-03-31 12:00:00',
                ],
            ],[
                'convert_type' => ConvertTypeEnum::PAY, // 转化类型
                'convert_id' => 6666, // 转化id
                'convert_at' => '2021-03-31 12:05:00', // 转化时间
                'convert_times' => 1, // 转化次数(包含当前转化)
                'request_id' => '',
                'muid' => '',
                'oaid' => '',
                'oaid_md5' => '',
                'ip' => '127.0.0.1',
                'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.',
                // 联运用户信息
                'n8_union_user' => [
                    'guid' => 1,
                    'channel_id' => 228,
                    'created_at' => '2021-03-31 12:00:00',
                ],
            ],
        ]);
        dd($ret, 'testConvertMatch');
    }

    private function testConvertCallbackGet(){
        $a = new AdvOceanApiService();
        $ret = $a->apiGetConvertCallbacks([
            [
                'convert_type' => ConvertTypeEnum::PAY, // 转化类型
                'convert_id' => 5555, // 转化id
            ],[
                'convert_type' => ConvertTypeEnum::PAY, // 转化类型
                'convert_id' => 6666, // 转化id
            ],
        ]);
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
