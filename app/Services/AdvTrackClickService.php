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


    public function callback($trackCode, $param){
        $convertType = $param['convert_type'];
        $item  = new ConvertCallbackModel();
        $item->convert_type = $convertType;
        $item->click = $trackCode->extends;
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
