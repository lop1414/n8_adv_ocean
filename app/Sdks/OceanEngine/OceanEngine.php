<?php

namespace App\Sdks\OceanEngine;

use App\Sdks\OceanEngine\Traits\AccessToken;
use App\Sdks\OceanEngine\Traits\Account;
use App\Sdks\OceanEngine\Traits\Ad;
use App\Sdks\OceanEngine\Traits\AdConvert;
use App\Sdks\OceanEngine\Traits\App;
use App\Sdks\OceanEngine\Traits\Campaign;
use App\Sdks\OceanEngine\Traits\City;
use App\Sdks\OceanEngine\Traits\Error;
use App\Sdks\OceanEngine\Traits\Image;
use App\Sdks\OceanEngine\Traits\Industry;
use App\Sdks\OceanEngine\Traits\Material;
use App\Sdks\OceanEngine\Traits\Multi;
use App\Sdks\OceanEngine\Traits\Region;
use App\Sdks\OceanEngine\Traits\Report;
use App\Sdks\OceanEngine\Traits\Request;
use App\Sdks\OceanEngine\Traits\Video;

class OceanEngine
{
    use App;
    use Account;
    use AccessToken;
    use Request;
    use Video;
    use Image;
    use Campaign;
    use Multi;
    use Material;
    use Region;
    use City;
    use Industry;
    use Ad;
    use AdConvert;
    use Error;
    use Report;

    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://ad.oceanengine.com/open_api';

    /**
     * OceanEngine constructor.
     * @param $appId
     */
    public function __construct($appId = ''){
        $this->setAppId($appId);
    }

    /**
     * @param $uri
     * @return string
     * 获取请求地址
     */
    public function getUrl($uri){
        return self::BASE_URL .'/'. ltrim($uri, '/');
    }

    /**
     * @param string $path
     * @return string
     * 获取 sdk 路径
     */
    public function getSdkPath($path = ''){
        $path = rtrim($path, '/');
        $sdkPath = rtrim(__DIR__ .'/'. $path, '/');
        return $sdkPath;
    }
}
