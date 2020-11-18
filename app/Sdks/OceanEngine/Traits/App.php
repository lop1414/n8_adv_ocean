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
}
