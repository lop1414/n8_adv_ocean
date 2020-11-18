<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Tools\CustomException;

trait Request
{
    /**
     * @param $url
     * @param $param
     * @param string $method
     * @param array $header
     * @return mixed
     * @throws CustomException
     * 携带认证请求
     */
    public function authRequest($url, $param = [], $method = 'GET', $header = []){
        $param = json_encode($param);

        // header 添加 Access-Token
        $header = array_merge([
            'Access-Token:'. $this->getAccessToken(),
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($param)
        ], $header);

        $ret = $this->publicRequest($url, $param, $method, $header);

        return $ret;
    }

    /**
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @return mixed
     * @throws CustomException
     * 文件请求
     */
    public function fileRequest($url, $param = [], $method = 'POST', $header = []){
        // header 添加 Access-Token
        $header = array_merge([
            'Access-Token:'. $this->getAccessToken()
        ], $header);

        $option = [
            'timeout' => 60,
        ];

        $ret = $this->publicRequest($url, $param, $method, $header, $option);

        return $ret;
    }

    /**
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return mixed
     * @throws CustomException
     * 公共请求
     */
    private function publicRequest($url, $param = [], $method = 'GET', $header = [], $option = []){
        $ret = $this->curlRequest($url, $param, $method, $header, $option);

        $result = json_decode($ret, true);

        if(empty($result) || $result['code'] != 0){
            // 错误提示
            $errorMessage = $result['msg'] ?? '公共请求错误';

            throw new CustomException([
                'code' => 'PUBLIC_REQUEST_ERROR',
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
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return bool|string
     * @throws CustomException
     * CURL请求
     */
    private function curlRequest($url, $param = [], $method = 'GET', $header = [], $option = []){
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

        $timeout = $option['timeout'] ?? 15;

        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $result = curl_exec($ch);

        //$info = curl_getinfo($ch);

        $errno = curl_errno($ch);
        if(!!$errno){
            throw new CustomException([
                'code' => 'CURL_REQUEST_ERROR',
                'message' => 'CURL请求错误',
                'log' => true,
                'data' => [
                    'url' => $url,
                    'header' => $header,
                    'param' => $param,
                    'result' => $result,
                    'error' => $errno,
                ],
            ]);
        }

        curl_close($ch);

        return $result;
    }
}
