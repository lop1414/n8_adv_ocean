<?php

namespace App\Sdks\OceanEngine\Traits;

trait City
{
    /**
     * @return array
     * 获取城市列表
     */
    public function getCityList(){
        $cityFile = $this->getSdkPath('Data/city.json');
        $json = file_get_contents($cityFile);
        $data = json_decode($json, true);

        $citys = [];
        foreach($data as $v){
            $citys[] = [
                'id' => $v['id'],
                'parent_id' => $v['parent'],
                'name' => $v['name'],
                'level' => $v['level'],
            ];
        }

        return $citys;
    }
}
