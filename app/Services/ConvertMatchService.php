<?php

namespace App\Services;

use App\Enums\Ocean\ConvertCallbackTimeEnum;
use App\Common\Enums\ConvertTypeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Enums\Ocean\OceanConvertCallbackStatusEnum;
use App\Models\Ocean\OceanAdExtendModel;
use App\Models\Ocean\OceanClickModel;
use App\Models\Ocean\OceanConvertCallbackModel;
use App\Services\Ocean\OceanConvertCallbackService;
use Jenssegers\Agent\Agent;

class ConvertMatchService extends BaseService
{
    const MATCH_BY_REQUEST_ID = 'request_id';
    const MATCH_BY_MUID = 'muid';
    const MATCH_BY_OAID = 'oaid';
    const MATCH_BY_OAID_MD5 = 'oaid_md5';
    const MATCH_BY_IP = 'ip';

    /**
     * 匹配规则
     */
    protected $matchBys = [
        self::MATCH_BY_REQUEST_ID,
        self::MATCH_BY_MUID,
        self::MATCH_BY_OAID,
        self::MATCH_BY_OAID_MD5,
        self::MATCH_BY_IP,
    ];

    protected $matchBy;

    /**
     * @param $converts
     * @return array
     * @throws \App\Common\Tools\CustomException
     * 匹配
     */
    public function match($converts){
        $data = [];
        foreach($converts as $convert){
            // 校验
            $this->validRule($convert, [
                'convert_type' => 'required',
                'convert_at' => 'required',
                'convert_times' => 'required|integer',
                'n8_union_user.created_at' => 'required',
                'n8_union_user.guid' => 'required',
                'n8_union_user.channel_id' => 'required',
            ]);
            Functions::hasEnum(ConvertTypeEnum::class, $convert['convert_type']);

            $click = $this->matchItem($convert);

            if(!empty($click)){
                $clickId = $click->id;

                $oceanConvertCallbackService = new OceanConvertCallbackService();
                $oceanConvertCallbackService->create([
                    'click_id' => $clickId,
                    'convert_type' => $convert['convert_type'],
                    'convert_id' => $convert['convert_id'],
                    'n8_union_guid' => $convert['n8_union_user']['guid'],
                    'n8_union_channel_id' => $convert['n8_union_user']['channel_id'],
                    'convert_at' => $convert['convert_at'],
                    'convert_callback_status' => $this->getConvertCallbackStatus($convert, $click),
                    'extends' => $convert,
                ]);
            }else{
                $clickId = 0;
            }

            $data[] = [
                'convert_type' => $convert['convert_type'],
                'convert_id' => $convert['convert_id'],
                'click_id' => $clickId,
            ];
        }
        return $data;
    }

    /**
     * @param $convert
     * @param $click
     * @return string
     * @throws CustomException
     * 获取转化回传状态
     */
    private function getConvertCallbackStatus($convert, $click){
        // 获取回传策略
        $strategy = $this->getConvertCallbackStrategy($click->ad_id, $convert['convert_type']);

        if(!empty($strategy)){
            // 转化回传时间
            if(!empty($strategy['time_range'])){
                $convertCallbackTime = $this->getConvertCallbackTime($strategy['time_range'], $convert['n8_union_user']['created_at']);
                if($convertCallbackTime < $convert['convert_at']){
                    // 转化时间超过回传时间
                    return OceanConvertCallbackStatusEnum::DOT_NEED_CALLBACK_BY_CALLBACK_TIME;
                }
            }

            // 转化回传率
            if(!empty($strategy['callback_rate'])){
                $callbackRate = $this->getConvertCallbackRate($click->ad_id, $convert['convert_type']);
                if($callbackRate >= $strategy['callback_rate']){
                    return OceanConvertCallbackStatusEnum::DOT_NEED_CALLBACK_BY_RATE;
                }
            }

            // 转化次数
            if(!empty($strategy['convert_times']) && $convert['convert_times'] > $strategy['convert_times']){
                return OceanConvertCallbackStatusEnum::DOT_NEED_CALLBACK_BY_CONVERT_TIMES;
            }
        }

        return OceanConvertCallbackStatusEnum::WAITING_CALLBACK;
    }

    /**
     * @param $adId
     * @param $convertType
     * @return array
     * 获取转化回传规则
     */
    private function getConvertCallbackStrategy($adId, $convertType){
        // 默认策略
        $strategy = [
            ConvertTypeEnum::PAY => [
                'time_range' => ConvertCallbackTimeEnum::HOUR_24,
                'times' => 1,
                'callback_rate' => 100,
            ],
        ];

        // 配置策略
        $adExtend = OceanAdExtendModel::find($adId);
        if(!empty($adExtend) && !empty($adExtend->convert_callback_strategy()->enable()->first())){
            $strategy = $adExtend->convert_callback_strategy['extends'];
        }

        $convertStrategy = $strategy[$convertType] ?? ['time_range' => ConvertCallbackTimeEnum::NEVER];

        return $convertStrategy;
    }

    /**
     * @param $adId
     * @param $convertType
     * @return float|int
     * 获取转化回传率
     */
    private function getConvertCallbackRate($adId, $convertType){
        $clickDatetime = date('Y-m-d H:i:s', strtotime("-15 days", time()));
        $convertDatetime = date('Y-m-d H:i:s', strtotime("-1 days", time()));

        $oceanConvertCallbackModel = new OceanConvertCallbackModel();
        $convertCallbacks = $oceanConvertCallbackModel->whereRaw("
            click_id IN (
                SELECT id FROM ocean_clicks
                    WHERE ad_id = '{$adId}' AND click_at > '{$clickDatetime}'
            ) AND convert_at > '{$convertDatetime}'
            AND convert_type IN ('{$convertType}')
        ")->get();


        $count = $total = 0;
        foreach($convertCallbacks as $convertCallback){
            if(in_array($convertCallback->convert_callback_status, [
                OceanConvertCallbackStatusEnum::WAITING_CALLBACK,
                OceanConvertCallbackStatusEnum::MACHINE_CALLBACK,
                OceanConvertCallbackStatusEnum::MANUAL_CALLBACK,
            ])){
                $count += 1;
            }

            $total += 1;
        }

        $callbackRate = ($count / ($total + 1)) * 100;

        return $callbackRate;
    }

    /**
     * @param $convert_callback_time
     * @param $createAt
     * @return false|string
     * 获取转化回传时间
     */
    private function getConvertCallbackTime($convert_callback_time, $createAt){
        $time = strtotime("+24 hours", strtotime($createAt));

        if($convert_callback_time == ConvertCallbackTimeEnum::NEVER){
            $time = strtotime('1970-01-01 00:00:00');
        }elseif($convert_callback_time == ConvertCallbackTimeEnum::TODAY){
            $time = strtotime('today', strtotime($createAt));
        }elseif(strpos($convert_callback_time, 'HOUR_') !== false){
            $hour = intval(str_replace('HOUR_', '', $convert_callback_time));
            $time = strtotime("+ {$hour} hours", strtotime($createAt));
        }

        $datetime = date('Y-m-d H:i:s', $time);

        return $datetime;
    }

    /**
     * @param $data
     * @return bool|null
     * @throws CustomException
     * 单个匹配
     */
    public function matchItem($data){
        $click = null;

        foreach($this->matchBys as $matchBy){
            $this->matchBy = $matchBy;

            $click = $this->matchBy($data);
            if(!empty($click)){
                break;
            }
        }

        return $click;
    }

    /**
     * @return int
     * 获取回溯期（天）
     */
    private function getLookback(){
        if($this->matchBy == self::MATCH_BY_IP){
            // 按ip匹配, 回溯期为1天
            $lookback = 1;
        }else{
            $lookback = 15;
        }

        return $lookback;
    }

    /**
     * @param $data
     * @return bool|null
     * @throws CustomException
     * 按规则匹配
     */
    private function matchBy($data){
        if(empty($data[$this->matchBy])){
            return false;
        }

        $lookback = $this->getLookback();

        if(!Functions::timeCheck($data['convert_at'])){
            throw new CustomException([
                'code' => 'CONVERT_AT_ERROR',
                'message' => '转化时间格式错误',
            ]);
        }

        // 回溯期
        $lookbackTime = strtotime("-{$lookback} days", strtotime($data['convert_at']));
        $lookbackDateTime = date('Y-m-d H:i:s', $lookbackTime);

        $productId = $data['product_id'] ?? 0;

        $oceanClickModel = new OceanClickModel();
        $builder = $oceanClickModel->whereBetween('click_at', [$lookbackDateTime, $data['convert_at']])
            ->where('product_id', $productId);

        // 规则
        if(in_array($this->matchBy, [self::MATCH_BY_MUID, self::MATCH_BY_OAID, self::MATCH_BY_OAID_MD5])){
            $flag = trim($data[$this->matchBy]);
            $flagMd5 = md5($flag);
            $flags = [$flag, $flagMd5];
        }else{
            $flag = trim($data[$this->matchBy]);
            $flags = [$flag];
        }

        // 查询构造器
        $flagStr = implode("','", $flags);
        $builder->whereRaw("`{$this->matchBy}` IN ('{$flagStr}')")->orderBy('id', 'desc');

        if($this->matchBy == self::MATCH_BY_IP){
            // 按ip匹配
            if(empty($data['ua'])){
                return false;
            }

            // 联运ua
            $uinonUserAgent = new Agent();
            $uinonUserAgent->setUserAgent($data['ua']);

            $items = $builder->get();

            $click = null;
            foreach($items as $item){
                // 点击ua
                $clickUserAgent = new Agent();
                $clickUserAgent->setUserAgent($item->ua);

                if($this->isSameUserAgent($uinonUserAgent, $clickUserAgent)){
                    $click = $item;
                    break;
                }
            }
        }else{
            $click = $builder->first();
        }

        return $click;
    }

    /**
     * @param $userAgent1
     * @param $userAgent2
     * @return bool
     * 是否相同 user_agent
     */
    private function isSameUserAgent($userAgent1, $userAgent2){
        // 设备校验
        $deviceChcek = $userAgent1->device() == $userAgent2->device();

        // 平台校验
        $platformChcek = $userAgent1->platform() == $userAgent2->platform();

        // 平台版本校验
        $platformVersionCheck = $userAgent1->version($userAgent1->platform()) == $userAgent2->version($userAgent2->platform());

        return $deviceChcek && $platformChcek && $platformVersionCheck;
    }
}
