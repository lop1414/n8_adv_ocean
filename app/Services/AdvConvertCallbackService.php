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
     * @return bool
     * @throws CustomException
     * 回传
     */
    protected function callback($item){

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
            $eventTypeMap =  $this->getEventTypeMap();
            if(!isset($eventTypeMap[$item->convert_type])){
                throw new CustomException([
                    'code' => 'UNDEFINED_EVENT_TYPE_MAP',
                    'message' => '未定义的转化跟踪回传类型映射',
                    'log' => true,
                    'data' => ['item' => $item],
                ]);
            }


            $props = [];
            if(!empty($item->extends->convert->amount)){
                // 付费金额
                $props = ['pay_amount' => $item->extends->convert->amount * 100];
            }
            $this->runCallback($item->click,$eventTypeMap[$item->convert_type],$props);
        }else{
            // 事件管理回传
            $eventTypeMap = $this->getAssetEventType();
            if(!isset($eventTypeMap[$item->convert_type])){
                throw new CustomException([
                    'code' => 'UNDEFINED_EVENT_TYPE_MAP',
                    'message' => '未定义的事件管理回传类型映射',
                    'log' => true,
                    'data' => ['item' => $item],
                ]);
            }

            $props = [];
            if(!empty($item->extends->convert->amount)){
                // 付费金额
                $props = ['pay_amount' => $item->extends->convert->amount];
            }
            $this->runAssetEventCallback($item->click,$eventTypeMap[$item->convert_type],$props);
        }

        return true;
    }


    /**
     * @param $click
     * @param $convertType
     * @param array $props
     * @return bool
     * @throws CustomException
     * 事件管理回传
     */
    public function runAssetEventCallback($click, $convertType, array $props = []){

        if(!empty($click->link)){
            $tmp = parse_url($click->link);
            parse_str($tmp['query'], $query);
            $callback = $query['clickid'];
        }else{
            $callback = $click->callback_param;
        }

        $param = [
            'event_type' => $convertType,
            'context'   => [
                'ad' => ['callback' => $callback,'match_type' => 0]
            ],
            'timestamp' => strtotime($click->convert_at) * 1000
        ];

        if(!empty($props)){
            $param['properties'] = $props;
        }

        $ret = $this->postCallback($param);
        $result = json_decode($ret, true);

        if(!isset($result['code']) || $result['code'] != 0){
            throw new CustomException([
                'code' => 'OCEAN_CONVERT_CALLBACK_ERROR',
                'message' => '巨量转化回传事件失败',
                'log' => true,
                'data' => ['param' => $param, 'result' => $result],
            ]);
        }
        return true;
    }



    /**
     * @param $click
     * @param $convertType
     * @param array $props
     * @return bool
     * @throws CustomException
     * 转化跟踪回传
     */
    public function runCallback($click, $convertType, array $props = []){

        $param = ['event_type' => $convertType];
        if(!empty($click->link)){
            $param['link'] = $click->link;
        }else{
            $param['callback'] = $click->callback_param;
        }

        if(!empty($props)){
            $param['props'] = json_encode($props);
        }

        $url = 'https://ad.oceanengine.com/track/activate/';
        $ret = file_get_contents($url .'?'. http_build_query($param));
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

        return true;
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
