<?php

namespace App\Http\Controllers\Admin;

use App\Common\Controllers\Admin\AdminController;
use App\Services\ChannelAdService;
use Illuminate\Http\Request;

class ChannelAdController extends AdminController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 更新
     */
    public function update(Request $request){
        $data = $request->post();

        $channelAdService = new ChannelAdService();
        $ret = $channelAdService->update($data);

        return $this->ret($ret);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 详情
     */
    public function read(Request $request){
        $data = $request->post();

        $channelAdService = new ChannelAdService();
        $channelAd = $channelAdService->read($data);

        return $this->success($channelAd);
    }
}
