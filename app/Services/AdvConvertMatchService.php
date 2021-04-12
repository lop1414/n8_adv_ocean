<?php

namespace App\Services;

use App\Common\Enums\ConvertCallbackStatusEnum;
use App\Common\Enums\ConvertCallbackTimeEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Models\ConvertCallbackModel;
use App\Common\Services\ConvertMatchService;
use App\Models\Ocean\OceanAdExtendModel;

class AdvConvertMatchService extends ConvertMatchService
{
    /**
     * @param $click
     * @param $convertType
     * @return array|mixed
     * 获取转化回传规则
     */
    protected function getConvertCallbackStrategy($click, $convertType){
        // 默认策略
        $strategy = [
            ConvertTypeEnum::PAY => [
                'time_range' => ConvertCallbackTimeEnum::HOUR_24,
                'times' => 1,
                'callback_rate' => 100,
            ],
        ];

        // 配置策略
        $adId = $click->ad_id ?? 0;
        $adExtend = OceanAdExtendModel::find($adId);
        if(!empty($adExtend) && !empty($adExtend->convert_callback_strategy()->enable()->first())){
            $strategy = $adExtend->convert_callback_strategy['extends'];
        }

        $convertStrategy = $strategy[$convertType] ?? ['time_range' => ConvertCallbackTimeEnum::NEVER];

        return $convertStrategy;
    }

    /**
     * @param $click
     * @param $convert
     * @return mixed
     * 获取转化回传列表
     */
    protected function getConvertCallbacks($click, $convert){
        $clickDatetime = date('Y-m-d H:i:s', strtotime("-15 days", strtotime($convert['convert_at'])));
        $convertDate = date('Y-m-d', strtotime($convert['convert_at']));
        $convertRange = [
            $convertDate .' 00:00:00',
            $convertDate .' 23:59:59',
        ];

        $adId = $click->ad_id ?? 0;

        $convertCallbackModel = new ConvertCallbackModel();
        $convertCallbacks = $convertCallbackModel->whereRaw("
            click_id IN (
                SELECT id FROM clicks
                    WHERE ad_id = '{$adId}' 
                        AND click_at BETWEEN '{$clickDatetime}' AND '{$convert['convert_at']}'
            ) AND convert_at BETWEEN '{$convertRange[0]}' AND '{$convertRange[1]}'
            AND convert_type IN ('{$convert['convert_type']}')
        ")->get();

        return $convertCallbacks;
    }
}
