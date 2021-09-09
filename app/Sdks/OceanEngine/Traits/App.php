<?php

namespace App\Sdks\OceanEngine\Traits;

trait App
{
    /**
     * @var
     * 应用id
     */
    protected $appId;

    /**
     * @param $appId
     * @return bool
     * 设置应用
     */
    public function setAppId($appId){
        $this->appId = $appId;
        return true;
    }

    /**
     * @return mixed
     * 获取应用id
     */
    public function getAppId(){
        return $this->appId;
    }

    /**
     * @param $appId
     * @param $secret
     * @param $authCode
     * @param string $grantType
     * @return mixed
     * oauth
     */
    public function grant($appId, $secret, $authCode, $grantType = 'auth_code'){
        $url = $this->getUrl('oauth2/access_token/');

        $param = [
            'app_id' => $appId,
            'secret' => $secret,
            'grant_type' => $grantType,
            'auth_code' => $authCode,
        ];

        return $this->publicRequest($url, $param, 'POST');
    }
}
