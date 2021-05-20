<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Common\Helpers\Functions;
use App\Enums\Ocean\OceanAdUpdateTypeEnum;
use App\Enums\TaskTypeEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Ocean\OceanAdCreativeCreateService;
use App\Services\Ocean\OceanAdUpdateService;
use App\Services\Ocean\OceanToolService;

class TaskOceanAdUpdateService extends TaskOceanService
{
    /**
     * TaskOceanAdCreativeCreateService constructor.
     * @throws CustomException
     */
    public function __construct()
    {
        parent::__construct(TaskTypeEnum::OCEAN_AD_UPDATE);
    }

    /**
     * @param $taskId
     * @param $data
     * @return bool|void
     * @throws CustomException
     * 创建
     */
    public function createSub($taskId, $data){
        // 验证
        $this->validRule($data, [
            'app_id' => 'required',
            'account_id' => 'required',
            'ad_id' => 'required',
            'ad_update_type' => 'required',
            'data' => 'required',
        ]);
        Functions::hasEnum(OceanAdUpdateTypeEnum::class, $data['ad_update_type']);

        // data 验证
        if($data['ad_update_type'] == OceanAdUpdateTypeEnum::STATUS){
            $this->validRule($data, ['data.opt_status' => 'required']);
            if(!in_array($data['data']['opt_status'], ['enable', 'disable', 'delete'])){
                throw new CustomException([
                    'code' => 'OPT_STATUS_ERROR',
                    'message' => '操作状态错误',
                ]);
            }
        }elseif($data['ad_update_type'] == OceanAdUpdateTypeEnum::BUDGET){
            $this->validRule($data, ['data.budget' => 'required']);
        }elseif($data['ad_update_type'] == OceanAdUpdateTypeEnum::BID){
            $this->validRule($data, ['data.bid' => 'required']);
        }else{
            throw new CustomException([
                'code' => 'PLEASE_WRITE_CREATE_SUB_TASK_BY_OCEAN_AD_UPDATE_TYPE_CODE',
                'message' => '请书写按类型创建巨量计划更新任务代码',
            ]);
        }

        $subModel = new $this->subModelClass();
        $subModel->task_id = $taskId;
        $subModel->app_id = $data['app_id'];
        $subModel->account_id = $data['account_id'];
        $subModel->ad_id = $data['ad_id'];
        $subModel->ad_update_type = $data['ad_update_type'];
        $subModel->data = $data['data'];
        $subModel->start_at = $data['start_at'] ?? date('Y-m-d H:i:s', time());
        $subModel->exec_status = ExecStatusEnum::WAITING;
        $subModel->admin_id = $data['admin_id'] ?? 0;
        $subModel->extends = $data['extends'] ?? [];

        return $subModel->save();
    }

    /**
     * @param $taskId
     * @return mixed
     * 获取待执行子任务
     */
    public function getWaitingSubTasks($taskId){
        $subModel = new $this->subModelClass();

        $datetime = date('Y-m-d H:i:s');

        $subTasks = $subModel->where('task_id', $taskId)
            ->where('exec_status', ExecStatusEnum::WAITING)
            ->where('start_at', '<', $datetime)
            ->orderBy('id', 'asc')
            ->get();

        return $subTasks;
    }

    /**
     * @param $task
     * @param $option
     * @return bool
     * 执行子任务
     */
    public function runSubs($task, $option){
        // 获取子任务
        $subTasks = $this->getWaitingSubTasks($task->id);

        if($subTasks->isEmpty()){
            return false;
        }

        foreach($subTasks as $subTask){
            try{
                $oceanAdUpdateService = new OceanAdUpdateService();
                $oceanAdUpdateService->update($subTask);

                $subTask->exec_status = ExecStatusEnum::SUCCESS;
            }catch(CustomException $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                // 失败结果
                $errorInfo = $e->getErrorInfo(true);
                $subTask->fail_data = $errorInfo;

                $subTask->exec_status = ExecStatusEnum::FAIL;
            }catch(\Exception $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                $subTask->exec_status = ExecStatusEnum::FAIL;
            }

            $subTask->save();
        }

        return true;
    }
}
