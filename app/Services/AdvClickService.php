<?php

namespace App\Services;

use App\Common\Enums\AdvClickSourceEnum;
use App\Common\Helpers\Functions;
use App\Common\Models\ClickModel;
use App\Common\Services\ClickService;
use App\Common\Tools\CustomException;
use App\Enums\QueueEnums;

class AdvClickService extends ClickService
{
    /**
     * constructor.
     */
    public function __construct(){
        parent::__construct(QueueEnums::CLICK);
    }

    /**
     * @param $data
     * @return mixed
     * @throws CustomException
     * 数据过滤
     */
    public function dataFilter($data){
        // 验证
        $this->validRule($data, [
            'click_source' => 'required',
        ]);
        Functions::hasEnum(AdvClickSourceEnum::class, $data['click_source']);

        $muid = '';
        if(!empty($data['imei'])){
            $muid = trim($data['imei']);
        }elseif(!empty($data['idfa'])){
            $muid = trim($data['idfa']);
        }elseif(!empty($data['muid'])){
            $muid = trim($data['muid']);
        }
        $data['muid'] = $muid;

        $clickAt = null;
        if(!empty($data['click_at'])){
            if(!is_numeric($data['click_at'])){
                throw new CustomException([
                    'code' => 'CLICK_AT_IS_ERROR',
                    'message' => '点击时间格式错误',
                    'log' => true,
                    'data' => $data,
                ]);
            }

            $clickAt = date('Y-m-d H:i:s', intval($data['click_at'] / 1000));
            if(!Functions::timeCheck($clickAt)){
                throw new CustomException([
                    'code' => 'CLICK_AT_IS_ERROR',
                    'message' => '点击时间格式错误',
                    'log' => true,
                    'data' => $data,
                ]);
            }
        }

        if(empty($clickAt)){
            throw new CustomException([
                'code' => 'CLICK_AT_IS_NULL',
                'message' => '点击时间不能为空',
                'log' => true,
                'data' => $data
            ]);
        }

        $data['click_at'] = $clickAt;

        return $data;
    }

    /**
     * @param $data
     * @return bool|void
     * 创建
     */
    protected function create($data){
        $clickModel = new ClickModel();

        $clickModel->click_source = $data['click_source'] ?? '';
        $clickModel->campaign_id = $data['campaign_id'] ?? '';
        $clickModel->ad_id = $data['ad_id'] ?? '';
        $clickModel->creative_id = $data['creative_id'] ?? '';
        $clickModel->request_id = $data['request_id'] ?? '';
        $clickModel->channel_id = $data['channel_id'] ?? 0;
        $clickModel->creative_type = $data['creative_type'] ?? '';
        $clickModel->creative_site = $data['creative_site'] ?? '';
        $clickModel->convert_id = $data['convert_id'] ?? '';
        $clickModel->muid = $data['muid'] ?? '';
        $clickModel->android_id = $data['android_id'] ?? '';
        $clickModel->oaid = $data['oaid'] ?? '';
        $clickModel->oaid_md5 = $data['oaid_md5'] ?? '';
        $clickModel->os = $data['os'] ?? '';
        $clickModel->ip = $data['ip'] ?? '';
        $clickModel->ua = $data['ua'] ?? '';
        $clickModel->click_at = $data['click_at'] ?? null;
        $clickModel->callback_param = $data['callback_param'] ?? '';
        $clickModel->model = $data['model'] ?? '';
        $clickModel->union_site = $data['union_site'] ?? '';
        $clickModel->caid = $data['caid'] ?? '';
        $clickModel->link = $data['link'] ?? '';
        $clickModel->extends = $data['extends'] ?? [];
        $ret = $clickModel->save();

        return $ret;
    }
}
