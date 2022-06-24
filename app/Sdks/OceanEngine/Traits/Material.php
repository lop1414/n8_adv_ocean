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

        $option = [
            'timeout' => 30,
        ];

        return $this->authRequest($url, $param, 'POST', [], $option);
    }

    /**
     * @param $accountId
     * @param $preAuditMaterials
     * @return mixed
     * 发送预审
     */
    public function sendPreAudit($accountId, $preAuditMaterials){
        $url = $this->getUrl('2/tools/pre_audit/send/');

        $param = [
            'advertiser_id' => $accountId * 1,
            'pre_audit_materials' => $preAuditMaterials,
        ];

        return $this->authRequest($url, $param, 'POST');
    }

    /**
     * @param $accountId
     * @param $filter
     * @return mixed
     * 获取预审
     */
    public function getPreAudit($accountId, $filter){
        $url = $this->getUrl('2/tools/pre_audit/get/');

        $param = [
            'advertiser_id' => $accountId * 1,
            'filter' => $filter,
        ];

        return $this->authRequest($url, $param, 'GET');
    }
}
