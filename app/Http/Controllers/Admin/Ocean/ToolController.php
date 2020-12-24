<?php

namespace App\Http\Controllers\Admin\Ocean;

use App\Common\Controllers\Admin\AdminController;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanCampaignStatusEnum;
use App\Enums\Ocean\OceanSyncTypeEnum;
use App\Models\Ocean\OceanAccountModel;
use App\Services\Ocean\OceanCampaignService;
use App\Services\Ocean\OceanService;
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

        $syncType = $this->getUriSyncType($uri);
        if($syncType == OceanSyncTypeEnum::CAMPAIGN){
            // 广告组
            $oceanCampaignService = new OceanCampaignService($data['app_id']);

            $option = [
                'account_ids' => $data['account_id'],
                'status' => OceanCampaignStatusEnum::CAMPAIGN_STATUS_ALL,
            ];

            if(!empty($result['campaign_id'])){
                $option['ids'] = [$result['campaign_id']];
            }elseif(!empty($result['campaign_ids'])){
                $option['ids'] = $result['campaign_ids'];
            }

            $oceanCampaignService->syncCampaign($option);
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
}
