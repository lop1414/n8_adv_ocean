<?php

namespace App\Services\Ocean;

use App\Common\Enums\MaterialTypeEnums;
use App\Common\Tools\CustomException;
use App\Models\Material\VideoModel;
use App\Models\Ocean\OceanAccountVideoModel;
use App\Models\Ocean\OceanCompanyAccountModel;
use App\Models\Ocean\OceanMaterialPreAuditModel;
use App\Models\Ocean\OceanVideoModel;
use App\Services\Task\TaskOceanVideoUploadService;

class OceanMaterialPreAuditService extends OceanService
{
    /**
     * constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 执行
     */
    public function run($option = []){
        $timeRange = [
            $option['date'] .' 00:00:00',
            $option['date'] .' 23:59:59',
        ];

        $videoModel = new VideoModel();
        $videos = $videoModel->whereBetween('created_at', $timeRange)->get();

        $oceanCompanyAccountModel = new OceanCompanyAccountModel();
        $oceanCompanyAccounts = $oceanCompanyAccountModel->enable()->get();

        foreach($oceanCompanyAccounts as $oceanCompanyAccount){
            foreach($videos as $video){
                $oceanMaterialPreAuditModel = new OceanMaterialPreAuditModel();
                $oceanMaterialPreAudit = $oceanMaterialPreAuditModel->where('material_type', MaterialTypeEnums::VIDEO)
                    ->where('n8_material_id', $video->id)
                    ->where('company', $oceanCompanyAccount->company)
                    ->first();

                if(!empty($oceanMaterialPreAudit)){
                    continue;
                }

                $oceanVideoId = null;

                $oceanVideoModel = new OceanVideoModel();
                $oceanVideo = $oceanVideoModel->where('signature', $video->signature)->first();

                if(!empty($oceanVideo)){
                    $oceanAccountVideoModel = new OceanAccountVideoModel();
                    $oceanAccountVideo = $oceanAccountVideoModel->where('account_id', $oceanCompanyAccount->account_id)
                        ->where('video_id', $oceanVideo->id)
                        ->first();

                    if(!empty($oceanAccountVideo)){
                        $oceanVideoId = $oceanVideo->id;
                    }
                }

                $down = 0;
                if(empty($oceanVideoId)){
                    // 下载
                    $down = 1;
                    $taskOceanVideoUploadService = new TaskOceanVideoUploadService();
                    $file = $taskOceanVideoUploadService->download($video->path);

                    // 上传
                    $oceanVideoService = new OceanVideoService($oceanCompanyAccount->app_id);
                    $oceanVideoService->setAccountId($oceanCompanyAccount->account_id);
                    $oceanVideoService->uploadVideo($oceanCompanyAccount->account_id, $file['signature'], $file['curl_file'], $video->name);
                }else{
                    // 发送预审
                    $this->setAppId($oceanCompanyAccount->app_id);
                    $this->setAccountId($oceanCompanyAccount->account_id);
                    $sendPreAuditResult = $this->sendPreAudit($oceanCompanyAccount->account_id, 'VIDEO', $oceanVideoId);

                    // 获取预审
                    do{
                        $getPreAuditResult = $this->getPreAudit($oceanCompanyAccount->account_id, $sendPreAuditResult['pre_audit_id']);
                        sleep(2);
                    }while($getPreAuditResult['status'] == 'AUDITING');

                    if($getPreAuditResult['status'] == 'AUDIT_FAILED'){
                        var_dump($video->toArray());
                    }

                    // 预审成功
                    //if($getPreAuditResult['status'] != 'AUDIT_FAILED'){
                        // 记录
                        $oceanMaterialPreAuditModel = new OceanMaterialPreAuditModel();
                        $oceanMaterialPreAuditModel->material_type = MaterialTypeEnums::VIDEO;
                        $oceanMaterialPreAuditModel->n8_material_id = $video->id;
                        $oceanMaterialPreAuditModel->company = $oceanCompanyAccount->company;
                        $oceanMaterialPreAuditModel->account_id = $oceanCompanyAccount->account_id;
                        $oceanMaterialPreAuditModel->pre_audit_material_type = 'VIDEO';
                        $oceanMaterialPreAuditModel->pre_audit_content = $oceanVideoId;
                        $oceanMaterialPreAuditModel->pre_audit_id = $sendPreAuditResult['pre_audit_id'];
                        $oceanMaterialPreAuditModel->pre_audit_status = $getPreAuditResult['status'];
                        $oceanMaterialPreAuditModel->reject_reason = $getPreAuditResult['reject_reason'];
                        $oceanMaterialPreAuditModel->save();
                    //}
                }

                // 删除临时文件
                $down && unlink($file['path']);
            }
        }

        return true;
    }

    /**
     * @param $accountId
     * @param $materialType
     * @param $content
     * @return mixed
     * @throws CustomException
     * 发送预审
     */
    protected function sendPreAudit($accountId, $materialType, $content){
        $this->setAccessToken();

        $preAuditMaterials = [
            ['type' => $materialType, 'content' => $content]
        ];

        $result = $this->sdk->sendPreAudit($accountId, $preAuditMaterials);

        return current($result['list']);
    }

    /**
     * @param $accountId
     * @param $preAuditId
     * @return mixed
     * @throws CustomException
     * 获取预审
     */
    protected function getPreAudit($accountId, $preAuditId){
        $this->setAccessToken();

        $filter = [
            'pre_audit_ids' => [$preAuditId],
        ];

        $result = $this->sdk->getPreAudit($accountId, $filter);

        return current($result['list']);
    }
}