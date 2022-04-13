<?php

namespace App\Services;

use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Models\ConvertCallbackModel;
use App\Common\Services\TrackClickService;


class AdvTrackClickService extends TrackClickService
{
    public function __construct(){
        parent::__construct(AdvAliasEnum::OCEAN);
    }


    public function callback($trackClick, $param){
        $convertType = $param['convert_type'];
        // 联调页面上报
        if(empty($trackClick->extends->link) && !empty($trackClick->extends->clickid)){
            $trackClick->extends->callback_param = $trackClick->extends->clickid;
        }

        $item  = new ConvertCallbackModel();
        $item->convert_type = $convertType;
        $item->click = $trackClick->extends;
        $item->convert_at = date('Y-m-d H:i:s');
        if($convertType == ConvertTypeEnum::PAY){
            $item->extends =  ['convert' => ['amount' => 1.00]];
        }

        // 转化跟踪回传
        if(isset($param['type']) && $param['type'] == 'adv_convert'){
            return (new AdvConvertCallbackService())->runCallback($item);
        }

        // 事件管理回传 event
        return (new AdvConvertCallbackService())->runAssetEventCallback($item);
    }
}
