<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Services\Task\TaskOceanSyncService;

class OceanMaterialService extends OceanService
{
    /**
     * OceanMaterialService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $accountId
     * @param array $targetAccountIds
     * @param array $videoIds
     * @param array $imageIds
     * @return mixed
     * @throws CustomException
     * 推送素材
     */
    public function pushMaterial($accountId, array $targetAccountIds, $videoIds = [], $imageIds = []){
        $this->setAccessToken();

        $ret = $this->sdk->pushMaterial($accountId, $targetAccountIds, $videoIds, $imageIds);

        // 同步
        if(!empty($ret)){
//            $taskOceanSyncService = new TaskOceanSyncService(OceanSyncTypeEnum::VIDEO);
//            $task = [
//                'name' => '同步巨量视频',
//                'admin_id' => 0,
//            ];
//            $subs = [];
//            $subs[] = [
//                'app_id' => $this->sdk->getAppId(),
//                'account_id' => $accountId,
//                'admin_id' => 0,
//                'extends' => [],
//            ];
//            $taskOceanSyncService->create($task, $subs);
        }

        return $ret;
    }
}
