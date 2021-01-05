<?php

namespace App\Services\Task;

use App\Common\Enums\TaskStatusEnum;
use App\Common\Enums\TaskTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\NoticeApiService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountVideoModel;
use App\Models\Task\TaskModel;
use Illuminate\Support\Facades\DB;

class TaskService extends BaseService
{
    /**
     * @var
     * 任务id
     */
    public $taskId;

    /**
     * @var
     * 任务类型
     */
    public $taskType;

    /**
     * TaskService constructor.
     * @param $taskType
     * @throws CustomException
     */
    public function __construct($taskType)
    {
        parent::__construct();

        // 任务类型
        Functions::hasEnum(TaskTypeEnum::class, $taskType);
        $this->taskType = $taskType;
    }

    /**
     * @param $data
     * @param $subs
     * @return bool
     * @throws CustomException
     * 创建
     */
    public function create($data, $subs){
        // 验证
        $this->validRule($data, [
            'name' => 'required',
        ]);

        // 任务类型
        Functions::hasEnum(TaskTypeEnum::class, $this->taskType);

        try{
            // 开启事务
            DB::beginTransaction();

            $taskModel = new TaskModel();
            $taskModel->name = $data['name'];
            $taskModel->task_type = $this->taskType;
            $taskModel->task_status = TaskStatusEnum::WAITING;
            $taskModel->admin_id = $data['admin_id'] ?? 0;

            $ret = $taskModel->save();
            if(!$ret){
                throw new CustomException([
                    'code' => 'CREATE_TASK_ERROR',
                    'message' => '任务创建失败',
                ]);
            }

            $this->taskId = $taskModel->id;

            foreach($subs as $sub){
                $this->createSub($taskModel->id, $sub);
            }

            DB::commit();

            return true;
        }catch(CustomException $e){
            DB::rollBack();
            throw new CustomException($e->getErrorInfo(true));
        }catch(\Exception $e){
            DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $taskId
     * @param $data
     * @throws CustomException
     * 创建子任务
     */
    public function createSub($taskId, $data){
        throw new CustomException([
            'code' => 'PLEASE_WRITE_CREATE_SUB_CODE',
            'message' => '请书写子任务创建代码',
        ]);
    }

    /**
     * @return mixed
     * @throws CustomException
     * 获取待执行任务
     */
    public function getWaitingTasks(){
        // 任务类型
        Functions::hasEnum(TaskTypeEnum::class, $this->taskType);

        // 待执行任务
        $taskModel = new TaskModel();
        $waitingTasks = $taskModel->where('task_type', $this->taskType)
            ->where('task_status', TaskStatusEnum::WAITING)
            ->orderBy('id', 'asc')
            ->take(30)
            ->get();

        return $waitingTasks;
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 执行
     */
    public function run($option = []){
        $tasks = $this->getWaitingTasks();

        foreach($tasks as $task){
            try{
                // 执行子任务
                $ret = $this->runSub($task, $option);

                if(!!$ret){
                    // 更改任务状态
                    $this->updateTaskStatus($task, TaskStatusEnum::SUCCESS);
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

                // 错误码处理
                if(isset($errorInfo['data']['result']['code'])){
                    if($errorInfo['data']['result']['code'] == 51007){
                        // 网络错误
                        $taskStatus = TaskStatusEnum::WAITING;
                    }elseif($errorInfo['data']['result']['code'] == 40501){
                        // 视频不存在
                        $param = json_decode($errorInfo['data']['param'], true);

                        // 删除视频记录
                        if(!empty($param['advertiser_id']) && !empty($param['video_ids'])){
                            $oceanAccountVideoModel = new OceanAccountVideoModel();
                            $oceanAccountVideoModel->where('account_id', $param['advertiser_id'])
                                ->whereIn('video_id', $param['video_ids'])
                                ->delete();
                        }

                        $taskStatus = TaskStatusEnum::WAITING;
                    }
                }

                // 更改任务状态
                $this->updateTaskStatus($task, $taskStatus);

                throw new CustomException($errorInfo);
            }catch(\Exception $e){
                // 更改任务状态
                $this->updateTaskStatus($task, TaskStatusEnum::FAIL);

                throw new \Exception($e->getMessage());
            }
        }

        return true;
    }

    /**
     * @param $task
     * @param $option
     * @throws CustomException
     * 执行子任务
     */
    public function runSub($task, $option){
        throw new CustomException([
            'code' => 'PLEASE_WRITE_RUN_SUB_CODE',
            'message' => '请书写执行子任务代码',
        ]);
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

    /**
     * @param $adminId
     * @return bool
     * @throws CustomException
     * 对应管理员是否有未执行任务
     */
    public function hasAdminUserWaitingTask($adminId){
        // 任务类型
        Functions::hasEnum(TaskTypeEnum::class, $this->taskType);

        // 待执行任务
        $taskModel = new TaskModel();
        $waitingTasks = $taskModel->where('task_type', $this->taskType)
            ->where('task_status', TaskStatusEnum::WAITING)
            ->where('admin_id', $adminId)
            ->orderBy('id', 'asc')
            ->first();

        return !empty($waitingTasks);
    }
}
