<?php

namespace App\Services\Ocean;

use App\Common\Enums\AdvAccountBelongTypeEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Ocean\OceanAdModel;
use App\Sdks\OceanEngine\OceanEngine;
use App\Services\SecondVersionService;

class OceanService extends BaseService
{
    /**
     * @var OceanEngine
     * 句柄
     */
    public $sdk;

    /**
     * OceanService constructor.
     * @param $appId
     */
    public function __construct($appId){
        parent::__construct();

        $this->sdk = new OceanEngine($appId);
    }

    /**
     * @param $appId
     * @return bool
     * 设置应用id
     */
    public function setAppId($appId){
        return $this->sdk->setAppId($appId);
    }

    /**
     * @param $accountId
     * @return bool
     * 设置账户id
     */
    public function setAccountId($accountId){
        // 设置账户id
        return $this->sdk->setAccountId($accountId);
    }

    /**
     * @param $accountId
     * @return mixed
     * 获取账户
     */
    public function getAccount($accountId){
        $oceanAccountModel = new OceanAccountModel();
        $account = $oceanAccountModel->where('account_id', $accountId)->first();
        return $account;
    }

    /**
     * @param $accountIds
     * @return mixed
     * @throws CustomException
     * 获取账户信息
     */
    public function getAccountInfoList($accountIds){
        $this->setAccessToken();

//        return $this->sdk->getAccountInfo($accountIds);

        return $this->sdk->getAccountPublicInfo($accountIds);
    }

    /**
     * @throws CustomException
     * 设置 access_token (请求前必须调用)
     */
    protected function setAccessToken(){
        $accountId = $this->sdk->getAccountId();

        // 获取账户信息
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccount = $oceanAccountModel->where('app_id', $this->sdk->getAppId())
            ->where('account_id', $accountId)
            ->first();

        if(empty($oceanAccount)){
            throw new CustomException([
                'code' => 'NOT_FOUND_OCEAN_ACCOUNT',
                'message' => "找不到该巨量账户{{$accountId}}",
            ]);
        }

        // 重载失效 access_token
        $oceanAccount = $this->reloadFailAccessToken($oceanAccount);

        // 设置token
        $this->sdk->setAccessToken($oceanAccount->access_token);
    }

    /**
     * @param $oceanAccount
     * @return bool
     * access_token是否已失效
     */
    private function isFailAccessToken($oceanAccount){
        $datetime = date('Y-m-d H:i:s', time());
        return $datetime > $oceanAccount->fail_at;
    }

    /**
     * @param $oceanAccount
     * @return mixed
     * @throws CustomException
     * 重载失效 access_token
     */
    private function reloadFailAccessToken($oceanAccount){
        if($this->isFailAccessToken($oceanAccount)){
            Functions::consoleDump('reload fail access token');
            if($oceanAccount->belong_platform == AdvAccountBelongTypeEnum::SECOND_VERSION){
                $secondVersionService = new SecondVersionService();
                $secondVersionAccount = $secondVersionService->getJrttAdvAccount($oceanAccount->app_id, $oceanAccount->account_id);

                if(!empty($secondVersionAccount)){
                    $oceanAccount->access_token = $secondVersionAccount['token'];
                    $oceanAccount->fail_at = $secondVersionAccount['fail_at'];
                    $oceanAccount->save();

                    // 父账户
//                    $oceanAccountModel = new OceanAccountModel();
//                    $oceanAccountModel->whereRaw("parent_id = '{$oceanAccount->parent_id}' OR id = '{$oceanAccount->parent_id}'")->update([
//                        'access_token' => $secondVersionAccount['token'],
//                        'fail_at' => $secondVersionAccount['fail_at'],
//                    ]);
                }
            }
        }
        return $oceanAccount;
    }

    /**
     * @param array $accountIds
     * @return mixed
     * 获取子账号
     */
    public function getSubAccount(array $accountIds = []){
        $oceanAccountModel = new OceanAccountModel();
        $builder = $oceanAccountModel->where('status', StatusEnum::ENABLE);

        if(!empty($accountIds)){
            $accountIdsStr = implode("','", $accountIds);
            $builder->whereRaw("
                (
                    account_id IN ('{$accountIdsStr}') 
                    OR parent_id IN ('{$accountIdsStr}')
                )
            ");
        }

        $subAccount = $builder->where('parent_id', '<>', 0)->get();

        return $subAccount;
    }

    /**
     * @param array $accountIds
     * @return array
     * 获取子账号组
     */
    public function getSubAccountGroup(array $accountIds = []){
        $subAccount = $this->getSubAccount($accountIds);

        $s = [];
        foreach($subAccount as $account){
            $s[$account->parent_id][] = $account;
        }

        $groupSize = 10;

        $group = [];
        foreach($s as $ss){
            $chunks = array_chunk($ss, $groupSize);
            foreach($chunks as $chunk){
                $group[] = $chunk;
            }
        }

        foreach($group as $k => $chunk){
            foreach($chunk as $v){
                $tmp[$v->account_id] = 1;
            }
        }

        return $group;
    }

    /**
     * @param $accountIds
     * @param $accessToken
     * @param $filtering
     * @param $page
     * @param $pageSize
     * @param array $param
     * @throws CustomException
     * sdk批量获取列表
     */
    public function sdkMultiGetList($accountIds, $accessToken, $filtering, $page, $pageSize, $param = []){
        throw new CustomException([
            'code' => 'PLEASE_WRITE_SDK_MULTI_GET_LIST_CODE',
            'message' => '请书写sdk批量获取列表代码',
        ]);
    }

    /**
     * @param $accounts
     * @param $filtering
     * @param $pageSize
     * @param array $param
     * @return array
     * @throws CustomException
     * 并发获取分页列表
     */
    public function multiGetPageList($accounts, $filtering, $pageSize, $param = []){
// 是否打印
$dump = false;
        // 重载 access_token
        $accessToken = '';
        foreach($accounts as $account){
$dump && Functions::consoleDump('=============== start =================');
            if($this->isFailAccessToken($account)){
$dump && Functions::consoleDump('access token is fail, account id:'. $account->account_id .', fail at:'. $account->fail_at);
                // 查找父级账户
                $oceanParentAccounts = Functions::getGlobalData('ocean_parent_accounts') ?? [];
$dump && Functions::consoleDump('parent global data account count:'. count($oceanParentAccounts));
                if(isset($oceanParentAccounts[$account->parent_id])){
$dump && Functions::consoleDump('has parent global data, parent id:'. $account->parent_id);
                    $parentAccount = $oceanParentAccounts[$account->parent_id];
                }else{
$dump && Functions::consoleDump('find parent data from db, parent id:'. $account->parent_id);
                    $oceanAccountModel = new OceanAccountModel();
                    $parentAccount = $oceanAccountModel->where('app_id', $account->app_id)
                        ->where('account_id', $account->parent_id)
                        ->first();
                }

                // 重载父账户 access_token
                if($this->isFailAccessToken($parentAccount)){
$dump && Functions::consoleDump('parent access token is fail, parent id:'. $account->parent_id .', fail at:'. $parentAccount->fail_at);
                    $parentAccount = $this->reloadFailAccessToken($parentAccount);
$dump && Functions::consoleDump('reload parent access token, parent id:'. $account->parent_id .', fail at:'. $parentAccount->fail_at);
                    $oceanParentAccounts[$parentAccount->account_id] = $parentAccount;
                    Functions::setGlobalData('ocean_parent_accounts', $oceanParentAccounts);
$dump && Functions::consoleDump('parent global data account count:'. count($oceanParentAccounts));
                }else{
                    $accessToken = $parentAccount->access_token;
                }
            }else{
                $accessToken = $account->access_token;
            }

$dump && Functions::consoleDump('=============== end =================');
        }

        // 账户第一页数据
        $accountIds = [];
        foreach($accounts as $account){
            $accountIds[] = $account->account_id;
        }
        $res = $this->sdkMultiGetList($accountIds, $accessToken, $filtering, 1, $pageSize, $param);

        // 查询其他页数
        $more = [];
        foreach($res as $v){
            if(empty($v['req']['param'])){
                continue;
            }
            $reqParam = json_decode($v['req']['param'], true);

            $totalPage = $v['data']['page_info']['total_page'] ?? 1;
            $advertiserId = $reqParam['advertiser_id'] ?? 0;

            if($advertiserId > 0 && $totalPage > 1){
                for($i = 2; $i <= $totalPage; $i++){
                    $more[$i][] = $advertiserId;
                }
            }
        }

        // 多页数据
        foreach($more as $page => $accountIds){
            $tmp = $this->sdkMultiGetList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
            $res = array_merge($res, $tmp);
        }

        // 后置处理
        $res = $this->multiGetPageListAfter($res);

        // 数据过滤
        $list = [];
        foreach($res as $v){
            if(empty($v['data']['list']) || empty($v['req']['param'])){
                continue;
            }
            $reqParam = json_decode($v['req']['param'], true);

            foreach($v['data']['list'] as $item){
                $item['advertiser_id'] = $reqParam['advertiser_id'];
                $item['account_id'] = $reqParam['advertiser_id'];
                $list[] = $item;
            }
        }
        return $list;
    }

    /**
     * @param $res
     * @return mixed
     * 并发获取分页列表后置处理
     */
    public function multiGetPageListAfter($res){
        return $res;
    }

    /**
     * @param $func
     * @param int $page
     * @return array
     * 获取列表所有数据
     */
    public function getPageListAll($func, $page = 1){
        $all = [];
        do{
            $data = $func($page);

            $all = array_merge($all, $data['list']);

            $totalPage = $data['page_info']['total_page'] ?? 0;

            $page++;

            sleep(1);
        }while($page <= $totalPage);

        return $all;
    }

    /**
     * @return mixed
     * @throws CustomException
     * 获取有效账户
     */
    public function getValidAccount(){
        $oceanAccountModel = new OceanAccountModel();
        $oceanAccount = $oceanAccountModel->where('status', StatusEnum::ENABLE)->first();

        // 重载
        $oceanAccount = $this->reloadFailAccessToken($oceanAccount);

        return $oceanAccount;
    }

    /**
     * @return mixed
     * 获取在跑账户id
     */
    public function getRunningAccountIds(){
        // 在跑状态
        $runningStatus = [
            OceanAdStatusEnum::AD_STATUS_DELIVERY_OK,
        ];
        $runningStatusStr = implode("','", $runningStatus);

        $oceanAccountModel = new OceanAccountModel();
        $oceanAccountIds = $oceanAccountModel->whereRaw("
            account_id IN (
                SELECT account_id FROM ocean_ads
                    WHERE `status` IN ('{$runningStatusStr}')
                    GROUP BY account_id
            )
        ")->pluck('account_id');

        return $oceanAccountIds->toArray();
    }

    /**
     * @param $uri
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return mixed
     * @throws CustomException
     * 转发
     */
    public function forward($uri, $param = [], $method = 'GET', $header = [], $option = []){
        $this->setAccessToken();

        $url = OceanEngine::BASE_URL .'/'. ltrim($uri);

        return $this->sdk->authRequest($url, $param, $method, $header, $option);
    }
}
