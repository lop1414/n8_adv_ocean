<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Models\Ocean\OceanAccountVideoModel;
use App\Models\Ocean\OceanVideoModel;
use App\Services\Task\TaskOceanSyncService;

class OceanVideoService extends OceanService
{
    /**
     * OceanVideoService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $accountId
     * @param $signature
     * @param $file
     * @param string $filename
     * @return mixed
     * @throws CustomException
     * 上传
     */
    public function uploadVideo($accountId, $signature, $file, $filename = ''){
        $this->setAccessToken();

        $ret = $this->sdk->uploadVideo($accountId, $signature, $file, $filename);
Functions::consoleDump($ret);
        // 同步
        if(!empty($ret['video_id'])){
            $taskOceanSyncService = new TaskOceanSyncService(OceanSyncTypeEnum::VIDEO);
            $task = [
                'name' => '同步巨量视频',
                'admin_id' => 0,
            ];
            $subs = [];
            $subs[] = [
                'app_id' => $this->sdk->getAppId(),
                'account_id' => $accountId,
                'admin_id' => 0,
                'extends' => [
                    'video_id' => $ret['video_id']
                ],
            ];
            $taskOceanSyncService->create($task, $subs);
        }

        return $ret;
    }

    /**
     * @param $accounts
     * @param $filtering
     * @param $pageSize
     * @return array
     * @throws CustomException
     * 并发获取
     */
    public function multiGetVideoList($accounts, $filtering, $pageSize){
        return $this->multiGetPageList('video', $accounts, $filtering, $pageSize);
    }

    /**
     * @param array $option
     * @throws CustomException
     * 同步
     */
    public function syncVideo($option = []){
        ini_set('memory_limit', '2048M');

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
        }

        $filtering = [];
        if(!empty($option['date'])){
            $filtering['start_time'] = Functions::getDate($option['date']);
            $filtering['end_time'] = Functions::getDate($option['date']);
        }

        if(!empty($option['ids'])){
            $filtering['video_ids'] = $option['ids'];
        }

        $accountGroup = $this->getSubAccountGroup($accountIds);

        $t = microtime(1);

        $pageSize = 100;
        foreach($accountGroup as $pid => $g){
            $videos = $this->multiGetVideoList($g, $filtering, $pageSize);
            Functions::consoleDump('count:'. count($videos));

            // 保存
            foreach($videos as $video) {
                $this->saveVideo($video);
            }
        }

        $t = microtime(1) - $t;
        var_dump($t);
    }

    /**
     * @param $video
     * @return bool
     * 保存
     */
    public function saveVideo($video){
        $oceanVideoModel = new OceanVideoModel();
        $oceanVideo = $oceanVideoModel->where('id', $video['id'])->first();

        if(empty($oceanVideo)){
            $oceanVideo = new OceanVideoModel();
        }

        $oceanVideo->id = $video['id'];
        $oceanVideo->size = $video['size'];
        $oceanVideo->width = $video['width'];
        $oceanVideo->height = $video['height'];
        $oceanVideo->format = $video['format'];
        $oceanVideo->signature = $video['signature'];
        $oceanVideo->poster_url = $video['poster_url'];
        $oceanVideo->bit_rate = $video['bit_rate'];
        $oceanVideo->duration = $video['duration'];
        $oceanVideo->material_id = $video['material_id'];
        $oceanVideo->source = $video['source'];
        $oceanVideo->create_time = $video['create_time'];
        $oceanVideo->filename = $video['filename'];

        $ret = $oceanVideo->save();

        if($ret){
            // 添加关联关系
            $oceanAccountVideoModel = new OceanAccountVideoModel();
            $oceanAccountVideo = $oceanAccountVideoModel->where('account_id', $video['advertiser_id'])
                ->where('video_id', $video['id'])
                ->first();

            if(empty($oceanAccountVideo)){
                $oceanAccountVideo = new OceanAccountVideoModel();
                $oceanAccountVideo->account_id = $video['advertiser_id'];
                $oceanAccountVideo->video_id = $video['id'];
                $oceanAccountVideo->save();
            }
        }

        return $ret;
    }
}
