<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;

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
}
