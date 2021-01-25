<?php

namespace App\Sdks\OceanEngine\Traits;

trait Video
{
    /**
     * @param $accountId
     * @param $signature
     * @param $file
     * @param string $filename
     * @return mixed
     * 上传
     */
    public function uploadVideo($accountId, $signature, $file, $filename = ''){
        $url = $this->getUrl('/2/file/video/ad/');

        $param = [
            'advertiser_id' => $accountId,
            'video_signature' => $signature,
            'video_file' => $file,
        ];

        !empty($filename) && $param['filename'] = $filename;

        return $this->fileRequest($url, $param, 'POST');
    }

    /**
     * @return array
     * 获取视频规则
     */
    public function getVideoRules(){
        $rules = [
            'CREATIVE_IMAGE_MODE_VIDEO' => [
                'ratio' => 1.78,
                'width' => [1280, 2560],
                'height' => [720, 1440],
                'max_size' => 1000 * 1024 * 1024, // 1000M
            ],
            'CREATIVE_IMAGE_MODE_VIDEO_VERTICAL' => [
                'ratio' => 0.56,
                'width' => [720, 1440],
                'height' => [1280, 2560],
                'max_size' => 100 * 1024 * 1024,
            ],
            'CREATIVE_IMAGE_MODE_UNION_SPLASH_VIDEO' => [
                'ratio' => 0.56,
                'width' => [720, 1440],
                'height' => [1280, 2560],
                'max_size' => 100 * 1024 * 1024,
                'duration' => [1, 6],
            ],
        ];
        return $rules;
    }

    /**
     * @param $width
     * @param $height
     * @param $size
     * @param $duration
     * @return bool
     * 验证视频
     */
    public function validVideo($width, $height, $size, $duration){
        // 宽高比
        $ratio = round($width / $height, 2);

        $valid = false;
        foreach($this->getVideoRules() as $rule){
            $ratioValid = $ratio == $rule['ratio'];
            $widthValid = $width >= $rule['width'][0] && $width <= $rule['width'][1];
            $heightValid = $height >= $rule['height'][0] && $height <= $rule['height'][1];
            $sizeValid = $size <= $rule['max_size'];

            // 时长
            $durationValid = true;
            if(isset($rule['duration'])){
                $durationValid = $duration >= $rule['duration'][0] && $duration <= $rule['duration'][1];
            }

            if($ratioValid && $widthValid && $heightValid && $sizeValid && $durationValid){
                $valid = true;
                break;
            }
        }

        return $valid;
    }

    /**
     * @param array $accountIds
     * @param $accessToken
     * @param array $filtering
     * @param int $page
     * @param int $pageSize
     * @return mixed
     * 并发获取视频列表
     */
    public function multiGetVideoList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10){
        $url = $this->getUrl('2/file/video/get/');

        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize);
    }
}
