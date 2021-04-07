<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Common\Enums\TaskStatusEnum;
use App\Common\Models\TaskModel;
use App\Common\Services\TaskService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Ocean\OceanAccountVideoModel;
use App\Services\Ocean\OceanToolService;

class TaskOceanService extends TaskService
{
    public $oceanToolService;

    /**
     * TaskOceanService constructor.
     * @param $taskType
     * @throws CustomException
     */
    public function __construct($taskType)
    {
        parent::__construct($taskType);

        $this->oceanToolService = new OceanToolService();
    }

    /**
     * @throws CustomException
     * 重执行
     */
    public function reWaiting(){
        $createdAt = date('Y-m-d H:i:s', (time() - 86400));
        $taskStatus = TaskStatusEnum::DONE;
        $execStatus = ExecStatusEnum::FAIL;
        $taskType = $this->taskType;

        if(empty($this->subModelClass)){
            throw new CustomException([
                'code' => 'PLEASE_SET_CONSTRUCT_SUB_MODEL_CLASS',
                'message' => '请设置构造子模型类',
            ]);
        }

        $subModel = new $this->subModelClass;
        $failSubTasks = $subModel->whereRaw("
                task_id IN (
                    SELECT id FROM tasks 
                        WHERE created_at >= '$createdAt'
                        AND task_status = '{$taskStatus}'
                        AND task_type = '{$taskType}'
                ) AND exec_status = '{$execStatus}'
            ")->get();

        foreach($failSubTasks as $failSubTask){
            if(empty($failSubTask->fail_data)){
                continue;
            }

            if($this->oceanToolService->sdk->isNetworkError($failSubTask->fail_data['result'])){
                // 网络错误
                $this->updateReWaitingStatus($failSubTask);
            }elseif($this->oceanToolService->sdk->isVideoNotExist($failSubTask->fail_data['result'])){
                $param = json_decode($failSubTask->fail_data['param'], true);

                // 删除视频记录
                if(!empty($param['advertiser_id']) && !empty($param['video_ids'])){
                    $oceanAccountVideoModel = new OceanAccountVideoModel();
                    $oceanAccountVideoModel->where('account_id', $param['advertiser_id'])
                        ->whereIn('video_id', $param['video_ids'])
                        ->delete();
                }
                $this->updateReWaitingStatus($failSubTask);
            }elseif($this->oceanToolService->sdk->isNotPermission($failSubTask->fail_data['result'])){
                #账户无权限
            }
        }
    }

    /**
     * @param $failSubTask
     * @return bool
     * @throws CustomException
     * 更新重执行状态
     */
    private function updateReWaitingStatus($failSubTask){
        $failSubTask->exec_status = ExecStatusEnum::WAITING;
        $failSubTask->save();

        $task = TaskModel::find($failSubTask->task_id);
        $this->updateTaskStatus($task, TaskStatusEnum::WAITING);

        return true;
    }
}
