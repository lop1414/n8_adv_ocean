<?php

namespace App\Services;

use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\PlatformEnum;
use App\Common\Helpers\Advs;
use App\Common\Helpers\Functions;
use App\Common\Models\ConvertCallbackStrategyGroupModel;
use App\Common\Models\ConvertCallbackStrategyModel;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\NoticeApiService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Models\Ocean\ChannelAdLogModel;
use App\Models\Ocean\ChannelAdModel;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Ocean\OceanAdModel;
use Illuminate\Support\Facades\DB;

class ChannelAdService extends BaseService
{
    /**
     * @param $data
     * @return bool
     * @throws CustomException
     * 批量更新
     */
    public function batchUpdate($data){
        $this->validRule($data, [
            'channel_id' => 'required|integer',
            'ad_ids' => 'required|array',
            'channel' => 'required',
            'platform' => 'required'
        ]);

        Functions::hasEnum(PlatformEnum::class, $data['platform']);

        DB::beginTransaction();

        try{
            foreach($data['ad_ids'] as $adId){
                $this->update([
                    'ad_id' => $adId,
                    'channel_id' => $data['channel_id'],
                    'platform' => $data['platform'],
                    'extends' => [
                        'channel' => $data['channel'],
                    ],
                ]);
            }
        }catch(CustomException $e){
            DB::rollBack();
            throw $e;
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }

        DB::commit();

        return true;
    }

    /**
     * @param $data
     * @return bool
     * 更新
     */
    public function update($data){
        $channelAdModel = new ChannelAdModel();
        $channelAd = $channelAdModel->where('ad_id', $data['ad_id'])
            ->where('platform', $data['platform'])
            ->first();

        $flag = $this->buildFlag($channelAd);
        if(empty($channelAd)){
            $channelAd = new ChannelAdModel();
        }

        $channelAd->ad_id = $data['ad_id'];
        $channelAd->channel_id = $data['channel_id'];
        $channelAd->platform = $data['platform'];
        $channelAd->extends = $data['extends'];
        $ret = $channelAd->save();

        if($ret && !empty($channelAd->id) && $flag != $this->buildFlag($channelAd)){
            $this->createChannelAdLog([
                'channel_ad_id' => $channelAd->id,
                'ad_id' => $data['ad_id'],
                'channel_id' => $data['channel_id'],
                'platform' => $data['platform'],
                'extends' => $data['extends'],
            ]);
        }

        return $ret;
    }

    /**
     * @param $channelAd
     * @return string
     * 构建标识
     */
    protected function buildFlag($channelAd){
        $adminId = !empty($channelAd->extends->channel->admin_id) ? $channelAd->extends->channel->admin_id : 0;
        if(empty($channelAd)){
            $flag = '';
        }else{
            $flag = implode("_", [
                $channelAd->ad_id,
                $channelAd->channel_id,
                $channelAd->platform,
                $adminId
            ]);
        }
        return $flag;
    }

    /**
     * @param $data
     * @return bool
     * 创建渠道-计划日志
     */
    protected function createChannelAdLog($data){
        $channelAdLogModel = new ChannelAdLogModel();
        $channelAdLogModel->channel_ad_id = $data['channel_ad_id'];
        $channelAdLogModel->ad_id = $data['ad_id'];
        $channelAdLogModel->channel_id = $data['channel_id'];
        $channelAdLogModel->platform = $data['platform'];
        $channelAdLogModel->extends = $data['extends'];
        return $channelAdLogModel->save();
    }

    /**
     * @param $param
     * @return array
     * @throws CustomException
     * 列表
     */
    public function select($param){
        $this->validRule($param, [
            'start_datetime' => 'required',
            'end_datetime' => 'required',
        ]);
        Functions::timeCheck($param['start_datetime']);
        Functions::timeCheck($param['end_datetime']);
        $channelAdModel = new ChannelAdModel();
        $channelAds = $channelAdModel->whereBetween('updated_at', [$param['start_datetime'], $param['end_datetime']])->get();

        $distinct = $data = [];
        foreach($channelAds as $channelAd){
            if(empty($distinct[$channelAd['channel_id']])){
                // 计划
                $oceanAd = OceanAdModel::find($channelAd['ad_id']);
                if(empty($oceanAd)){
                    continue;
                }

                // 账户
                $oceanAccount = (new OceanAccountModel())->where('account_id', $oceanAd['account_id'])->first();
                if(empty($oceanAccount)){
                    continue;
                }

                $data[] = [
                    'channel_id' => $channelAd['channel_id'],
                    'ad_id' => $channelAd['ad_id'],
                    'ad_name' => $oceanAd['name'],
                    'account_id' => $oceanAd['account_id'],
                    'account_name' => $oceanAccount['name'],
                    'admin_id' => $oceanAccount['admin_id'],
                ];
                $distinct[$channelAd['channel_id']] = 1;
            }
        }

        return $data;
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

        foreach($ads as $ad){
            if(!empty($ad->ocean_ad_extends)){
                $ad->convert_callback_strategy = ConvertCallbackStrategyModel::find($ad->ocean_ad_extends->convert_callback_strategy_id);
                $ad->convert_callback_strategy_group = ConvertCallbackStrategyGroupModel::find($ad->ocean_ad_extends->convert_callback_strategy_group_id);
            }else{
                $ad->convert_callback_strategy = null;
                $ad->convert_callback_strategy_group = null;
            }
        }

        return [
            'channel_id' => $data['channel_id'],
            'list' => $ads
        ];
    }

    /**
     * @param $param
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($param){
        $date = $param['date'];

        $startTime = date('Y-m-d H:i:s', strtotime('-2 hours', strtotime($date)));
        $endTime = "{$date} 23:59:59";

        $oceanAdModel = new OceanAdModel();
        $oceanAds = $oceanAdModel->whereBetween('ad_modify_time', [$startTime, $endTime])
            ->where('ad_create_time', '>', '2021-07-20 00:00:00')
            ->get();

        $keyword = 'sign='. Advs::getAdvClickSign(AdvAliasEnum::OCEAN);

        foreach($oceanAds as $oceanAd){
            $actionTrackUrls = $oceanAd->extends->action_track_url ?? [];
            $hasKeyword = false;
            foreach($actionTrackUrls as $actionTrackUrl){
                if(empty($actionTrackUrl)){
                    continue;
                }

                if(strpos($actionTrackUrl, $keyword) === false){
                    continue;
                }

Functions::isLocal() && $actionTrackUrl .= '&support_admin_id=1';

                $ret = parse_url($actionTrackUrl);
                parse_str($ret['query'], $param);

                // 助理管理员id
                $supportAdminId = $param['support_admin_id'] ?? 0;

                $unionApiService = new UnionApiService();

                if(!empty($param['android_channel_id'])){
                    $channel = $unionApiService->apiReadChannel(['id' => $param['android_channel_id']]);
                    $chanenlExtends = $channel['channel_extends'] ?? [];
                    $channel['admin_id'] = $chanenlExtends['admin_id'] ?? 0;
                    unset($channel['extends']);
                    unset($channel['channel_extends']);

                    $this->update([
                        'ad_id' => $oceanAd->id,
                        'channel_id' => $param['android_channel_id'],
                        'platform' => PlatformEnum::ANDROID,
                        'extends' => [
                            'action_track_url' => $actionTrackUrl,
                            'channel' => $channel,
                            'support_admin_id' => $supportAdminId,
                        ],
                    ]);
                }

                if(!empty($param['ios_channel_id'])){
                    $channel = $unionApiService->apiReadChannel(['id' => $param['ios_channel_id']]);
                    $chanenlExtends = $channel['channel_extends'] ?? [];
                    $channel['admin_id'] = $chanenlExtends['admin_id'] ?? 0;
                    unset($channel['extends']);
                    unset($channel['channel_extends']);

                    $this->update([
                        'ad_id' => $oceanAd->id,
                        'channel_id' => $param['ios_channel_id'],
                        'platform' => PlatformEnum::IOS,
                        'extends' => [
                            'action_track_url' => $actionTrackUrl,
                            'channel' => $channel,
                            'support_admin_id' => $supportAdminId,
                        ],
                    ]);
                }

                $hasKeyword = true;
            }

            // 不需通知状态
            $oceanAdStatus = [
                OceanAdStatusEnum::AD_STATUS_DELETE,
                OceanAdStatusEnum::AD_STATUS_DISABLE,
                OceanAdStatusEnum::AD_STATUS_CREATE,
            ];

            if(!in_array($oceanAd->status, $oceanAdStatus) && !$hasKeyword){
                $oceanAccountModel = new OceanAccountModel();
                $oceanAccount = $oceanAccountModel->where('account_id', $oceanAd->account_id)->first();

                if(!empty($oceanAccount->admin_id)){
                    $title = "计划监测链错误";
                    $c = [
                        "账户id: {$oceanAccount->account_id}",
                        "账户名称: {$oceanAccount->name}",
                        "计划id: {$oceanAd->id}",
                        "计划名称: {$oceanAd->name}",
                        "请在 联运系统 > 渠道 中复制正确监测链！！",
                    ];

                    $i = 1;
                    foreach($actionTrackUrls as $actionTrackUrl){
                        $c[] = "当前计划监测链{$i}:$actionTrackUrl}";
                        $i++;
                    }

                    $content = implode("<br>", $c);

                    $adminId = $oceanAccount->admin_id;

                    $noticeApiService = new NoticeApiService();
                    $noticeApiService->apiSendFeishuMessage($title, $content, $adminId, 1800);
                }
            }
        }

        return true;
    }
}
