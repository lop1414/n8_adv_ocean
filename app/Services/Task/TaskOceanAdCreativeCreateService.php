<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Enums\TaskTypeEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Ocean\OceanAdCreativeCreateService;
use App\Services\Ocean\OceanToolService;

class TaskOceanAdCreativeCreateService extends TaskOceanService
{
    /**
     * TaskOceanAdCreativeCreateService constructor.
     * @throws CustomException
     */
    public function __construct()
    {
        parent::__construct(TaskTypeEnum::OCEAN_AD_CREATIVE_CREATE);
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
            'data' => 'required',
            'start_at' => 'required',
        ]);

        $subModel = new $this->subModelClass();
        $subModel->task_id = $taskId;
        $subModel->app_id = $data['app_id'];
        $subModel->account_id = $data['account_id'];
        $subModel->data = $data['data'];
        $subModel->start_at = $data['start_at'];
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
     * @throws CustomException
     * 执行子任务
     */
    public function runSubs($task, $option){
        // 获取子任务
        $subTasks = $this->getWaitingSubTasks($task->id);

        if($subTasks->isEmpty()){
            return false;
        }

        $syncs = [];
        foreach($subTasks as $subTask){
            try{
                $oceanAdCreativeCreateService = new OceanAdCreativeCreateService();
                $ret = $oceanAdCreativeCreateService->createAdCreative($subTask->toArray());

                if(!empty($ret['ad_id'])){
                    $syncs[] = [
                        'app_id' => $subTask->app_id,
                        'account_id' => $subTask->account_id,
                        'ad_id' => $ret['ad_id'],
                    ];
                }

                $subTask->exec_status = ExecStatusEnum::SUCCESS;
            }catch(CustomException $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                // 失败结果
                $errorInfo = $e->getErrorInfo(true);
                $subTask->fail_data = $errorInfo['data'];

                $subTask->exec_status = ExecStatusEnum::FAIL;
            }catch(\Exception $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);

                $subTask->exec_status = ExecStatusEnum::FAIL;
            }

            $subTask->save();
        }

        // 休眠防延迟
        $sleep = max(1, (60 - ($subTasks->count() * 1)));
        sleep($sleep);

        $oceanToolService = new OceanToolService();
        foreach($syncs as $sync){
            $oceanToolService->sync(OceanSyncTypeEnum::AD, $sync);
        }

        return true;
    }
}
