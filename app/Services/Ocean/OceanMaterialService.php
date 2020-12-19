<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountVideoModel;

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

        // 错误列表
        $failList = $ret['fail_list'] ?? [];
        foreach($failList as $v){
            $failReason = $v['fail_reason'] ?? '';
            if(!empty($v['video_id']) && $failReason == 'VIDEO_BINDING_EXISTED'){
                // 保存关联关系
                $oceanAccountVideoModel = new OceanAccountVideoModel();
                $oceanAccountVideo = $oceanAccountVideoModel->where('account_id', $v['target_advertiser_id'])
                    ->where('video_id', $v['video_id'])
                    ->first();

                if(empty($oceanAccountVideo)){
                    $oceanAccountVideo = new OceanAccountVideoModel();
                    $oceanAccountVideo->account_id = $v['target_advertiser_id'];
                    $oceanAccountVideo->video_id = $v['video_id'];
                    $oceanAccountVideo->save();
                }

            }elseif(!empty($v['image_id']) && $failReason == 'IMAGE_BINDING_EXISTED'){
                #图片
            }
        }

        return $ret;
    }
}
