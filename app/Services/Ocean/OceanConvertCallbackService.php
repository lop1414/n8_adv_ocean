<?php

namespace App\Services\Ocean;

use App\Common\Enums\ConvertTypeEnum;
use App\Common\Enums\ExecStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\AdvOceanApiService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanConvertCallbackStatusEnum;
use App\Models\Ocean\OceanConvertCallbackModel;

class OceanConvertCallbackService extends OceanService
{
    /**
     * OceanConvertCallbackService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $data
     * @return bool
     * @throws CustomException
     * 创建
     */
    public function create($data){
        $clickId = !empty($data['click_id']) ? intval($data['click_id']) : 0;

        Functions::hasEnum(ConvertTypeEnum::class, $data['convert_type']);

        $convertId = !empty($data['convert_id']) ? intval($data['convert_id']) : 0;
        if(empty($convertId)){
            throw new CustomException([
                'code' => 'CONVERT_ID_IS_EMPTY',
                'message' => '转化id不能为空',
            ]);
        }

        Functions::hasEnum(OceanConvertCallbackStatusEnum::class, $data['convert_callback_status']);

        // 已创建过的转化
        $oceanConvertCallback = (new OceanConvertCallbackModel())->where('convert_type', $data['convert_type'])
            ->where('convert_id', $convertId)
            ->first();
        if(!empty($oceanConvertCallback)){
            return false;
        }

        $oceanConvertCallbackModel = new OceanConvertCallbackModel();
        $oceanConvertCallbackModel->click_id = $clickId;
        $oceanConvertCallbackModel->convert_type = $data['convert_type'];
        $oceanConvertCallbackModel->convert_id = $convertId;
        $oceanConvertCallbackModel->n8_union_guid = $data['n8_union_guid'];
        $oceanConvertCallbackModel->n8_union_channel_id = $data['n8_union_channel_id'];
        $oceanConvertCallbackModel->convert_at = $data['convert_at'];
        $oceanConvertCallbackModel->exec_status = ExecStatusEnum::WAITING;
        $oceanConvertCallbackModel->convert_callback_status = $data['convert_callback_status'];
        $oceanConvertCallbackModel->extends = $data['extends'];
        $ret = $oceanConvertCallbackModel->save();

        return $ret;
    }

    /**
     * @return bool
     * 执行
     */
    public function run(){
        // 获取待处理回传
        $items = $this->getWaitingCallbacks();

        foreach($items as $item){
            try{
                $this->callback($item);

                $item->exec_status = ExecStatusEnum::SUCCESS;
            }catch(CustomException $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                // 失败结果
                $errorInfo = $e->getErrorInfo(true);
                $item->fail_data = $errorInfo['data'];

                $item->exec_status = ExecStatusEnum::FAIL;
            }catch(\Exception $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                $item->exec_status = ExecStatusEnum::FAIL;
            }
            $item->callback_at = date('Y-m-d H:i:s', time());
            $item->save();
        }

        return true;
    }

    /**
     * @return mixed
     * 获取待处理回传
     */
    private function getWaitingCallbacks(){
        // 24小时内
        $time = strtotime("-1 days", time());
        $datetime = date('Y-m-d H:i:s', $time);

        $oceanConvertCallbackModel = new OceanConvertCallbackModel();
        $oceanConvertCallbacks = $oceanConvertCallbackModel->where('created_at', '>', $datetime)
            ->where('exec_status', ExecStatusEnum::WAITING)
            ->get();

        return $oceanConvertCallbacks;
    }

    /**
     * @param $item
     * @return bool
     * @throws CustomException
     * 回传
     */
    private function callback($item){
        // 无需回传
        if($item->convert_callback_status != OceanConvertCallbackStatusEnum::WAITING_CALLBACK){
            return false;
        }

        $eventTypeMap = $this->getEventTypeMap();

        if(!isset($eventTypeMap[$item->convert_type])){
            // 无映射
            throw new CustomException([
                'code' => 'UNDEFINED_EVENT_TYPE_MAP',
                'message' => '未定义的事件类型映射',
                'log' => true,
                'data' => [
                    'item' => $item,
                ],
            ]);
        }
        $eventType = $eventTypeMap[$item->convert_type];

        // 关联点击
        if(empty($item->click)){
            throw new CustomException([
                'code' => 'NOT_FOUND_CONVERT_CLICK',
                'message' => '找不到该转化对应点击',
                'log' => true,
                'data' => [
                    'item' => $item,
                ],
            ]);
        }

        $url = 'https://ad.oceanengine.com/track/activate/';
        $param = [
            'callback' => $item->click->callback_param,
            'event_type' => $eventType,
        ];

        #TODO:添加props参数
//        if(!empty($props)){
//            $param['props'] = json_encode($props);
//        }

        $ret = file_get_contents($url .'?'. http_build_query($param));
        $result = json_decode($ret, true);

        if(!isset($result['code']) || $result['code'] != 0){
            throw new CustomException([
                'code' => 'OCEAN_CONVERT_CALLBACK_ERROR',
                'message' => '巨量转化回传失败',
                'log' => true,
                'data' => [
                    'item' => $item,
                    'url' => $url,
                    'param' => $param,
                    'result' => $result,
                ],
            ]);
        }

        $item->convert_callback_status = OceanConvertCallbackStatusEnum::MACHINE_CALLBACK;

        return true;
    }

    /**
     * @return array
     * 获取事件映射
     */
    private function getEventTypeMap(){
        return [
            ConvertTypeEnum::ADD_DESKTOP => 1,
            ConvertTypeEnum::PAY => 2,
        ];
    }

    /**
     * @param $converts
     * @return mixed
     * @throws CustomException
     * 按转化列表获取转化回传列表
     */
    public function getItemsByConverts($converts){
        foreach($converts as $k => $v){
            $item = $this->getItemByConvert($v);
            if(!empty($item)){
                $tmp = [
                    'id' => $item['id'],
                    'click_id' => $item['click_id'],
                    'convert_type' => $item['convert_type'],
                    'convert_id' => $item['convert_id'],
                    'n8_union_guid' => $item['n8_union_guid'],
                    'n8_union_channel_id' => $item['n8_union_channel_id'],
                    'convert_callback_status' => $item['convert_callback_status'],
                    'callback_at' => $item['callback_at'],
                ];

                if(!empty($item->click)){
                    $tmp['click'] = [
                        'id' => $item->click['id'],
                        'campaign_id' => $item->click['campaign_id'],
                        'ad_id' => $item->click['ad_id'],
                        'creative_id' => $item->click['creative_id'],
                        'click_at' => $item->click['click_at'],
                    ];
                }else{
                    $tmp['click'] = null;
                }
            }else{
                $tmp = null;
            }

            $converts[$k]['convert_callback'] = $tmp;
        }
        return $converts;
    }

    /**
     * @param $convert
     * @return mixed
     * @throws CustomException
     * 按转化获取转化回传
     */
    public function getItemByConvert($convert){
        $this->validRule($convert, [
            'convert_type' => 'required',
            'convert_id' => 'required',
        ]);

        Functions::hasEnum(ConvertTypeEnum::class, $convert['convert_type']);

        $oceanConvertCallbackModel = new OceanConvertCallbackModel();
        $item = $oceanConvertCallbackModel->where('convert_type', $convert['convert_type'])
            ->where('convert_id', $convert['convert_id'])
            ->first();

        if(!empty($item)){
            // 关联点击
            $item->click;
        }

        return $item;
    }
}
