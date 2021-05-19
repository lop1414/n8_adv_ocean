<?php

namespace App\Services\Task;

use App\Common\Enums\ExecStatusEnum;
use App\Enums\TaskTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Ocean\OceanAdConvertService;
use App\Services\Ocean\OceanAdService;
use App\Services\Ocean\OceanCampaignService;
use App\Services\Ocean\OceanCreativeService;
use App\Services\Ocean\OceanVideoService;

class TaskOceanSyncService extends TaskOceanService
{
    /**
     * @var
     * 同步类型
     */
    public $syncType;

    /**
     * TaskOceanSyncService constructor.
     * @param $syncType
     * @throws CustomException
     */
    public function __construct($syncType)
    {
        parent::__construct(TaskTypeEnum::OCEAN_SYNC);

        // 同步类型
        Functions::hasEnum(OceanSyncTypeEnum::class, $syncType);
        $this->syncType = $syncType;
    }

    /**
     * @param $taskId
     * @param $data
     * @return bool
     * @throws CustomException
     * 创建
     */
    public function createSub($taskId, $data){
        // 验证
        $this->validRule($data, [
            'app_id' => 'required',
            'account_id' => 'required',
        ]);

        // 校验
        Functions::hasEnum(OceanSyncTypeEnum::class, $this->syncType);

        $subModel = new $this->subModelClass();
        $subModel->task_id = $taskId;
        $subModel->app_id = $data['app_id'];
        $subModel->account_id = $data['account_id'];
        $subModel->sync_type = $this->syncType;
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

        $builder = $subModel->where('task_id', $taskId)
            ->where('sync_type', $this->syncType)
            ->where('exec_status', ExecStatusEnum::WAITING);

        if($this->syncType == OceanSyncTypeEnum::VIDEO){
            // 获取3分钟前创建的任务
            $time = time() - 3 * 60;
            $datetime = date('Y-m-d H:i:s', $time);
            $builder->where('created_at', '<', $datetime);
        }

        $subTasks = $builder->orderBy('id', 'asc')->get();

        return $subTasks;
    }

    /**
     * @param $subTask
     * @return bool|void
     * @throws CustomException
     * 执行单个子任务
     */
    public function runSub($subTask){
        if($this->syncType == OceanSyncTypeEnum::CAMPAIGN){
            $this->syncCampaign($subTask);
        }elseif($this->syncType == OceanSyncTypeEnum::VIDEO){
            $this->syncVideo($subTask);
        }elseif($this->syncType == OceanSyncTypeEnum::AD){
            $this->syncAd($subTask);
        }elseif($this->syncType == OceanSyncTypeEnum::CREATIVE){
            $this->syncCreative($subTask);
        }elseif($this->syncType == OceanSyncTypeEnum::AD_CONVERT){
            $this->syncAdConvert($subTask);
        }else{
            throw new CustomException([
                'code' => 'NOT_HANDLE_FOR_SYNC_TYPE',
                'message' => '该同步类型无对应处理',
            ]);
        }

        return true;
    }

    /**
     * @param $subTask
     * @return bool
     * @throws CustomException
     * 同步广告组
     */
    private function syncCampaign($subTask){
        $oceanCampaignService = new OceanCampaignService($subTask->app_id);
        $option = [
            'account_ids' => [$subTask->account_id],
        ];
        $oceanCampaignService->sync($option);
        return true;
    }

    /**
     * @param $subTask
     * @return bool
     * @throws CustomException
     * 同步广告计划
     */
    private function syncAd($subTask){
        $oceanAdService = new OceanAdService($subTask->app_id);
        $option = [
            'account_ids' => [$subTask->account_id],
        ];

        $oceanAdService->sync($option);
        return true;
    }

    /**
     * @param $subTask
     * @return bool
     * @throws CustomException
     * 同步广告创意
     */
    private function syncCreative($subTask){
        $oceanCreativeService = new OceanCreativeService($subTask->app_id);
        $option = [
            'account_ids' => [$subTask->account_id],
        ];

        $oceanCreativeService->sync($option);
        return true;
    }

    /**
     * @param $subTask
     * @return bool
     * @throws CustomException
     * 同步视频
     */
    private function syncVideo($subTask){
        $oceanVideoService = new OceanVideoService($subTask->app_id);

        $option = [
            'account_ids' => [$subTask->account_id],
        ];

        // 筛选视频id
        if(!empty($subTask->extends->video_id)){
            $option['ids'] = [$subTask->extends->video_id];
        }

        $oceanVideoService->sync($option);

        return true;
    }

    /**
     * @param $subTask
     * @return bool
     * @throws CustomException
     * 同步转化目标
     */
    private function syncAdConvert($subTask){
        $oceanAdConvertService = new OceanAdConvertService($subTask->app_id);

        $option = [
            'account_ids' => [$subTask->account_id],
        ];

        $oceanAdConvertService->sync($option);

        return true;
    }
}
