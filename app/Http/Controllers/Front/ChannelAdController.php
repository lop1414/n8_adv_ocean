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
     * 批量更新
     */
    public function batchUpdate(Request $request){
        $data = $request->post();

        $channelAdService = new ChannelAdService();
        $ret = $channelAdService->batchUpdate($data);

        return $this->ret($ret);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 列表
     */
    public function select(Request $request){
        $param = $request->post();

        $channelAdService = new ChannelAdService();
        $data = $channelAdService->select($param);

        return $this->success($data);
    }
}
