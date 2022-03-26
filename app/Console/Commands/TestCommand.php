<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\PlatformEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Models\ConvertCallbackModel;
use App\Common\Services\SystemApi\UnionApiService;
use App\Models\Ocean\ChannelAdLogModel;
use App\Models\Ocean\OceanAccountModel;

class TestCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'test';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '测试';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }

    /**
     * @throws \App\Common\Tools\CustomException
     * 处理
     */
    public function handle(){
        $this->demo();
    }


    public function demo(){
        $list = (new OceanAccountModel())->get();
        foreach ($list as $item){
            if(!empty($item->extend->roi_callback_status)){
                $item->roi_callback_status = $item->extend->roi_callback_status;
            }else{
                $item->roi_callback_status = StatusEnum::DISABLE;
            }
            $item->extend = [];
            $item->save();
        }
    }




    /**
     * @throws \App\Common\Tools\CustomException
     * 刷新渠道-计划日志
     */
    public function reflashChannelAdLog(){
        $channelAdLogModel = new ChannelAdLogModel();
        $channelAdLogs = $channelAdLogModel->whereRaw("
            extends not LIKE '%cp_channel_id%'
        ")->get();

        $unionApiService = new UnionApiService();
        foreach($channelAdLogs as $channelAdLog){
            if(!empty($channelAdLog->extends->action_track_url)){
                $ret = parse_url($channelAdLog->extends->action_track_url);
                parse_str($ret['query'], $param);

                if($channelAdLog->platform == PlatformEnum::ANDROID && !empty($param['android_channel_id'])){
                    $channel = $unionApiService->apiReadChannel(['id' => $param['android_channel_id']]);
                    $chanenlExtends = $channel['channel_extends'] ?? [];
                    $channel['admin_id'] = $chanenlExtends['admin_id'] ?? 0;
                    unset($channel['extends']);
                    unset($channel['channel_extends']);

                    $channelAdLog->extends = [
                        'action_track_url' => $channelAdLog->extends->action_track_url,
                        'channel' => $channel,
                    ];

                    $channelAdLog->save();
                }elseif($channelAdLog->platform == PlatformEnum::IOS && !empty($param['ios_channel_id'])){
                    $channel = $unionApiService->apiReadChannel(['id' => $param['ios_channel_id']]);
                    $chanenlExtends = $channel['channel_extends'] ?? [];
                    $channel['admin_id'] = $chanenlExtends['admin_id'] ?? 0;
                    unset($channel['extends']);
                    unset($channel['channel_extends']);

                    $channelAdLog->extends = [
                        'action_track_url' => $channelAdLog->extends->action_track_url,
                        'channel' => $channel,
                    ];

                    $channelAdLog->save();
                }
            }
        }
    }

    /**
     * 刷新转化回传
     */
    public function reflashConvertCallback(){
        $convertCallbackModel = new ConvertCallbackModel();
        do{
            $convertCallbacks = $convertCallbackModel->where('updated_at', '<', '2021-07-22 11:00:00')->orderBy('id', 'asc')->take(5000)->get();
            var_dump($convertCallbacks->count());
            foreach($convertCallbacks as $convertCallback){
                if(empty($convertCallback->extends->convert)){
                    $extends = [
                        'convert' => $convertCallback->extends,
                    ];
                    $convertCallback->extends = $extends;
                    $convertCallback->save();
                    var_dump($convertCallback->id);
                }
            }
        }while($convertCallbacks->count() > 0);
    }
}
