<?php

namespace App\Http\Controllers\Front;

use App\Common\Controllers\Front\ClickController;
use App\Common\Enums\AdvAliasEnum;
use Illuminate\Http\Request;

class AdvClickController extends ClickController
{
    public function __construct(){
        parent::__construct(AdvAliasEnum::OCEAN);
    }

    /**
     * @return false|string
     * 广告商响应
     */
    protected function advResponse(){
        return json_encode([
            'code' => 0,
            'message' => 'SUCCESS'
        ]);
    }
}
