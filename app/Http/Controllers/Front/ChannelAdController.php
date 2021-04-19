<?php

namespace App\Http\Controllers\Front;

use App\Common\Controllers\Front\FrontController;
use App\Services\ChannelAdService;
use Illuminate\Http\Request;

class ChannelAdController extends FrontController
{
    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 更新
     */
    public function update(Request $request){
        $data = $request->post();

        $channelAdService = new ChannelAdService($data);
        $ret = $channelAdService->update($data);

        return $this->ret($ret);
    }
}
