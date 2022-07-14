<?php

namespace App\Services;

use App\Common\Enums\ConvertTypeEnum;
use App\Common\Tools\CustomException;
use App\Common\Services\ConvertCallbackService;
use App\Models\Ocean\OceanAdModel;

class AdvConvertCallbackService extends ConvertCallbackService
{
    /**
     * 回传金额
     * @var bool
     */
    protected $callbackAmount = true;

    /**
     * @param $item
     * @return array
     * @throws CustomException
     * 回传
     */
    public function callback($item){

        // 关联点击
        if(empty($item->click)){
            throw new CustomException([
                'code' => 'NOT_FOUND_CONVERT_CLICK',
                'message' => '找不到该转化对应点击',
                'log' => true,
                'data' => [
                    'item' => $item,
                ],
            ]);
        }

        $adInfo = (new OceanAdModel())->where('id',$item->click->ad_id)->first();
        if(empty($adInfo)){
            throw new CustomException([
                'code' => 'NOT_AD_INFO', 'message' => '未找到计划信息', 'log' => true, 'data' => ['item' => $item],
            ]);
        }

        $convertType = $adInfo->extends->external_action ?? null;

        if(empty($adInfo->extends->asset_ids)){
            // 转化跟踪回传
            return $this->runCallback($item,null,$convertType);
        }else{
            // 事件管理回传
            return $this->runAssetEventCallback($item,null,$convertType);
        }
    }




    /**
     * 事件管理回传
     * @param $item
     * @param null $eventType
     * @param null $convertType
     * @return array
     * @throws CustomException
     */
    public function runAssetEventCallback($item,$eventType = null,$convertType = null){

        if(!empty($item->click->link)){
            $tmp = parse_url($item->click->link);
            parse_str($tmp['query'], $query);


            if(!isset($query['clickid'])){
                throw new CustomException([
                    'code' => 'NOT_CLICK_ID_ERROR',
                    'message' => '缺少clickid参数',
                    'log' => true,
                    'data' => $item,
                ]);
            }
            $callback = $query['clickid'];

        }else{
            $callback = $item->click->callback_param;
        }

        $eventTypeMap = $this->getAssetEventType($convertType);
        $param = [
            'event_type' => $eventType ?: $eventTypeMap[$item->convert_type],
            'context'   => [
                'ad' => ['callback' => $callback]
            ],
            'timestamp' => strtotime($item->convert_at) * 1000
        ];

        if(!empty($item->extends->convert->amount) && $this->callbackAmount){
            // 付费金额
            $param['properties'] = ['pay_amount' => $item->extends->convert->amount * 100];
        }

        $ret = $this->postCallback($param);
        $result = json_decode($ret, true);

        if(!isset($result['code']) || $result['code'] != 0){
            throw new CustomException([
                'code' => 'OCEAN_CONVERT_CALLBACK_ERROR',
                'message' => '巨量转化回传事件失败',
                'log' => true,
                'data' => [
                    'param' => $param,
                    'result' => $result,
                ],
            ]);
        }
        return ['param' => $param, 'result' => $result];
    }




    /**
     * 转化跟踪回传
     * @param $item
     * @param null $eventType
     * @param null $convertType
     * @return array
     * @throws CustomException
     */
    public function runCallback($item,$eventType = null,$convertType = null){

        $props = [];
        if(!empty($item->extends->convert->amount) && $this->callbackAmount){
            // 付费金额
            $props = ['pay_amount' => $item->extends->convert->amount * 100];
        }

        $eventTypeMap =  $this->getEventTypeMap($convertType);

        $param = [
            'event_type' => $eventType ?: $eventTypeMap[$item->convert_type],
            'conv_time'  => strtotime($item->convert_at),
            'props'      => json_encode($props)
        ];


        if(!empty($item->click->link)){
            $param['link'] = $item->click->link;
        }else{
            $param['callback'] = $item->click->callback_param;
        }

        $url = 'https://ad.oceanengine.com/track/activate/'.'?'. http_build_query($param);
        $ret = file_get_contents($url);
        $result = json_decode($ret, true);

        if(!isset($result['code']) || $result['code'] != 0){
            throw new CustomException([
                'code' => 'OCEAN_CONVERT_CALLBACK_ERROR',
                'message' => '巨量转化回传失败',
                'log' => true,
                'data' => [
                    'url' => $url,
                    'param' => $param,
                    'result' => $result,
                ],
            ]);
        }

        return ['param' => $param, 'result' => $result];
    }



    /**
     * 获取转化跟踪回传映射
     * @param null $convertType 转化类型
     * @return int[]
     */
    public function getEventTypeMap($convertType = null): array
    {
        // 默认
        $arr = [
            ConvertTypeEnum::ACTIVATION => 0,
            ConvertTypeEnum::REGISTER => 0,
            ConvertTypeEnum::ADD_DESKTOP => 0,
            ConvertTypeEnum::PAY => 2,
        ];

        if($convertType == 'AD_CONVERT_TYPE_WECHAT_PAY'){
            // 微信内付费
            $arr[ConvertTypeEnum::REGISTER] = 0;
            $arr[ConvertTypeEnum::PAY] = 2;
        }

        return $arr;
    }



    /**
     * 获取事件管理回传映射
     * @param null $convertType 转化类型
     * @return string[]
     */
    public function getAssetEventType($convertType = null): array
    {
        // 默认
        $arr =  [
            ConvertTypeEnum::ACTIVATION => 'active',
            ConvertTypeEnum::REGISTER => 'active',
            ConvertTypeEnum::ADD_DESKTOP => 'active',
            ConvertTypeEnum::PAY => 'active_pay',
        ];

        if($convertType == 'AD_CONVERT_TYPE_WECHAT_PAY'){
            // 微信内付费
            $arr[ConvertTypeEnum::REGISTER] = 'in_wechat_login' ;
            $arr[ConvertTypeEnum::PAY] = 'in_wechat_pay' ;
        }

        return $arr;
    }



    /**
     * @param $click
     * @return array|void
     * 点击数据过滤
     */
    public function filterClickData($click){
        return [
            'id' => $click['id'],
            'campaign_id' => $click['campaign_id'],
            'ad_id' => $click['ad_id'],
            'creative_id' => $click['creative_id'],
            'click_at' => $click['click_at'],
        ];
    }



    /**
     * @param $data
     * @return bool|string
     * 事件管理回传post请求
     */
    public function postCallback($data){
        $curl = curl_init();
        $data = json_encode($data);

        $privateKey = "hOXfVYVcFAYrqTjNtWyEwsZsQSKhHFFK";
        $hash = hash("sha256", $data . $privateKey);
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://analytics.oceanengine.com/api/v2/conversion',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "content-type: application/json",
//                "x-signature: " . $hash
            ]
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }
}
