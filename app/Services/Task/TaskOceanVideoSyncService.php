<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Common\Enums\TaskStatusEnum;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Models\TaskOceanVideoSyncModel;
use App\Services\Ocean\OceanVideoService;

class TaskOceanVideoSyncService extends BaseService
{
    /**
     * TaskOceanVideoSyncService constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $data
     * @return bool
     * 创建
     */
    public function create($data){
        $this->model = new TaskOceanVideoSyncModel();
        $this->model->task_id = $data['task_id'];
        $this->model->app_id = $data['app_id'];
        $this->model->account_id = $data['account_id'];
        $this->model->video_id = $data['video_id'];
        $this->model->exec_status = ExecStatusEnum::WAITING;
        $this->model->admin_id = $data['admin_id'] ?? 0;
        $this->model->extends = $data['extends'] ?? [];
        return $this->model->save();
    }

    /**
     * @param $taskId
     * @return mixed
     * 获取待执行子任务
     */
    public function getWaitingSubTasks($taskId){
        $taskOceanVideoUploadModel = new TaskOceanVideoSyncModel();

        // 获取3分钟前创建的任务
        $time = time() - 3 * 60;
        $datetime = date('Y-m-d H:i:s', $time);

        $subTasks = $taskOceanVideoUploadModel->where('task_id', $taskId)
            ->where('exec_status', ExecStatusEnum::WAITING)
            ->where('created_at', '<', $datetime)
            ->orderBy('id', 'asc')
            ->get();

        return $subTasks;
    }

    /**
     * @return bool
     * @throws CustomException
     * 执行
     */
    public function run(){
        $taskService = new TaskService();
        $tasks = $taskService->getWaitingTasks(TaskTypeEnum::OCEAN_VIDEO_SYNC);

        foreach($tasks as $task){
            try{
                // 执行子任务
                $ret = $this->runSubTask($task);

                if(!!$ret){
                    // 更改任务状态
                    $taskService->updateTaskStatus($task, TaskStatusEnum::SUCCESS);
                }
            }catch(CustomException $e){
                $taskStatus = TaskStatusEnum::FAIL;
                $errorInfo = $e->getErrorInfo(true);

                // 公共请求返回空, 任务状态修改为待执行
                if(
                    $errorInfo['code'] == 'PUBLIC_REQUEST_ERROR' &&
                    empty($errorInfo['data']['result'])
                ){
                    $taskStatus = TaskStatusEnum::WAITING;
                }

                // 更改任务状态
                $taskService->updateTaskStatus($task, $taskStatus);

                throw new CustomException($errorInfo);
            }catch(\Exception $e){
                // 更改任务状态
                $taskService->updateTaskStatus($task, TaskStatusEnum::FAIL);

                throw new \Exception($e->getMessage());
            }
        }

        return true;
    }

    /**
     * @param $task
     * @return bool
     * @throws CustomException
     * 执行子任务
     */
    private function runSubTask($task){
        // 获取子任务
        $subTasks = $this->getWaitingSubTasks($task->id);

        if($subTasks->isEmpty()){
            return false;
        }

        foreach($subTasks as $subTask){
            $oceanVideoService = new OceanVideoService($subTask->app_id);
            $option = [
                'account_ids' => $subTask->account_id,
            ];

            if(!empty($subTask->video_id)){
                $option['video_ids'] = $subTask->video_id;
            }

            $oceanVideoService->syncVideo($option);

            $subTask->exec_status = ExecStatusEnum::SUCCESS;
            $subTask->save();
        }

        return true;
    }
}
