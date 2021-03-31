<?php

namespace App\Services\Ocean;

use App\Common\Tools\CustomException;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;
use App\Models\Ocean\OceanClickModel;

class OceanClickService extends OceanService
{
    /**
     * @var string
     * 队列key
     */
    protected $queueKey = QueueEnums::OCEAN_CLICK;

    /**
     * OceanClickService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $data
     * @return mixed
     * 加入队列
     */
    public function push($data){
        $queue = new CustomQueue($this->queueKey);
        return $queue->push($data);
    }

    /**
     * @return bool
     * 拉取队列
     */
    public function pull(){
        $queue = new CustomQueue($this->queueKey);

        $queue->pullWithRepush(function($data){
            $this->create($data);
        });

        return true;
    }

    /**
     * @param $data
     * @return bool
     * @throws CustomException
     * 创建
     */
    public function create($data){
        $muid = '';
        if(!empty($data['imei'])){
            $muid = trim($data['imei']);
        }elseif(!empty($data['idfa'])){
            $muid = trim($data['idfa']);
        }

        $clickAt = null;
        if(!empty($data['click_at'])){
            $clickAt = date('Y-m-d H:i:s', intval($data['click_at'] / 1000));
        }

        if(empty($clickAt)){
            throw new CustomException([
                'code' => 'CLICK_AT_IS_NULL',
                'message' => '点击时间不能为空',
                'log' => true,
                'data' => $data
            ]);
        }

        $oceanClickModel = new OceanClickModel();
        $oceanClickModel->campaign_id = $data['campaign_id'] ?? '';
        $oceanClickModel->ad_id = $data['ad_id'] ?? '';
        $oceanClickModel->creative_id = $data['creative_id'] ?? '';
        $oceanClickModel->request_id = $data['request_id'] ?? '';
        $oceanClickModel->product_id = $data['product_id'] ?? 0;
        $oceanClickModel->creative_type = $data['creative_type'] ?? '';
        $oceanClickModel->creative_site = $data['creative_site'] ?? '';
        $oceanClickModel->convert_id = $data['convert_id'] ?? '';
        $oceanClickModel->muid = $muid;
        $oceanClickModel->android_id = $data['android_id'] ?? '';
        $oceanClickModel->oaid = $data['oaid'] ?? '';
        $oceanClickModel->oaid_md5 = $data['oaid_md5'] ?? '';
        $oceanClickModel->os = $data['os'] ?? '';
        $oceanClickModel->ip = $data['ip'] ?? '';
        $oceanClickModel->ua = $data['ua'] ?? '';
        $oceanClickModel->click_at = $clickAt;
        $oceanClickModel->callback_param = $data['callback_param'] ?? '';
        $oceanClickModel->model = $data['model'] ?? '';
        $oceanClickModel->union_site = $data['union_site'] ?? '';
        $oceanClickModel->caid = $data['caid'] ?? '';
        $ret = $oceanClickModel->save();

        return $ret;
    }
}
