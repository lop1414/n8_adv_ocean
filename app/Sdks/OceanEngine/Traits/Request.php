<?php

namespace App\Sdks\OceanEngine\Traits;

use App\Common\Enums\ExceptionTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;

trait Request
{
    /**
     * @var int
     * 并发请求块大小
     */
    protected $multiChunkSize = 3;

    /**
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return mixed
     * @throws CustomException
     * 携带认证请求
     */
    public function authRequest($url, $param = [], $method = 'GET', $header = [], $option = []){
        // 无过滤
        if(isset($param['filtering']) && empty($param['filtering'])){
            unset($param['filtering']);
        }

        $param = json_encode($param);

        // header 添加 Access-Token
        $header = array_merge([
            'Access-Token:'. $this->getAccessToken(),
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($param)
        ], $header);

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
     * 文件请求
     */
    public function fileRequest($url, $param = [], $method = 'POST', $header = [], $option = []){
        // header 添加 Access-Token
        $header = array_merge([
            'Access-Token:'. $this->getAccessToken()
        ], $header);

        $option['timeout'] = $option['timeout'] ?? 180;

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

        if(empty($result) || !isset($result['code']) || $result['code'] != 0){
            // 错误提示
            $errorMessage = $result['message'] ?? '公共请求错误';

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
        $ch = $this->buildCurl($url, $param, $method, $header, $option);

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

    /**
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return false|resource
     * 构建curl
     */
    private function buildCurl($url, $param = [], $method = 'GET', $header = [], $option = []){
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

        $timeout = $option['timeout'] ?? 30;

        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        return $ch;
    }

    /**
     * @param $chunkSize
     * @return bool
     * 设置并发请求块大小
     */
    public function setMultiChunkSize($chunkSize){
        $this->multiChunkSize = $chunkSize;
        return true;
    }

    /**
     * @return int
     * 获取并发请求块大小
     */
    public function getMultiChunkSize(){
        return $this->multiChunkSize;
    }

    /**
     * @param $curlOptions
     * @return array
     * 并发请求
     */
    public function multiPublicRequest($curlOptions){
        $chunkSize = $this->getMultiChunkSize();
        $chunks = array_chunk($curlOptions, $chunkSize);

        //Functions::consoleDump("ocean multi public chuck size({$chunkSize})");
        $i = 1;

        $response = [];
        foreach($chunks as $chunk){
            $chunkCount = count($chunk);
            //Functions::consoleDump("chunk block({$i}), count({$chunkCount})");

            $response = array_merge($response, $this->multiCurlRequest($chunk));

            $i++;
        }

        $succ = $err = [];
        foreach($response as $k => $v){

            if(!empty($v['error'])){
                $errorLogService = new ErrorLogService();
                $errorLogService->create(
                    'MULTI_CURL_REQUEST_ERROR',
                    '并发CURL请求错误',
                    [
                        'url' => $v['url'],
                        'error' => $v['error'],
                        'info' => $v['info'],
                        'req' => $v['req'],
                    ],
                    ExceptionTypeEnum::CUSTOM
                );

                continue;
            }

            $result = json_decode($v['result'], true);
            if(!isset($result['code']) || $result['code'] != 0){
                // 错误提示
                $errorMessage = $result['msg'] ?? '并发请求错误';

                $errorLogService = new ErrorLogService();
                $errorLogService->create(
                    'MULTI_REQUEST_ERROR',
                    $errorMessage,
                    [
                        'url' => $v['url'],
                        'error' => $v['error'],
                        'info' => $v['info'],
                        'req' => $v['req'],
                        'result' => $result,
                    ],
                    ExceptionTypeEnum::CUSTOM
                );

                continue;
            }

            $succ[] = [
                'data' => $result['data'],
                'req' => $v['req'],
            ];
        }

        return $succ;
    }

    /**
     * @param $curlOptions
     * @return array
     * 并发curl请求
     */
    private function multiCurlRequest($curlOptions){
        $mh = curl_multi_init();
        $chs = $reqs = [];
        foreach($curlOptions as $i => $curlOption){
            // 无过滤
            if(empty($curlOption['param']['filtering'])){
                unset($curlOption['param']['filtering']);
            }

            // 默认值
            $url = $curlOption['url'];
            $param = json_encode($curlOption['param']) ?? '';
            $method = $curlOption['method'] ?? 'GET';
            $header = $curlOption['header'] ?? [];
            $option = $curlOption['option'] ?? [];
            // 构造句柄
            $ch = $this->buildCurl($url, $param, $method, $header, $option);

            curl_multi_add_handle($mh, $ch);
Functions::consoleDump($ch);

            $chs[strval($ch)] = $ch;
            $reqs[strval($ch)] = [
                'url' => $url,
                'param' => $param,
                'method' => $method,
                'header' => $header,
                'option' => $option,
            ];
        }

        $res = [];
        do{
            if(($status = curl_multi_exec($mh, $active)) != CURLM_CALL_MULTI_PERFORM){
                if ($status != CURLM_OK) { break; } //如果没有准备就绪，就再次调用curl_multi_exec
                while ($done = curl_multi_info_read($mh)) {
                    $info = curl_getinfo($done["handle"]);
                    $error = curl_error($done["handle"]);
                    $result = curl_multi_getcontent($done["handle"]);
                    $req = $reqs[strval($done["handle"])];
                    $rtn = compact('info', 'error', 'result', 'url', 'req');

                    $res[] = $rtn;
                    curl_multi_remove_handle($mh, $done['handle']);
                    curl_close($done['handle']);

                    // 如果仍然有未处理完毕的句柄，那么就select
                    if ($active > 0) {
                        // 阻塞
                        curl_multi_select($mh, 1);
                    }
                }
            }
        }while($active > 0);

        curl_multi_close($mh);

        return $res;
    }
}
