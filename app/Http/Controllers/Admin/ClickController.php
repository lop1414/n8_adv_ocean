<?php

namespace App\Http\Controllers\Admin;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Helpers\Functions;
use App\Common\Models\ClickModel;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanLandingTypeEnum;
use App\Models\Ocean\OceanAdModel;
use App\Services\AdvConvertCallbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $link = $request->post('link');
            $click = new ClickModel();
            $click->link = $link;
        }else{
            $this->validRule($request->post(), [
                'convert_id' => 'required',
            ]);
            $convertId = $request->post('convert_id');

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
}
