<?php

namespace App\Http\Controllers\Front\Ocean;

use App\Common\Controllers\Front\FrontController;
use App\Common\Enums\AdvClickSourceEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Enums\ExceptionTypeEnum;
use App\Common\Enums\MaterialTypeEnums;
use App\Common\Enums\PlatformEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\AdvOceanApiService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomLock;
use App\Common\Tools\CustomRedis;
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
//        $this->testConvertMatch();
//        $this->testConvertCallbackGet();
//        $this->testCreateClick();
//        $this->testUpdateChannelAd();
//        $this->redisSelect();
//        $this->exception();
//        $this->testGetChannelAds();
        $this->testGetMaterialCreative();
    }

    private function testGetMaterialCreative(){
        $a = new AdvOceanApiService();
        $data = $a->apiGetMaterialStat([16219, 16687, 111], MaterialTypeEnums::VIDEO);
        dd($data);
    }

    public function exception(){
        try{
            throw new CustomException([
                'code' => 'aa',
                'message' => 'bb',
            ]);
        }catch (\Exception $e){
            dd($e->getMessage());
        }
    }

    public function redisSelect(){

        $b = new CustomRedis();
        $b->set('ttb', 1);

        $a = new CustomLock('tta');
        $a->set();

        $c = new CustomRedis();
        $c->set('ttc', 1);

//        $a->del();
        dd(11);
    }

    private function testCreateClick(){
        $data = [
            'click_at' => '1612583307000',
            'ad_id' => '123123123123',
            'ip' => '6.6.6.9',
            'ua' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.',
        ];
        $a = new AdvOceanApiService();
        $ret = $a->apiCreateClick($data, AdvClickSourceEnum::N8_TRANSFER);
        dd($ret);
    }

    private function testConvertMatch(){
        $converts = [
            [
                'convert_type' => ConvertTypeEnum::PAY, // 转化类型
                'convert_id' => 444, // 转化id
                'convert_at' => '2021-07-15 12:05:00', // 转化时间
                'convert_times' => 1, // 转化次数(包含当前转化)
                "amount" => 28,
                'request_id' => 'request_id',
                'muid' => '',
                'oaid' => '',
                'oaid_md5' => '',
                'ip' => '127.0.0.1',
                'ua' => 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.198 Mobile Safari/537.36',
                // 联运用户信息
                'n8_union_user' => [
                    'guid' => 1,
                    'channel_id' => 228,
                    'created_at' => '2021-07-15 12:00:00',
                    'click_source' => AdvClickSourceEnum::ADV_CLICK_API,
                ],
            ],
        ];
        $a = new AdvOceanApiService();
        $ret = $a->apiConvertMatch($converts);
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

    public function testUpdateChannelAd(){
        $channelId = 228;

        $adIds = [
            '1690757112343566',
            '1690757111708711',
        ];

        $a = new AdvOceanApiService();
        $ret = $a->apiUpdateChannelAd($channelId, $adIds, PlatformEnum::DEFAULT, ['book_id' => 11, 'product_id' => 33]);
        dd($ret);
    }

    public function testGetChannelAds(){
        $a = new AdvOceanApiService();
        $data = $a->apiGetChannelAds('2021-07-01 00:00:00', '2021-07-08 23:59:59');
        dd($data);
    }
}
