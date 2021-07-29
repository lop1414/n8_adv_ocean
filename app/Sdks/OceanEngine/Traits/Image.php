<?php

namespace App\Sdks\OceanEngine\Traits;

trait Image
{
    /**
     * @param $accountId
     * @param $signature
     * @param $file
     * @param string $filename
     * @return mixed
     * 上传
     */
    public function uploadImage($accountId, $signature, $file, $filename = ''){
        $url = $this->getUrl('/2/file/image/ad/');

        $param = [
            'advertiser_id' => $accountId,
            'image_signature' => $signature,
            'image_file' => $file,
        ];

        !empty($filename) && $param['filename'] = $filename;

        return $this->fileRequest($url, $param, 'POST');
    }

    /**
     * @return array
     * 获取图片规则
     */
    public function getImageRules(){
        $rules = [
            'CREATIVE_IMAGE_MODE_SMALL' => [
                'ratio' => 1.52,
                'width' => [456, 1368],
                'height' => [300, 900],
                'max_size' => 2 * 1024 * 1024, // 2M
            ],
            'CREATIVE_IMAGE_MODE_LARGE' => [
                'ratio' => 1.78,
                'width' => [1280, 2560],
                'height' => [720, 1440],
                'max_size' => 2 * 1024 * 1024,
            ],
            'CREATIVE_IMAGE_MODE_GROUP' => [
                'ratio' => 1.52,
                'width' => [456, 1368],
                'height' => [300, 900],
                'max_size' => 2 * 1024 * 1024,
            ],
            'CREATIVE_IMAGE_MODE_LARGE_VERTICAL' => [
                'ratio' => 0.56,
                'width' => [720, 1440],
                'height' => [1280, 2560],
                'max_size' => 2 * 1024 * 1024,
            ],
            'TOUTIAO_SEARCH_AD_IMAGE' => [
                'ratio' => 0.5,
                'width' => [345, 690],
                'height' => [138, 276],
                'max_size' => 2 * 1024 * 1024,
            ],
            'SEARCH_AD_SMALL_IMAGE' => [
                'ratio' => 0.25,
                'width' => [108, 432],
                'height' => [72, 288],
                'max_size' => 2 * 1024 * 1024,
            ],
            'CREATIVE_IMAGE_MODE_UNION_SPLASH' => [
                'ratio' => 0.56,
                'width' => [1080, 2160],
                'height' => [1920, 3840],
                'max_size' => 2 * 1024 * 1024,
            ],
        ];

        return $rules;
    }

    /**
     * @param $width
     * @param $height
     * @param $size
     * @return bool
     * 校验图片
     */
    public function validImage($width, $height, $size){
        // 宽高比
        $ratio = round($width / $height, 2);

        $valid = false;
        foreach($this->getImageRules() as $rule){
            $ratioValid = $ratio == $rule['ratio'];
            $widthValid = $width >= $rule['width'][0] && $width <= $rule['width'][1];
            $heightValid = $height >= $rule['height'][0] && $height <= $rule['height'][1];
            $sizeValid = $size <= $rule['max_size'];
            if($ratioValid && $widthValid && $heightValid && $sizeValid){
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
     * @param array $param
     * @return mixed
     * 并发获取图片列表
     */
    public function multiGetImageList(array $accountIds, $accessToken, $filtering = [], $page = 1, $pageSize = 10, $param = []){
        $url = $this->getUrl('2/file/image/get/');

        return $this->multiGetPageList($url, $accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }
}
