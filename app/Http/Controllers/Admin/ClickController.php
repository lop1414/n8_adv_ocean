<?php

namespace App\Http\Controllers\Admin;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Helpers\Functions;
use App\Common\Models\ClickModel;
use App\Common\Tools\CustomException;
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
                // 7天内
                $datetime = date('Y-m-d H:i:s', strtotime("- 1 days"));
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
            'id' => 'required',
            'event_type' => 'required',
        ]);

        $id = $request->post('id');
        $eventType = $request->post('event_type');

        $click = $this->found($id);

        $advConvertCallbackService = new AdvConvertCallbackService();
        $eventTypeMap = $advConvertCallbackService->getEventTypeMap();
        $eventTypes = array_values($eventTypeMap);
        if(!in_array($eventType, $eventTypes)){
            throw new CustomException([
                'code' => 'UNKNOWN_EVENT_TYPE',
                'message' => '非合法回传类型',
            ]);
        }

        $ret = $advConvertCallbackService->runCallback($click, $eventType);

        return $this->ret($ret);
    }
}
