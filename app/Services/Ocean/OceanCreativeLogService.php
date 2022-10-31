<?php

namespace App\Services\Ocean;

use App\Common\Enums\NoticeStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\NoticeApiService;
use App\Enums\Ocean\OceanAdStatusEnum;
use App\Enums\Ocean\OceanCreativeStatusEnum;
use App\Models\Ocean\OceanAccountModel;
use App\Models\Ocean\OceanAdModel;
use App\Models\Ocean\OceanCreativeLogModel;

class OceanCreativeLogService extends OceanService
{
    /**
     * constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $item
     * @return bool
     * 创建
     */
    public function create($item){
        $oceanCreativeLogModel = new OceanCreativeLogModel();
        $oceanCreativeLogModel->account_id = $item['account_id'];
        $oceanCreativeLogModel->ad_id = $item['ad_id'];
        $oceanCreativeLogModel->creative_id = $item['creative_id'];
        $oceanCreativeLogModel->before_status = $item['before_status'];
        $oceanCreativeLogModel->after_status = $item['after_status'];
        $oceanCreativeLogModel->notice_status = NoticeStatusEnum::WAITING;
        return $oceanCreativeLogModel->save();
    }

    /**
     * @throws \App\Common\Tools\CustomException
     * 通知
     */
    public function notice(){
        $timeRange = [
            date('Y-m-d H:i:s', time() - 86400),
            date('Y-m-d H:i:s', time() - 180),
        ];
        $oceanCreativeLogModel = new OceanCreativeLogModel();
        $oceanCreativeLogs = $oceanCreativeLogModel->where('notice_status', NoticeStatusEnum::WAITING)
            ->whereBetween('created_at', $timeRange)
            ->get();

        $group = [];
        foreach($oceanCreativeLogs as $oceanCreativeLog){
            // 通知创意状态
            $noticeCreativeStatus = [
                OceanCreativeStatusEnum::CREATIVE_STATUS_CAMPAIGN_EXCEED,
                OceanCreativeStatusEnum::CREATIVE_STATUS_BALANCE_EXCEED,
                OceanCreativeStatusEnum::CREATIVE_STATUS_BUDGET_EXCEED,
                OceanCreativeStatusEnum::CREATIVE_STATUS_ADVERTISER_BUDGET_EXCEED,
                OceanCreativeStatusEnum::CREATIVE_STATUS_AUDIT_DENY,
                OceanCreativeStatusEnum::CREATIVE_STATUS_AD_AUDIT_DENY,
                OceanCreativeStatusEnum::CREATIVE_STATUS_DELETE,
            ];

            $oceanAdModel = new OceanAdModel();
            $oceanAd = $oceanAdModel->find($oceanCreativeLog->ad_id);
            if(!empty($oceanAd) && in_array($oceanAd->status, [
                    OceanAdStatusEnum::AD_STATUS_DELETE,
                    OceanAdStatusEnum::AD_STATUS_DISABLE,
                    OceanAdStatusEnum::AD_STATUS_CAMPAIGN_DISABLE,
                ])
            ){
                $oceanCreativeLog->notice_status = NoticeStatusEnum::DONT;
            }else{
                if(in_array($oceanCreativeLog->after_status, $noticeCreativeStatus)){
                    $key = $oceanCreativeLog->ad_id .'|###|'. $oceanCreativeLog->after_status;
                    $group[$key][] = $oceanCreativeLog;
                    $oceanCreativeLog->notice_status = NoticeStatusEnum::SUCCESS;
                }else{
                    $oceanCreativeLog->notice_status = NoticeStatusEnum::DONT;
                }
            }

            $oceanCreativeLog->save();
        }

        foreach($group as $key => $item){
            list($adId, $status) = explode("|###|", $key);
            $statusName = Functions::getEnumMapName(OceanCreativeStatusEnum::class, $status);
            $ad = OceanAdModel::find($adId);
            $count = count($item);

            if(empty($ad)){
                continue;
            }

            $oceanAccountModel = new OceanAccountModel();
            $account = $oceanAccountModel->where('account_id', $ad->account_id)->first();

            if(empty($account) || empty($account->admin_id)){
                continue;
            }

            $title = "巨量". $statusName;
            $content = implode("<br>", [
                "账户id: {$account->account_id}",
                "账户名称: {$account->name}",
                "计划id: {$adId}",
                "计划名称: {$ad->name}",
                "影响创意数: {$count}",
            ]);

            $adminId = $account->admin_id;

            $noticeApiService = new NoticeApiService();
            $noticeApiService->apiSendFeishuMessage($title, $content, $adminId);
        }

        return true;
    }
}
