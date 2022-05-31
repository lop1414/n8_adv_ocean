<?php

namespace App\Services;

use App\Common\Enums\ConvertCallbackStatusEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Enums\ExecStatusEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Models\ConvertCallbackModel;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Models\RoiConvertCallbackModel;
use Illuminate\Support\Facades\DB;

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

        // 可回传类型
        $convertCallbackStatus =[
            ConvertCallbackStatusEnum::DOT_NEED_CALLBACK,
            ConvertCallbackStatusEnum::DOT_NEED_CALLBACK_BY_CALLBACK_TIME,
            ConvertCallbackStatusEnum::DOT_NEED_CALLBACK_BY_CONVERT_TIMES,
        ];

        $status = StatusEnum::ENABLE;
        $convertCallbacks = $convertCallbackModel
            ->select(DB::raw('convert_callbacks.*'))
            ->leftJoin('clicks','convert_callbacks.click_id','=','clicks.id')
            ->leftJoin('ocean_ads AS ad','clicks.ad_id','=','ad.id')
            ->leftJoin('ocean_accounts','ad.account_id','=','ocean_accounts.account_id')
            ->leftJoin('roi_convert_callbacks AS  roi','convert_callbacks.id','=','roi.convert_callback_id')
            ->whereNull('roi.convert_callback_id')
            ->where('convert_callbacks.convert_at', '>', $datetime)
            ->where('convert_callbacks.exec_status', ExecStatusEnum::SUCCESS)
            ->whereIn('convert_callbacks.convert_callback_status', $convertCallbackStatus)
            ->whereIn('convert_callbacks.convert_type',['register','pay'])
            ->where('ocean_accounts.roi_callback_status',$status)
            ->get();

        return $convertCallbacks;
    }



    /**
     * @return bool
     * 执行
     */
    public function run(): bool
    {
        $items = $this->getWaitingCallbacks();

        $callbackRatio = env('ROI_CALLBACK_RATIO');
        $roiCallbackDotNeedByDay = env('ROI_CALLBACK_DOT_NEED_BY_DAY');

        foreach($items as $item){


            try{
                if($item->convert_type == ConvertTypeEnum::PAY){
                    //超时
                    $diff = time() - strtotime($item->extends->convert->n8_union_user->created_at);
                    if($diff > $roiCallbackDotNeedByDay*24*24*60){
                        $item->convert_callback_status = ConvertCallbackStatusEnum::DOT_NEED_ROI_CALLBACK_BY_TIME;
                        $item->save();
                        continue;
                    }

                    //比例扣除
                    $rand = mt_rand(0,100);
                    if($rand > $callbackRatio){
                        $item->convert_callback_status = ConvertCallbackStatusEnum::DOT_NEED_CALLBACK_BY_ROI;
                        $item->save();
                        continue;
                    }
                }

                $res = $this->callback($item);
                $callback_at = date('Y-m-d H:i:s');

                $item->convert_callback_status = ConvertCallbackStatusEnum::ROI_MACHINE_CALLBACK;
                $item->callback_at = $callback_at;
                $item->save();

                //日志
                $roiItem = (new RoiConvertCallbackModel())->where('convert_callback_id',$item->id)->first();
                if(empty($roiItem)){
                    $roiItem = new RoiConvertCallbackModel();
                    $roiItem->convert_callback_id = $item->id;
                }
                $roiItem->convert_callback_id = $item->id;
                $roiItem->extends = $res;
                $roiItem->callback_at = $callback_at;
                $roiItem->save();
            }catch(CustomException $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                if(!empty($roiItem)){
                    // 失败结果
                    $roiItem->fail_data = $e->getErrorInfo(true);
                    $roiItem->save();
                }

            }catch(\Exception $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);
                if(!empty($roiItem)){
                    // 失败结果
                    $roiItem->fail_data = [
                        'msg'   => $e->getMessage(),
                        'code'  => $e->getCode()
                    ];
                    $roiItem->save();
                }
            }


        }

        return true;
    }





    /**
     * @return array
     * 获取转化跟踪回传映射
     */
    public function getEventTypeMap(): array
    {
        return [
            ConvertTypeEnum::REGISTER => 0,
            ConvertTypeEnum::PAY => 392,
        ];
    }


    /**
     * @return string[]
     * 获取事件管理回传映射
     */
    public function getAssetEventType(): array
    {
        return  [
            ConvertTypeEnum::REGISTER => 'active',
            ConvertTypeEnum::PAY => 'supply_active_pay',
        ];
    }



}
