<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\ChannelAdModel;
use App\Models\Ocean\OceanAdModel;

class ChannelAdService extends BaseService
{
    /**
     * @param $data
     * @return bool
     * @throws CustomException
     * 更新
     */
    public function update($data){
        $this->validRule($data, [
            'channel_id' => 'required|integer',
            'ad_ids' => 'required|array',
        ]);

        foreach($data['ad_ids'] as $adId){
            $oceanAdChannelModel = new ChannelAdModel();
            $oceanAdChannel = $oceanAdChannelModel->where('channel_id', $data['channel_id'])
                ->where('ad_id', $adId)
                ->first();

            if(empty($oceanAdChannel)){
                $oceanAdChannel = new ChannelAdModel();
                $oceanAdChannel->channel_id = $data['channel_id'];
                $oceanAdChannel->ad_id = $adId;
                $oceanAdChannel->save();
            }
        }

        return true;
    }

    /**
     * @param $data
     * @return array
     * @throws CustomException
     * 详情
     */
    public function read($data){
        $this->validRule($data, [
            'channel_id' => 'required|integer'
        ]);

        $channelAdModel = new ChannelAdModel();
        $adIds = $channelAdModel->where('channel_id', $data['channel_id'])->pluck('ad_id')->toArray();

        $builder = new OceanAdModel();
        $builder = $builder->whereIn('id', $adIds);

        // 过滤
        if(!empty($data['filtering'])){
            $builder = $builder->filtering($data['filtering']);
        }

        $ads = $builder->get();

        foreach($ads as $k => $v){
            unset($ads[$k]['extends']);
        }

        return [
            'channel_id' => $data['channel_id'],
            'list' => $ads
        ];
    }
}
