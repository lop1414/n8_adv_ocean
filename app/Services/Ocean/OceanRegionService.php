<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Enums\Ocean\OceanRegionLevelEnum;
use App\Enums\Ocean\OceanRegionTypeEnum;
use App\Models\Ocean\OceanRegionModel;

class OceanRegionService extends OceanService
{
    /**
     * OceanRegionService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $regionType
     * @param string $regionLevel
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 获取列表
     */
    public function getRegionList($regionType, $regionLevel = ''){
        $this->setAccessToken();

        Functions::hasEnum(OceanRegionTypeEnum::class, $regionType);

        if(!empty($regionLevel)){
            Functions::hasEnum(OceanRegionLevelEnum::class, $regionLevel);
        }

        return $this->sdk->getRegionList($regionType, $regionLevel);
    }

    /**
     * @return bool
     * @throws \App\Common\Tools\CustomException
     * 同步
     */
    public function syncRegion(){
        $oceanAccount = $this->getValidAccount();

        $this->sdk->setAppId($oceanAccount->app_id);
        $this->setAccountId($oceanAccount->account_id);
        $regions = $this->getRegionList(OceanRegionTypeEnum::BUSINESS_DISTRICT);

        foreach($regions as $region){
            $this->saveRegion($region);
        }

        return true;
    }

    /**
     * @param $region
     * @return bool
     * 保存
     */
    public function saveRegion($region){
        $oceanRegionModel = new OceanRegionModel();
        $oceanRegion = $oceanRegionModel->where('id', $region['id'])->first();

        if(empty($oceanRegion)){
            $oceanRegion = new OceanRegionModel();
        }

        $oceanRegion->id = $region['id'];
        $oceanRegion->name = $region['name'];
        $oceanRegion->parent_id = $region['parent_id'];
        $oceanRegion->region_level = $region['region_level'];
        $ret = $oceanRegion->save();

        if(!$ret){
            var_dump($region);
            dd('插入失败');
        }

        return $ret;
    }
}
