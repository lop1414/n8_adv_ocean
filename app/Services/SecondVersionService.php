<?php

namespace App\Services;

use App\Common\Enums\AdvAccountBelongTypeEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Services\BaseService;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\CenterApiService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
use App\Services\Ocean\OceanService;

class SecondVersionService extends BaseService
{
    /**
     * @var mixed
     * 接口域名
     */
    public $baseUrl;

    /**
     * @var mixed
     * 接口密钥
     */
    public $secret;

    /**
     * SecondVersionService constructor.
     */
    public function __construct(){
        parent::__construct();

        $this->baseUrl = env('SECOND_VERSION_API_URL');
        $this->secret = env('SECOND_VERSION_API_SECRET');
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return mixed
     * @throws CustomException
     * 获取多个头条广告账户
     */
    public function getJrttAdvAccounts($page = 1, $pageSize = 10){
        $url = $this->baseUrl .'/api/adv_account/jrtt/get';

        $param = [
            'page' => $page,
            'page_size' => $pageSize,
        ];

        return $this->publicRequest($url, $param);
    }

    /**
     * @param $appId
     * @param $accountId
     * @return mixed
     * @throws CustomException
     * 获取单个头条账户
     */
    public function getJrttAdvAccount($appId, $accountId){
        $url = $this->baseUrl .'/api/adv_account/jrtt/get';

        $param = [
            'page' => 1,
            'page_size' => 1,
            'app_id' => $appId,
            'account_id' => $accountId,
        ];

        $data = $this->publicRequest($url, $param);

        return current($data['list']);
    }

    /**
     * @param int $pageSize
     * @return array
     * 获取所有头条广告账户
     */
    public function getJrttAllAdvAccount($pageSize = 100){
        // 获取所有
        $all = $this->getPageListAll(function($page) use($pageSize){
            return $this->getJrttAdvAccounts($page, $pageSize);
        });

        return $all;
    }

    /**
     * @param $func
     * @param int $page
     * @param int $pageSize
     * @return array
     * 获取分页列表所有数据
     */
    public function getPageListAll($func, $page = 1, $pageSize = 100){
        $all = [];
        do{
            $data = $func($page);

            $all = array_merge($all, $data['list']);

            $totalPage = $data['page_info']['total_page'] ?? 0;

            $page++;

            sleep(1);
        }while($page <= $totalPage);

        return $all;
    }

    /**
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @return mixed
     * @throws CustomException
     * 公共请求
     */
    public function publicRequest($url, $param = [], $method = 'POST', $header = []){
        // 构造签名
        $param['time'] = $param['time'] ?? time();
        $param['sign'] = $this->buildSign($param);

        $param = json_encode($param);

        $header = array_merge([
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($param)
        ], $header);

        $ret = $this->curlRequest($url, $param, $method, $header);

        $result = json_decode($ret, true);

        if(empty($result) || $result['code'] != 0){
            // 错误提示
            $errorMessage = '二版接口请求错误';

            throw new CustomException([
                'code' => 'SECOND_VERSION_API_REQUEST_ERROR',
                'message' => $errorMessage,
                'log' => true,
                'data' => [
                    'url' => $url,
                    'header' => $header,
                    'param' => $param,
                    'result' => $result,
                ],
            ]);
        }

        return $result['data'];
    }

    /**
     * @param $param
     * @return string
     * 构建签名
     */
    public function buildSign($param){
        return md5($this->secret . $param['time']);
    }

    /**
     * @param $url
     * @param $param
     * @param string $method
     * @param array $header
     * @return bool|string
     * CURL请求
     */
    private function curlRequest($url, $param = [], $method = 'POST', $header = []){
        $method = strtoupper($method);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $header = array_merge($header, ['Connection: close']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if(stripos($url, 'https://') === 0){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, true);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * @throws CustomException
     * 同步头条账户
     */
    public function syncJrttAccount(){
        // 获取账户
        $secondVersionAccounts = $this->getJrttAllAdvAccount();
//$tmp = $this->getJrttAdvAccounts(1, 10);
//$secondVersionAccounts = $tmp['list'];

        // 管理员映射
        $centerApiService = new CenterApiService();
        $adminUsers = $centerApiService->apiGetAdminUsers();
        $adminUserMap = array_column($adminUsers, 'id', 'name');

        foreach($secondVersionAccounts as $account){
            // 查找
            $oceanAccountModel = new OceanAccountModel();
            $oceanAccount = $oceanAccountModel->where('belong_platform', AdvAccountBelongTypeEnum::SECOND_VERSION)
                ->where('app_id', $account['app_id'])
                ->where('account_id', $account['adv_id'])
                ->first();

            if(empty($oceanAccount)){
                // 新增
                $oceanAccount = new OceanAccountModel();
                $oceanAccount->app_id = $account['app_id'];
                $oceanAccount->name = $account['name'];
                $oceanAccount->account_role = '';
                $oceanAccount->belong_platform = AdvAccountBelongTypeEnum::SECOND_VERSION;
                $oceanAccount->account_id = $account['adv_id'];
                $oceanAccount->access_token = $account['token'];
                $oceanAccount->refresh_token = '';
                $oceanAccount->fail_at = $account['fail_at'];
                $oceanAccount->extend = [];
                $oceanAccount->parent_id = $account['parent_adv_id'];
                $oceanAccount->status = StatusEnum::ENABLE;
                $oceanAccount->admin_id = $adminUserMap[$account['admin_name']] ?? 0;
            }else{
                // 更新
                $oceanAccount->access_token = $account['token'];
                $oceanAccount->fail_at = $account['fail_at'];
                $oceanAccount->admin_id = $adminUserMap[$account['admin_name']] ?? 0;
            }

            // 保存
            $oceanAccount->save();
        }

        // 同步头条账号信息
        $this->syncJrttAccountInfo();
    }

    /**
     * @return bool
     * 同步头条账户信息
     */
    public function syncJrttAccountInfo(){
        // 获取公司为空的账户
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccounts = $oceanAccountModel->where('belong_platform', AdvAccountBelongTypeEnum::SECOND_VERSION)
            ->where('company', '')
            ->get();

        foreach($oceanAccounts as $oceanAccount){
            try{
                // 获取账户信息
                $oceanService = new OceanService($oceanAccount->app_id);
                $oceanService->setAccountId($oceanAccount->account_id);
                $accountInfoList = $oceanService->getAccountInfoList([$oceanAccount->account_id]);
                $accountInfo = current($accountInfoList);

                // 保存
                $oceanAccount->company = $accountInfo['company'] ?? '';
                $oceanAccount->save();
            }catch(CustomException $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);
            }catch(\Exception $e){
                $errorLogService = new ErrorLogService();
                $errorLogService->catch($e);
            }
        }

        return true;
    }
}
