<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;

class OceanImageService extends OceanService
{
    /**
     * OceanImageService constructor.
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
     * 上传图片
     */
    public function uploadImage($accountId, $signature, $file, $filename = ''){
        $this->setAccessToken();

        return $this->sdk->uploadImage($accountId, $signature, $file, $filename);
    }
}
