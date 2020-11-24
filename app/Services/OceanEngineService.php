<?php

namespace App\Services;

use App\Common\Enums\AdvAccountBelongTypeEnum;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Models\OceanAccountModel;
use App\Sdks\OceanEngine\OceanEngine;

class OceanEngineService extends BaseService
{
    /**
     * @var OceanEngine
     * 句柄
     */
    public $sdk;

    /**
     * OceanEngineService constructor.
     * @param $appId
     */
    public function __construct($appId){
        parent::__construct();

        $this->sdk = new OceanEngine($appId);
    }

    /**
     * @param $accountId
     * @return bool
     * 设置账户id
     */
    public function setAccountId($accountId){
        // 设置账户id
        return $this->sdk->setAccountId($accountId);
    }

    /**
     * @throws CustomException
     * 设置 access_token (请求前必须调用)
     */
    private function setAccessToken(){
        $accountId = $this->sdk->getAccountId();

        // 获取账户信息
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccount = $oceanAccountModel->where('app_id', $this->sdk->getAppId())
            ->where('account_id', $accountId)
            ->first();

        if(empty($oceanAccount)){
            throw new CustomException([
                'code' => 'NOT_FOUND_OCEAN_ACCOUNT',
                'message' => "找不到该巨量账户{{$accountId}}",
            ]);
        }

        // token过期
        $datetime = date('Y-m-d H:i:s', time());
        if($datetime > $oceanAccount->fail_at){
            if($oceanAccount->belong_platform == AdvAccountBelongTypeEnum::SECOND_VERSION){
                $secondVersionService = new SecondVersionService();
                $secondVersionAccount = $secondVersionService->getJrttAdvAccount($oceanAccount->app_id, $oceanAccount->account_id);
                $oceanAccount->access_token = $secondVersionAccount['token'];
                $oceanAccount->fail_at = $secondVersionAccount['fail_at'];
                $oceanAccount->save();
            }
        }

        // 设置token
        $this->sdk->setAccessToken($oceanAccount->access_token);
    }

    /**
     * @param $accountIds
     * @return mixed
     * @throws CustomException
     * 获取账户信息
     */
    public function getAccountInfo($accountIds){
        $this->setAccessToken();

//        return $this->sdk->getAccountInfo($accountIds);

        return $this->sdk->getAccountPublicInfo($accountIds);
    }

    /**
     * @param $accountId
     * @param $signature
     * @param $file
     * @param string $filename
     * @return mixed
     * @throws CustomException
     * 上传视频
     */
    public function uploadVideo($accountId, $signature, $file, $filename = ''){
        $this->setAccessToken();

        return $this->sdk->uploadVideo($accountId, $signature, $file, $filename);
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

    /**
     * @param $uri
     * @param array $param
     * @param string $method
     * @param array $header
     * @return mixed
     * @throws CustomException
     * 转发
     */
    public function forward($uri, $param = [], $method = 'GET', $header = []){
        $this->setAccessToken();

        $url = OceanEngine::BASE_URL .'/'. ltrim($uri);

        return $this->sdk->authRequest($url, $param, $method, $header);
    }
}
