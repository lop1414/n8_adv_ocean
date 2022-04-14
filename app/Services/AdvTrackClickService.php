<?php

namespace App\Services;

use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Models\ClickModel;
use App\Common\Models\ConvertCallbackModel;
use App\Common\Services\TrackClickService;
use App\Common\Tools\CustomException;


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

        // 事件管理
        if($param['type'] == 'event'){
            if(empty($trackClick)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_CLICK',
                    'message' => '找不到对应点击',
                ]);
            }

            // 自研落地页
            if($param['asset_type'] == 'third_external'){
                $trackClickExtends = $trackClick->extends;
                $trackClickExtends->callback_param = $trackClickExtends->clickid;
                $trackClick->extends = $trackClickExtends;
            }
            $item->click = $trackClick->extends;
            return (new AdvConvertCallbackService())->runAssetEventCallback($item);
        }

        // 转化跟踪
        if($param['type'] == 'adv_convert' && !empty($param['link'])){
            $click = new ClickModel();
            $click->link = $param['link'];
            $item->click = $click;
            return (new AdvConvertCallbackService())->runCallback($item);
        }
    }
}
