<?php

namespace App\Services;

use App\Common\Enums\AdvClickSourceEnum;
use App\Common\Enums\ConvertCallbackTimeEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Models\ClickModel;
use App\Common\Models\ConvertCallbackModel;
use App\Common\Services\ConvertMatchService;
use App\Common\Services\SystemApi\AdvOceanApiService;
use App\Models\Ocean\OceanAdExtendModel;

class AdvConvertMatchService extends ConvertMatchService
{
    /**
     * @param $click
     * @param $convert
     * @return array|mixed|void
     * 获取转化回传规则
     */
    protected function getConvertCallbackStrategy($click, $convert){
        // 转化类型
        $convertType = $convert['convert_type'];

        // 默认策略
        $strategy = [
            ConvertTypeEnum::PAY => [
                'time_range' => ConvertCallbackTimeEnum::TODAY,
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

        // 兼容历史数据
        if(!isset($convertStrategy['min_amount'])){
            $convertStrategy['min_amount'] = 20;
        }

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

    /**
     * @param $data
     * @return ClickModel|void
     * 获取匹配查询构造器
     */
    protected function getMatchByBuilder($data){
        $builder = new ClickModel();

        if($this->clickSource != AdvClickSourceEnum::N8_TRANSFER){
            $channelId = $data['n8_union_user']['channel_id'] ?? 0;
            if(!empty($channelId)){
                $builder = $builder->whereRaw("
                ad_id IN (
                    SELECT ad_id FROM channel_ads
                        WHERE channel_id = {$channelId}
                )
            ");
            }
        }

        return $builder;
    }
}
