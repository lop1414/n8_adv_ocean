<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait AccessToken
{
    /**
     * @var
     * access token
     */
    protected $accessToken;

    /**
     * @param $accessToken
     * @return bool
     * 设置 access token
     */
    public function setAccessToken($accessToken){
        $this->accessToken = $accessToken;
        return true;
    }

    /**
     * @return mixed
     * @throws CustomException
     * 获取 access token
     */
    public function getAccessToken(){
        if(is_null($this->accessToken)){
            throw new CustomException([
                'code' => 'NOT_FOUND_ACCESS_TOKEN',
                'message' => '尚未设置access_token',
                'log' => true,
            ]);
        }
        return $this->accessToken;
    }
}
