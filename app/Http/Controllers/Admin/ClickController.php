<?php

namespace App\Http\Controllers\Admin;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Models\ClickModel;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanLandingTypeEnum;
use App\Services\AdvConvertCallbackService;
use Illuminate\Http\Request;

class ClickController extends AdminController
{
    /**
     * @var string
     * 默认排序字段
     */
    protected $defaultOrderBy = 'click_at';

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->model = new ClickModel();

        parent::__construct();
    }

    /**
     * 列表预处理
     */
    public function selectPrepare(){
        $this->curdService->selectQueryBefore(function(){
            $this->curdService->customBuilder(function($builder){
                // 24小时内
                $datetime = date('Y-m-d H:i:s', strtotime("-24 hours"));
                $builder->where('click_at', '>', $datetime);
            });
        });
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws CustomException
     * 回传
     */
    public function callback(Request $request){
        $this->validRule($request->post(), [
            'event_type' => 'required',
            'landing_type' => 'required',
        ]);

        $eventType = $request->post('event_type');
        $landingType = $request->post('landing_type');

        $advConvertCallbackService = new AdvConvertCallbackService();
        $eventTypeMap = $advConvertCallbackService->getEventTypeMap();
        $eventTypes = array_values($eventTypeMap);
        if(!in_array($eventType, $eventTypes)){
            throw new CustomException([
                'code' => 'UNKNOWN_EVENT_TYPE',
                'message' => '非合法回传类型',
            ]);
        }

        if($landingType == OceanLandingTypeEnum::LINK){
            $this->validRule($request->post(), [
                'link' => 'required',
            ]);
            $link = trim($request->post('link'));
            $click = new ClickModel();
            $click->link = $link;
        }else{
            $this->validRule($request->post(), [
                'convert_id' => 'required',
            ]);
            $convertId = trim($request->post('convert_id'));

            $datetime = date('Y-m-d H:i:s', strtotime("-24 hours"));

            $clickModel = new ClickModel();
            $click = $clickModel->where('click_at', '>', $datetime)->where('convert_id', $convertId)->first();
            if(empty($click)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_CLICK',
                    'message' => '找不到对应点击',
                ]);
            }
        }

        $ret = $advConvertCallbackService->runCallback($click, $eventType);

        return $this->ret($ret);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws CustomException
     * 事件管理回传
     */
    public function assetEventCallback(Request $request){
        $this->validRule($request->post(), [
            'event_type' => 'required',
            'landing_type' => 'required'
        ]);

        $eventType = $request->post('event_type');
        $landingType = $request->post('landing_type');

        $advConvertCallbackService = new AdvConvertCallbackService();
        $eventTypeMap = $advConvertCallbackService->getAssetEventType();
        $eventTypes = array_values($eventTypeMap);
        if(!in_array($eventType, $eventTypes)){
            throw new CustomException([
                'code' => 'UNKNOWN_EVENT_TYPE',
                'message' => '非合法回传类型',
            ]);
        }

        $datetime = date('Y-m-d H:i:s', strtotime("-24 hours"));
        if($landingType == OceanLandingTypeEnum::LINK){
            $this->validRule($request->post(), [
                'link' => 'required',
            ]);
            $link = trim($request->post('link'));

            $click = (new ClickModel())
                ->where('click_at', '>', $datetime)
                ->where('link', 'like',"{$link}%")
                ->orderBy('click_at','desc')
                ->first();;
        }else{
            $this->validRule($request->post(), [
                'channel_id' => 'required',
            ]);
            $channelId = trim($request->post('channel_id'));

            $clickModel = new ClickModel();
            $click = $clickModel
                ->where('click_at', '>', $datetime)
                ->where('channel_id', $channelId)
                ->orderBy('click_at','desc')
                ->first();

            if(empty($click)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_CLICK',
                    'message' => '找不到对应点击',
                ]);
            }
        }

        $props = [];
        if($eventType == $eventTypeMap[ConvertTypeEnum::PAY]){
            $props = ['pay_amount' => 1.00];
        }

        $ret = $advConvertCallbackService->runAssetEventCallback($click, $eventType,$props);

        return $this->ret($ret);
    }
}
