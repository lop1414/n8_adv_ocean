<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Models\Ocean\OceanAccountModel;
use App\Services\Ocean\OceanAdCreativeCreateService;
use App\Services\Ocean\OceanService;
use App\Services\Ocean\OceanToolService;
use Illuminate\Http\Request;

class ToolController extends OceanController
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
        $debug = $request->post('debug');

        if(!empty($debug)){
            $header = array_merge($header, ['X-Debug-Mode: 1']);
        }

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

        $OceanService = new OceanService($account->app_id);
        $OceanService->setAccountId($account->account_id);
        $result = $OceanService->forward($uri, $param, $method, $header);

        $this->forwardAfter([
            'uri' => $uri,
            'app_id' => $account->app_id,
            'account_id' => $account->account_id,
            'param' => $param,
            'result' => $result,
        ]);

        return $this->success($result);
    }

    /**
     * @param $data
     * @throws CustomException
     * 转发后操作
     */
    private function forwardAfter($data){
        $uri = $data['uri'] ?? '';
        $result = $data['result'] ?? [];

        // 获取 uri 对应同步类型
        $syncType = $this->getUriSyncType($uri);

        // 同步
        if(!empty($syncType)){
            ini_set('max_execution_time', 60);

            if(strpos($uri, 'create') !== false){
                // 休眠防延迟
                sleep(10 );
            }

            $syncParam = array_merge([
                'app_id' => $data['app_id'],
                'account_id' => $data['account_id'],
            ], $result);

            // 同步
            $oceanToolService = new OceanToolService();
            $oceanToolService->sync($syncType, $syncParam);
        }
    }

    /**
     * @param $uri
     * @return bool|int|string
     * 获取同步类型
     */
    private function getUriSyncType($uri){
        // 映射
        $map = [
            OceanSyncTypeEnum::CAMPAIGN => [
                '2/campaign/create/',
                '2/campaign/update/',
                '2/campaign/update/status/',
            ],
            OceanSyncTypeEnum::AD => [
                '2/ad/create/',
                '2/ad/update/',
                '2/ad/update/status/',
                '2/ad/update/budget/',
                '2/ad/update/bid/',
            ],
        ];

        foreach($map as $syncType => $syncUris){
            foreach($syncUris as $syncUri){
                if(rtrim($uri, '/') == rtrim($syncUri, '/')){
                    return $syncType;
                }
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws CustomException
     * 批量创建计划创意
     */
    public function batchCreateAdCreative(Request $request){
        // 超时时间
        ini_set('max_execution_time', 120);

        $items = $request->post('items');
        $rule = $request->post('rule');
        $ruleOption = $request->post('rule_option', []);

        $oceanAdCreativeCreateService = new OceanAdCreativeCreateService();
        $taskId = $oceanAdCreativeCreateService->batchCreateAdCreative($items, $rule, $ruleOption);

        return $this->success(['task_id' => $taskId], [], '批量上传任务已提交【任务id:'. $taskId .'】，执行结果后续同步到飞书，请注意查收！');
    }
}
