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
        $url = self::BASE_URL .'/2/file/video/ad/';

        $param = [
            'advertiser_id' => $accountId,
            'video_signature' => $signature,
            'video_file' => $file,
        ];

        !empty($filename) && $param['filename'] = $filename;

        return $this->fileRequest($url, $param, 'POST');
    }
}
