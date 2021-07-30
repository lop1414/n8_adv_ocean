<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Common\Enums\MaterialTypeEnums;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Models\Ocean\OceanAccountVideoModel;
use App\Models\Ocean\OceanMaterialModel;
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
     * @param $accountIds
     * @param $accessToken
     * @param $filtering
     * @param $page
     * @param $pageSize
     * @param array $param
     * @return mixed|void
     * sdk并发获取列表
     */
    public function sdkMultiGetList($accountIds, $accessToken, $filtering, $page, $pageSize, $param = []){
        return $this->sdk->multiGetVideoList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option = []){
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
        foreach($accountGroup as $g){
            $videos = $this->multiGetPageList($g, $filtering, $pageSize);
            Functions::consoleDump('count:'. count($videos));

            // 保存
            foreach($videos as $video) {
                $this->save($video);
            }
        }

        $t = microtime(1) - $t;
        var_dump($t);

        return true;
    }

    /**
     * @param $video
     * @return bool
     * @throws CustomException
     * 保存
     */
    public function save($video){
        $oceanVideoModel = new OceanVideoModel();
        $oceanVideo = $oceanVideoModel->where('id', $video['id'])->first();

        if(empty($oceanVideo)){
            $oceanVideo = new OceanVideoModel();
        }

        $oceanVideo->id = $video['id'];
        $oceanVideo->size = $video['size'] ?? 0;
        $oceanVideo->width = $video['width'];
        $oceanVideo->height = $video['height'];
        $oceanVideo->format = $video['format'] ?? '';
        $oceanVideo->signature = $video['signature'];
        $oceanVideo->poster_url = $video['poster_url'];
        $oceanVideo->bit_rate = $video['bit_rate'];
        $oceanVideo->duration = $video['duration'];
        $oceanVideo->material_id = $video['material_id'];
        $oceanVideo->source = $video['source'];
        $oceanVideo->create_time = $video['create_time'];
        $oceanVideo->filename = $video['filename'] ?? '';

        $ret = $oceanVideo->save();

        if($ret){
            // 视频-账户关联
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

            // 视频-素材关联
            $oceanMaterial = OceanMaterialModel::find($video['material_id']);
            if(empty($oceanMaterial)){
                $oceanMaterialService = new OceanMaterialService();
                $oceanMaterialService->create([
                    'id' => $video['material_id'],
                    'material_type' => MaterialTypeEnums::VIDEO,
                    'file_id' => $video['id']
                ]);
            }
        }

        return $ret;
    }
}
