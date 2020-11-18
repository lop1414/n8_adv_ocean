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
        $url = self::BASE_URL .'/2/file/image/ad/';

        $param = [
            'advertiser_id' => $accountId,
            'image_signature' => $signature,
            'image_file' => $file,
        ];

        !empty($filename) && $param['filename'] = $filename;

        return $this->fileRequest($url, $param, 'POST');
    }
}
