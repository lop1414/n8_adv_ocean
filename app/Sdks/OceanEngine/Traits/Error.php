<?php

namespace App\Sdks\OceanEngine\Traits;

trait Error
{
    /**
     * @return array
     * 获取返回映射
     */
    public function getCodeMessageMap(){
        return [
            // 成功返回
            0 => '成功',

            // 通用返回
            40001 => '参数错误',
            40002 => '没有权限进行相关操作',
            40003 => '过滤条件的field字段错误',

            // auth相关
            40100 => '请求过于频繁',
            40101 => '不合法的接入用户',
            40102 => 'access token过期',
            40103 => 'refresh token过期',
            40104 => 'access token为空',
            40105 => 'access token错误',
            40106 => '账户登录异常',
            40107 => 'refresh token错误',
            40108 => '授权类型错误',
            40109 => '密码AES加密错误',

            // 财务相关
            40200 => '充值金额太少',
            40201 => '账户余额不足',

            // 广告主相关
            40300 => '广告主状态不可用',
            40301 => '广告主在黑名单中',
            40302 => '密码过于简单',
            40303 => '邮箱已存在',
            40304 => '邮箱不合法',
            40305 => '名字已存在',

            // 系统相关
            50000 => '系统错误',

            // 补充
            51007 => '查询素材错误',
            40501 => '视频不存在',
        ];
    }

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

    /**
     * @param $result
     * @return bool
     * 是否没权限操作
     */
    public function isNotPermission($result){
        $errorCodes = [
            40002, // 没权限操作
        ];

        if(in_array($result['code'], $errorCodes)){
            return true;
        }

        return false;
    }
}
