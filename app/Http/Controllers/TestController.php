<?php

namespace App\Http\Controllers;



use App\Services\AdvConvertCallbackService;
use Illuminate\Http\Request;

class TestController extends Controller
{



    public function test(Request $request){
        $key = $request->input('key');
        if($key != 'aut'){
            return $this->forbidden();
        }

        // 测试事件回传 link
        (new AdvConvertCallbackService())->run();
    }
}
