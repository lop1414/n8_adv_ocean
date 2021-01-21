<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Services\Task\TaskOceanAdCreativeCreateService;

class OceanAdCreativeCreateService extends OceanService
{
    /**
     * OceanAdConvertService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $date
     * @return bool
     * @throws CustomException
     * 时间检查
     */
    private function timeCheck($date){
        if(!Functions::timeCheck($date)){
            throw new CustomException([
                'code' => 'DATE_ERROR',
                'message' => '日期格式错误',
                'data' => [
                    'date' => $date,
                ],
            ]);
        }

        return true;
    }

    /**
     * @param $items
     * @return array
     * @throws CustomException
     * 数据格式化
     */
    private function itemsFormat($items){
        $list = [];
        foreach($items as $item){
            $accountId = $item['account_id'] ?? '';
            $ad = $item['ad'] ?? [];
            $creative = $item['creative'] ?? [];
            if(empty($accountId) || empty($ad) || empty($creative)){
                throw new CustomException([
                    'code' => 'PARAM_ERROR',
                    'message' => '账户id、计划、创意参数均不能为空',
                    'data' => [
                        'item' => $item,
                    ],
                ]);
            }

            $account = $this->getAccount($accountId);
            if(empty($account)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_ACCOUNT',
                    'message' => "找不到账户{$accountId}",
                ]);
            }

            $list[] = [
                'app_id' => $account->app_id,
                'account_id' => $accountId,
                'data' => [
                    'ad' => $ad,
                    'creative' => $creative,
                ],
            ];
        }

        return $list;
    }

    /**
     * @param $items
     * @param $rule
     * @param $ruleOption
     * @return bool
     * @throws CustomException
     * 构建任务
     */
    public function buildTask($items, $rule, $ruleOption){
        $rule = strtolower($rule);

        $adminUserInfo = Functions::getGlobalData('admin_user_info');

        $taskName = "批量创建计划创意";
        if($rule == 'now'){
            // 立即
            $datetime = date('Y-m-d H:i:s');
            $subs = $this->buildTaskTimming($items, ['start_at' => $datetime], $adminUserInfo['admin_user']['id']);
        }elseif($rule == 'timming'){
            // 定时
            $taskName = "定时创建计划创意";

            $subs = $this->buildTaskTimming($items, $ruleOption, $adminUserInfo['admin_user']['id']);
        }elseif($rule == 'chunk'){
            // 分批
            $taskName = "分批创建计划创意";

            $subs = $this->buildTaskChunk($items, $ruleOption, $adminUserInfo['admin_user']['id']);
        }elseif($rule == 'cycle'){
            $taskName = "重复创建计划创意";

            // 周期
            $subs = $this->buildTaskCycle($items, $ruleOption, $adminUserInfo['admin_user']['id']);
        }else{
            throw new CustomException([
                'code' => 'UNKNOWN_RULE',
                'message' => '未知的规则',
                'data' => [
                    'rule' => $rule,
                    'rule_option' => $ruleOption,
                ],
            ]);
        }

        $task = [
            'name' => $taskName,
            'admin_id' => $adminUserInfo['admin_user']['id'],
        ];

        $taskOceanAdCreativeCreateService = new TaskOceanAdCreativeCreateService();
        $taskOceanAdCreativeCreateService->create($task, $subs);

        return $taskOceanAdCreativeCreateService->taskId;
    }

    /**
     * @param $items
     * @param $ruleOption
     * @param $adminId
     * @return array
     * @throws CustomException
     * 构建定时任务
     */
    private function buildTaskTimming($items, $ruleOption, $adminId){
        if(empty($ruleOption['start_at'])){
            throw new CustomException([
                'code' => 'START_AT_IS_EMPTY',
                'message' => '开始时间不能为空',
            ]);
        }

        $this->timeCheck($ruleOption['start_at']);

        $subs = [];
        foreach($items as $item){
            $subs[] = [
                'app_id' => $item['app_id'],
                'account_id' => $item['account_id'],
                'data' => [
                    'ad' => $item['data']['ad'],
                    'creative' => $item['data']['creative'],
                ],
                'start_at' => $ruleOption['start_at'],
                'admin_id' => $adminId,
            ];
        }

        return $subs;
    }

    /**
     * @param $items
     * @param $ruleOption
     * @param $adminId
     * @return array
     * @throws CustomException
     * 构建分批任务
     */
    private function buildTaskChunk($items, $ruleOption, $adminId){
        $chunkMinute = isset($ruleOption['chunk_minute']) ? intval($ruleOption['chunk_minute']) : 0;
        $chunkSize = isset($ruleOption['chunk_size']) ? intval($ruleOption['chunk_size']) : 0;

        if($chunkMinute < 1){
            throw new CustomException([
                'code' => 'CHUNK_MINUTE_HAVE_TO_MORE_THAN_1',
                'message' => '分批周期至少为1分钟',
            ]);
        }

        if($chunkSize < 1){
            throw new CustomException([
                'code' => 'CHUNK_SIZE_HAVE_TO_MORE_THAN_1',
                'message' => '分批计划个数至少为1个',
            ]);
        }

        $time = time();

        $subs = [];
        $chunks = array_chunk($items, $chunkSize);

        $i = 1;
        foreach($chunks as $chunk){
            foreach($chunk as $item){
                $startTime = $time + ($chunkMinute * 60 * $i);
                $subs[] = [
                    'app_id' => $item['app_id'],
                    'account_id' => $item['account_id'],
                    'data' => [
                        'ad' => $item['data']['ad'],
                        'creative' => $item['data']['creative'],
                    ],
                    'start_at' => date('Y-m-d H:i:s', $startTime),
                    'admin_id' => $adminId,
                ];
            }
            $i++;
        }

        return $subs;
    }

    /**
     * @param $items
     * @param $ruleOption
     * @param $adminId
     * @return array
     * @throws CustomException
     * 构建周期任务
     */
    private function buildTaskCycle($items, $ruleOption, $adminId){
        $cycleMinute = isset($ruleOption['cycle_minute']) ? intval($ruleOption['cycle_minute']) : 0;
        $cycleTimes = isset($ruleOption['cycle_times']) ? intval($ruleOption['cycle_times']) : 0;

        if($cycleMinute < 1){
            throw new CustomException([
                'code' => 'CYCLE_MINUTE_HAVE_TO_MORE_THAN_1',
                'message' => '重复周期至少为1分钟',
            ]);
        }

        if($cycleTimes < 1){
            throw new CustomException([
                'code' => 'CYCLE_TIMES_HAVE_TO_MORE_THAN_1',
                'message' => '重复次数至少为1次',
            ]);
        }

        $maxCreateTimes = 10;
        if($cycleTimes > $maxCreateTimes){
            throw new CustomException([
                'code' => "CYCLE_TIMES_HAVE_TO_LESS_THAN_{$maxCreateTimes}",
                'message' => "重复不能超过{$maxCreateTimes}次",
            ]);
        }

        $time = time();

        $subs = [];
        for($i = 1; $i <= $cycleTimes; $i++){
            foreach($items as $item){
                // 开始时间
                $startTime = $time + ($cycleMinute * 60 * $i);
                $startAt = date('Y-m-d H:i:s', $startTime);

                // 计划名称拼接时间
                $item['data']['ad']['name'] .= "_{$startAt}";

                $subs[] = [
                    'app_id' => $item['app_id'],
                    'account_id' => $item['account_id'],
                    'data' => [
                        'ad' => $item['data']['ad'],
                        'creative' => $item['data']['creative'],
                    ],
                    'start_at' => $startAt,
                    'admin_id' => $adminId,
                ];
            }
        }

        return $subs;
    }

    /**
     * @param $items
     * @param $rule
     * @param array $ruleOption
     * @return bool
     * @throws CustomException
     * 批量创建计划创意
     */
    public function batchCreateAdCreative($items, $rule, $ruleOption = []){

        // 格式化
        $items = $this->itemsFormat($items);

        // 构建任务
        $taskId = $this->buildTask($items, $rule, $ruleOption);

        return $taskId;
    }

    /**
     * @param $param
     * @return mixed
     * @throws CustomException
     * 创建计划
     */
    public function createAd($param){
        $ret = $this->forward('2/ad/create/', $param, 'POST');
        return $ret;
    }

    /**
     * @param $param
     * @return mixed
     * @throws CustomException
     * 创建创意
     */
    public function createCreative($param){
        $ret = $this->forward('2/creative/create_v2/', $param, 'POST');
        return $ret;
    }

    /**
     * @param $item
     * @return array
     * @throws CustomException
     */
    public function createAdCreative($item){
        // 设置账户
        $this->setAppId($item['app_id']);
        $this->setAccountId($item['account_id']);

        // 创建计划
        $ret = $this->createAd($item['data']['ad']);

        if(empty($ret['ad_id'])){
            throw new CustomException([
                'code' => 'CREATE_AD_FAIL',
                'message' => '创建广告计划失败',
                'data' => [
                    'item' => $item,
                ],
                'log' => true,
            ]);
        }

        // 计划id
        $adId = $ret['ad_id'];

        // 创建创意
        $creative = array_merge($item['data']['creative'], ['ad_id' => $adId]);
        $this->createCreative($creative);

        return [
            'ad_id' => $adId
        ];
    }
}
