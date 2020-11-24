<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\OceanAccountModel;
use App\Services\OceanEngineService;
use Illuminate\Http\Request;

class ToolController extends AdminController
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
     * @throws CustomException
     * 转发
     */
    public function forward(Request $request){
        $this->validRule($request->post(), [
            'account_id' => 'required',
            'uri' => 'required',
        ]);

        $uri = $request->post('uri');
        $account_id = $request->post('account_id');
        $param = $request->post('param');
        $header = $request->post('header', []);
        $method = $request->post('method', 'GET');

        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        // 查找用户
        $oceanAccountModel = new OceanAccountModel();
        $builder = $oceanAccountModel->where('account_id', $account_id);
        if(!$adminUserInfo['is_admin']){
            $builder->where('admin_id', $adminUserInfo['admin_user']['id']);
        }
        $account = $builder->first();

        if(empty($account)){
            throw new CustomException([
                'code' => 'NOT_FOUND_ACCOUNT',
                'message' => '找不到该账户'
            ]);
        }

        $oceanEngineService = new OceanEngineService($account->app_id);
        $oceanEngineService->setAccountId($account->account_id);
        $data = $oceanEngineService->forward($uri, $param, $method, $header);

        return $this->success($data);
    }
}
