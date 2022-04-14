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


        $item  = new ConvertCallbackModel();
        $item->convert_type = $convertType;
        $item->convert_at = date('Y-m-d H:i:s');
        if($convertType == ConvertTypeEnum::PAY){
            $item->extends =  ['convert' => ['amount' => 1.00]];
        }
        $trackClickExtends = $trackClick->extends;

        // 转化跟踪回传
        if(isset($param['type']) && $param['type'] == 'adv_convert'){
            if(!empty($param['link'])){
                $trackClickExtends->link = $param['link'];
                $trackClick->extends = $trackClickExtends;
            }
            $item->click = $trackClick->extends;
            return (new AdvConvertCallbackService())->runCallback($item);
        }

        // 事件管理回传 event
        if(empty($trackClick->extends->link) && !empty($trackClick->extends->clickid)){
            // 联调落地页上报
            $trackClickExtends->callback_param = $trackClickExtends->clickid;
            $trackClick->extends = $trackClickExtends;
        }

        $item->click = $trackClick->extends;
        return (new AdvConvertCallbackService())->runAssetEventCallback($item);
    }
}
