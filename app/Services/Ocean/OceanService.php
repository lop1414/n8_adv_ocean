<?php

namespace App\Services\Ocean;

use App\Common\Enums\AdvAccountBelongTypeEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountModel;
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
     * @param $accountId
     * @return bool
     * 设置账户id
     */
    public function setAccountId($accountId){
        // 设置账户id
        return $this->sdk->setAccountId($accountId);
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
     * @return mixed
     * @throws CustomException
     * 重载失效 access_token
     */
    private function reloadFailAccessToken($oceanAccount){
        // token过期
        $datetime = date('Y-m-d H:i:s', time());
        if($datetime > $oceanAccount->fail_at){
            if($oceanAccount->belong_platform == AdvAccountBelongTypeEnum::SECOND_VERSION){
                $secondVersionService = new SecondVersionService();
                $secondVersionAccount = $secondVersionService->getJrttAdvAccount($oceanAccount->app_id, $oceanAccount->account_id);

                if(!empty($secondVersionAccount)){
                    $oceanAccount->access_token = $secondVersionAccount['token'];
                    $oceanAccount->fail_at = $secondVersionAccount['fail_at'];
                    $oceanAccount->save();
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

        $group = [];
        foreach($subAccount as $account){
            $group[$account->parent_id][] = $account;
        }

        return $group;
    }

    /**
     * @param $type
     * @param $accounts
     * @param $filtering
     * @param $pageSize
     * @return array
     * @throws CustomException
     * 并发获取分页列表
     */
    public function multiGetPageList($type, $accounts, $filtering, $pageSize){
        // 获取分页列表方法
        $funcMap = [
            'campaign' => 'multiGetCampaignList',
            'video' => 'multiGetVideoList',
        ];
        if(!isset($funcMap[$type])){
            throw new CustomException([
                'code' => 'MULTI_GET_PAGE_LIST_TYPE_ERROR',
                'message' => '并发获取分页列表类型错误',
            ]);
        }
        $func = $funcMap[$type];

        // 重载 access_token
        $accessToken = '';
        foreach($accounts as $account){
            $account = $this->reloadFailAccessToken($account);

            $accessToken = $account->access_token;
        }

        // 账户第一页数据
        $accountIds = [];
        foreach($accounts as $account){
            $accountIds[] = $account->account_id;
        }
        $res = $this->sdk->$func($accountIds, $accessToken, $filtering, 1, $pageSize);

        // 查询其他页数
        $more = [];
        foreach($res as $v){
            if(empty($v['req']['param'])){
                continue;
            }
            $param = json_decode($v['req']['param'], true);

            $totalPage = $v['data']['page_info']['total_page'] ?? 1;
            $advertiserId = $param['advertiser_id'] ?? 0;

            if($advertiserId > 0 && $totalPage > 1){
                for($i = 2; $i <= $totalPage; $i++){
                    $more[$i][] = $advertiserId;
                }
            }
        }

        // 多页数据
        foreach($more as $page => $accountIds){
            $tmp = $this->sdk->$func($accountIds, $accessToken, $filtering, $page, $pageSize);
            $res = array_merge($res, $tmp);
        }

        // 数据过滤
        $list = [];
        foreach($res as $v){
            if(empty($v['data']['list']) || empty($v['req']['param'])){
                continue;
            }
            $param = json_decode($v['req']['param'], true);

            foreach($v['data']['list'] as $item){
                $item['advertiser_id'] = $param['advertiser_id'];
                $item['account_id'] = $param['advertiser_id'];
                $list[] = $item;
            }
        }

        return $list;
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
     * @param $uri
     * @param array $param
     * @param string $method
     * @param array $header
     * @return mixed
     * @throws CustomException
     * 转发
     */
    public function forward($uri, $param = [], $method = 'GET', $header = []){
        $this->setAccessToken();

        $url = OceanEngine::BASE_URL .'/'. ltrim($uri);

        return $this->sdk->authRequest($url, $param, $method, $header);
    }
}
