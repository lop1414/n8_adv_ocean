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

    /**
     * @param $appId
     * @param $secret
     * @param $refreshToken
     * @param string $grantType
     * @return mixed
     * 刷新access token
     */
    public function refreshAccessToken($appId, $secret, $refreshToken, $grantType = 'refresh_token'){
        $url = $this->getUrl('oauth2/refresh_token/');

        $param = json_encode([
            'app_id' => $appId,
            'secret' => $secret,
            'grant_type' => $grantType,
            'refresh_token' => $refreshToken,
        ]);

        $header = ['Content-Type: application/json; charset=utf-8'];

        return $this->publicRequest($url, $param, 'POST', $header);
    }
}
