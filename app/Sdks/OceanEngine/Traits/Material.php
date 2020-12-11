<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait Material
{
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
        $url = $this->getUrl('2/file/material/bind/');

        $param = [
            'advertiser_id' => $accountId,
            'target_advertiser_ids' => $targetAccountIds,
        ];

        if(empty($videoIds) && empty($imageIds)){
            throw new CustomException([
                'code' => 'VIDEO_AND_IMAGE_IS_EMPTY',
                'message' => '视频和图片不能都为空',
            ]);
        }elseif(!empty($videoIds) && !empty($imageIds)){
            throw new CustomException([
                'code' => 'VIDEO_AND_IMAGE_JUST_CHOOSE_ONE',
                'message' => '视频和图片只能二选一',
            ]);
        }elseif(!empty($videoIds)){
            $param['video_ids'] = $videoIds;
        }elseif(!empty($imageIds)){
            $param['image_ids'] = $imageIds;
        }

        return $this->authRequest($url, $param, 'POST');
    }
}
