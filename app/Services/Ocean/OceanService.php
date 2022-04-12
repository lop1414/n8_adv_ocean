<?php

namespace App\Services\Ocean;

use App\Common\Enums\AdvAccountBelongTypeEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Ocean\Report\OceanAccountReportModel;
use App\Sdks\OceanEngine\OceanEngine;
use Illuminate\Support\Facades\DB;

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

        $subAccount = $builder->where('parent_id', '<>', 0)->orderBy('id', 'desc')->get();

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
        $accessToken = '';
        foreach($accounts as $account){
            $accessToken = $account->access_token;
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
     * @param $accountIds
     * @param null $date
     * @return mixed
     * @throws CustomException
     * 获取存在历史消耗账户
     */
    public function getHasHistoryCostAccount($accountIds, $date = null){
        if(empty($date)){
            $date = date('Y-m-d');
        }else{
            Functions::dateCheck($date);
        }
        $startDate = date('Y-m-d', strtotime('-3 days', strtotime($date)));

        $oceanAccountReportModel = new OceanAccountReportModel();
        $builder = $oceanAccountReportModel->whereBetween('stat_datetime', ["{$startDate} 00:00:00", "{$date} 23:59:59"]);

        if(!empty($accountIds)){
            $builder->whereIn('account_id', $accountIds);
        }

        $report = $builder->groupBy('account_id')
            ->orderBy('cost', 'DESC')
            ->select(DB::raw("account_id, SUM(cost) cost"))
            ->pluck('account_id');

        return $report->toArray();
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
