<?php

namespace App\Services;

use App\Common\Enums\ConvertCallbackStatusEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Enums\ExecStatusEnum;
use App\Common\Models\ConvertCallbackModel;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Models\RoiConvertCallbackModel;

class AdvRoiConvertCallbackService extends AdvConvertCallbackService
{

    /**
     * @return mixed
     * 获取可回传列表
     */
    private function getWaitingCallbacks(){
        // 24小时内
        $time = strtotime("-1 days", time());
        $datetime = date('Y-m-d H:i:s', $time);

        $convertCallbackModel = new ConvertCallbackModel();

        // 剔除
        $convertCallbackStatus = array_diff(array_column(ConvertCallbackStatusEnum::$list,'id'),[
            ConvertCallbackStatusEnum::WAITING_CALLBACK,
            ConvertCallbackStatusEnum::DOT_CAN_CALLBACK_BY_TRANSFER,
            ConvertCallbackStatusEnum::MACHINE_CALLBACK,
            ConvertCallbackStatusEnum::MANUAL_CALLBACK,
            ConvertCallbackStatusEnum::CALLBACK_FAIL,
        ]);

        $convertCallbacks = $convertCallbackModel
            ->where('created_at', '>', $datetime)
            ->where('exec_status', ExecStatusEnum::SUCCESS)
            ->whereIn('convert_callback_status', $convertCallbackStatus)
            ->whereIn('convert_type',['add_desktop','pay'])
            ->get();

        return $convertCallbacks;
    }



    /**
     * @return bool
     * 执行
     */
    public function run(){
        $items = $this->getWaitingCallbacks();

        $roiConvertCallbackModel = new RoiConvertCallbackModel();
        foreach($items as $item){
            try{
                $roiItem = $roiConvertCallbackModel;
                $roiItem->convert_callback_id = $item->id;


                $res = $this->callback($item);

                $item->extends = $res[''];
                $item->convert_callback_status = ConvertCallbackStatusEnum::MACHINE_CALLBACK;
                $item->save();

                $roiItem->callback_at = date('Y-m-d H:i:s');
                $roiItem->save();


            }catch(CustomException $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                // 失败结果
                $errorInfo = $e->getErrorInfo(true);
                $roiItem->fail_data = $errorInfo;

            }catch(\Exception $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);
            }
            $item->save();
        }

        return true;
    }





    /**
     * @return array
     * 获取转化跟踪回传映射
     */
    public function getEventTypeMap(){
        return [
            ConvertTypeEnum::ADD_DESKTOP => 0,
            ConvertTypeEnum::PAY => 392,
        ];
    }


    /**
     * @return string[]
     * 获取事件管理回传映射
     */
    public function getAssetEventType(){
        return  [
            ConvertTypeEnum::ADD_DESKTOP => 'active',
            ConvertTypeEnum::PAY => 'supply_active_pay',
        ];
    }



}
