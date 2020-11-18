<?php

namespace App\Sdks\OceanEngine;

use App\Sdks\OceanEngine\Traits\AccessToken;
use App\Sdks\OceanEngine\Traits\Account;
use App\Sdks\OceanEngine\Traits\App;
use App\Sdks\OceanEngine\Traits\Image;
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

    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://ad.oceanengine.com/open_api';

    /**
     * OceanEngine constructor.
     * @param $appId
     */
    public function __construct($appId){
        $this->setAppId($appId);
    }
}
