<?php

namespace App\Services\Ocean;

use App\Models\Ocean\OceanCityModel;

class OceanCityService extends OceanService
{
    /**
     * OceanCityService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @return array
     * 获取城市列表
     */
    public function getCityList(){
        return $this->sdk->getCityList();
    }

    /**
     * @param array $option
     * @return bool
     * 同步
     */
    public function syncCity($option = []){
        $citys = $this->getCityList();

        $i = 1;
        $total = count($citys);
        foreach($citys as $city){
            $this->saveCity($city);

            if(app()->runningInConsole()){
                echo "\rsaving data ({$i}/{$total})";
            }

            $i++;
        }

        return true;
    }

    /**
     * @param $city
     * @return bool
     * 保存
     */
    public function saveCity($city){
        $oceanCityModel = new OceanCityModel();
        $oceanCity = $oceanCityModel->where('id', $city['id'])->first();

        if(empty($oceanCity)){
            $oceanCity = new OceanCityModel();
        }

        $oceanCity->id = $city['id'];
        $oceanCity->name = $city['name'];
        $oceanCity->parent_id = $city['parent_id'];
        $oceanCity->level = $city['level'];
        $ret = $oceanCity->save();

        return $ret;
    }
}
