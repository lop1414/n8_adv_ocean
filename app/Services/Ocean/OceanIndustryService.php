<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Enums\Ocean\OceanIndustryTypeEnum;
use App\Enums\Ocean\OceanRegionLevelEnum;
use App\Enums\Ocean\OceanRegionTypeEnum;
use App\Models\Ocean\OceanIndustryModel;
use App\Models\Ocean\OceanRegionModel;

class OceanIndustryService extends OceanService
{
    /**
     * OceanIndustryService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param string $industryType
     * @param int $level
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 获取行业列表
     */
    public function getIndustryList($industryType = '', $level = 0){
        $this->setAccessToken();

        if(!empty($industryType)){
            Functions::hasEnum(OceanIndustryTypeEnum::class, $industryType);
        }

        return $this->sdk->getIndustryList($industryType, $level);
    }

    /**
     * @return bool
     * @throws \App\Common\Tools\CustomException
     * 同步
     */
    public function syncIndustry(){
        $oceanAccount = $this->getValidAccount();

        $this->sdk->setAppId($oceanAccount->app_id);
        $this->setAccountId($oceanAccount->account_id);

        $industrys = $this->getIndustryList(OceanIndustryTypeEnum::ADVERTISER);
        foreach($industrys as $industry){
            // 父级id
            $parentId = 0;
            if($industry['level'] == 1){
                $parentId = 0;
            }elseif($industry['level'] == 2){
                $parentId = $industry['first_industry_id'];
            }elseif($industry['level'] == 3){
                $parentId = $industry['second_industry_id'];
            }

            // 保存
            $data = [
                'id' => $industry['industry_id'],
                'name' => $industry['industry_name'],
                'level' => $industry['level'],
                'type' => OceanIndustryTypeEnum::ADVERTISER,
                'parent_id' => $parentId,
            ];
            $this->saveIndustry($data);
        }

        return true;
    }

    /**
     * @param $industry
     * @throws \App\Common\Tools\CustomException
     * 保存
     */
    public function saveIndustry($industry){
        Functions::saveChange(OceanIndustryModel::class, ['id', $industry['id']], $industry);
    }
}
