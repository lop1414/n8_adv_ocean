<?php

namespace App\Sdks\OceanEngine\Traits;

trait Error
{
    /**
     * @param $result
     * @return bool
     * 是否网络错误
     */
    public function isNetworkError($result){
        if(empty($result)){
            return true;
        }

        if(!isset($result['code'])){
            return true;
        }

        $errorCodes = [
            50000, // 系统错误
            51007, // 查询素材错误
        ];

        if(in_array($result['code'], $errorCodes)){
            return true;
        }

        return false;
    }

    /**
     * @param $result
     * @return bool
     * 是否视频不存在
     */
    public function isVideoNotExist($result){
        // 网络错误
        if($this->isNetworkError($result)){
            return false;
        }

        $errorCodes = [
            40501, // 视频不存在
        ];

        if(in_array($result['code'], $errorCodes)){
            return true;
        }

        return false;
    }
}
