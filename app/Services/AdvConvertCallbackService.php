<?php

namespace App\Services;

use App\Common\Enums\ConvertTypeEnum;
use App\Common\Tools\CustomException;
use App\Common\Services\ConvertCallbackService;

class AdvConvertCallbackService extends ConvertCallbackService
{
    /**
     * @param $item
     * @return bool
     * @throws CustomException
     * 回传
     */
    protected function callback($item){
        $eventTypeMap = $this->getEventTypeMap();

        if(!isset($eventTypeMap[$item->convert_type])){
            // 无映射
            throw new CustomException([
                'code' => 'UNDEFINED_EVENT_TYPE_MAP',
                'message' => '未定义的事件类型映射',
                'log' => true,
                'data' => [
                    'item' => $item,
                ],
            ]);
        }

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

        $eventType = $eventTypeMap[$item->convert_type];

        $props = [];
        if(!empty($item->extends->amount)){
            // 付费金额
            $props = ['pay_amount' => $item->extends->amount * 100];
        }

        $this->runCallback($item->click, $eventType, $props);

        return true;
    }

    /**
     * @param $click
     * @param $eventType
     * @param array $props
     * @return bool
     * @throws CustomException
     * 执行回传
     */
    public function runCallback($click, $eventType, $props = []){
        $url = 'https://ad.oceanengine.com/track/activate/';
        $param = [
            'event_type' => $eventType
        ];
        if(!empty($click->link)){
            $param['link'] = $click->link;
        }else{
            $param['callback'] = $click->callback_param;
        }

        if(!empty($props)){
            $param['props'] = json_encode($props);
        }

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
     * 获取事件映射
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
}
