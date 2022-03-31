<?php

namespace App\Services;

use App\Common\Enums\ConvertTypeEnum;
use App\Common\Tools\CustomException;
use App\Common\Services\ConvertCallbackService;
use App\Models\Ocean\OceanAdModel;

class AdvConvertCallbackService extends ConvertCallbackService
{

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

        if(empty($adInfo->extends->asset_ids)){
            // 转化跟踪回传
            return $this->runCallback($item);
        }else{
            // 事件管理回传
            return $this->runAssetEventCallback($item);
        }
    }




    /**
     * @param $item
     * @param null $eventType
     * @return array
     * @throws CustomException
     * 事件管理回传
     */
    public function runAssetEventCallback($item,$eventType = null){

        if(!empty($item->click->link)){
            $tmp = parse_url($item->click->link);
            parse_str($tmp['query'], $query);
            $callback = $query['clickid'];
        }else{
            $callback = $item->click->callback_param;
        }

        $eventTypeMap = $this->getAssetEventType();
        $param = [
            'event_type' => $eventType ?: $eventTypeMap[$item->convert_type],
            'context'   => [
                'ad' => ['callback' => $callback]
            ],
            'timestamp' => strtotime($item->convert_at) * 1000
        ];

//        if(!empty($item->extends->convert->amount)){
//            // 付费金额
//            $param['properties'] = ['pay_amount' => $item->extends->convert->amount];
//        }

        $ret = $this->postCallback($param);
        $result = json_decode($ret, true);

        $retData = ['param' => $param, 'result' => $result];
        if(!isset($result['code']) || $result['code'] != 0){
            throw new CustomException([
                'code' => 'OCEAN_CONVERT_CALLBACK_ERROR',
                'message' => '巨量转化回传事件失败',
                'log' => true,
                'data' => $retData,
            ]);
        }
        return $retData;
    }




    /**
     * @param $item
     * @param null $eventType
     * @return array
     * @throws CustomException
     * 转化跟踪回传
     */
    public function runCallback($item,$eventType = null){

        $props = [];
//        if(!empty($item->extends->convert->amount)){
//            // 付费金额
//            $props = ['pay_amount' => $item->extends->convert->amount * 100];
//        }

        $eventTypeMap =  $this->getEventTypeMap();

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
        $retData = ['param' => $param, 'result' => $result];
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

        return $retData;
    }



    /**
     * @return array
     * 获取转化跟踪回传映射
     */
    public function getEventTypeMap(){
        return [
            ConvertTypeEnum::ACTIVATION => 0,
            ConvertTypeEnum::REGISTER => 0,
            ConvertTypeEnum::ADD_DESKTOP => 1,
            ConvertTypeEnum::PAY => 2,
        ];
    }



    /**
     * @return string[]
     * 获取事件管理回传映射
     */
    public function getAssetEventType(){
        return  [
            ConvertTypeEnum::ACTIVATION => 'active',
            ConvertTypeEnum::REGISTER => 'active',
            ConvertTypeEnum::ADD_DESKTOP => 'active_register',
            ConvertTypeEnum::PAY => 'active_pay',
        ];
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
