<?php

namespace App\Services\Task;

use App\Common\Enums\TaskStatusEnum;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\NoticeApiService;
use App\Models\Task\TaskModel;

class TaskService extends BaseService
{
    /**
     * TaskService constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $data
     * @return bool
     * @throws \App\Common\Tools\CustomException
     * 创建
     */
    public function create($data){
        // 任务类型
        Functions::hasEnum(TaskTypeEnum::class, $data['task_type']);

        $this->model = new TaskModel();
        $this->model->name = $data['name'];
        $this->model->task_type = $data['task_type'];
        $this->model->task_status = TaskStatusEnum::WAITING;
        $this->model->admin_id = $data['admin_id'];
        $ret = $this->model->save();

        return $ret;
    }

    /**
     * @param $taskType
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 获取待执行任务
     */
    public function getWaitingTasks($taskType){
        // 任务类型
        Functions::hasEnum(TaskTypeEnum::class, $taskType);

        // 待执行任务
        $taskModel = new TaskModel();
        $waitingTasks = $taskModel->where('task_type', $taskType)
            ->where('task_status', TaskStatusEnum::WAITING)
            ->orderBy('id', 'asc')
            ->get();

        return $waitingTasks;
    }

    /**
     * @param $task
     * @param $taskStatus
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 更新任务状态
     */
    public function updateTaskStatus($task, $taskStatus){
        Functions::hasEnum(TaskStatusEnum::class, $taskStatus);
        $task->task_status = $taskStatus;
        $ret = $task->save();

        if($task->admin_id > 0){
            $taskStatusName = Functions::getEnumMapName(TaskStatusEnum::class, $taskStatus);
            $taskTypeName = Functions::getEnumMapName(TaskTypeEnum::class, $task->task_type);

            // 发送通知
            $title = "你有一个任务{$taskStatusName}";
            $content = implode("<br>", [
                "任务id: {$task->id}",
                "任务名称: {$task->name}",
                "任务类型: {$taskTypeName}",
            ]);
            $noticeApiService = new NoticeApiService();
            $noticeApiService->apiSendFeishuMessage($title, $content, $task->admin_id);
        }

        return $ret;
    }
}
