<?php

namespace App\Services\Ocean;

use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Common\Enums\MaterialTypeEnums;
use App\Models\Ocean\OceanCreativeModel;
use App\Models\Ocean\OceanImageModel;
use App\Models\Ocean\OceanMaterialCreativeModel;
use App\Models\Ocean\OceanVideoModel;
use Illuminate\Support\Facades\DB;

class OceanMaterialCreativeService extends OceanService
{
    /**
     * OceanMaterialService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option = []){
        $oceanCreativeModel = new OceanCreativeModel();
        if(!empty($option['date'])){
            $date = Functions::getDate($option['date']);
            $oceanCreativeModel = $oceanCreativeModel->whereBetween('creative_modify_time', ["{$date} 00:00:00", "{$date} 23:59:59"]);
        }
        $oceanCreatives = $oceanCreativeModel->get();

        foreach($oceanCreatives as $oceanCreative){
            if(!empty($oceanCreative->extends->video_id)){
                $materialType = MaterialTypeEnums::VIDEO;
                $fileId = $oceanCreative->extends->video_id;
            }elseif($oceanCreative->extends->image_ids){
                $materialType = MaterialTypeEnums::IMAGE;
                $fileId = current($oceanCreative->extends->image_ids);
            }else{
                continue;
            }

            $material = $this->getMaterial($materialType, $fileId);

            if(!empty($material['material_id'])){
                $this->save([
                    'material_id' => $material['material_id'] ?? '',
                    'creative_id' => $oceanCreative->id ?? '',
                    'material_type' => $materialType,
                    'n8_material_id' => $material['n8_material_id'] ?? 0,
                    'signature' => $material['signature'] ?? '',
                ]);
            }
        }

        return true;
    }

    /**
     * @param $materialType
     * @param $fileId
     * @return array|null
     * @throws CustomException
     * 获取素材
     */
    protected function getMaterial($materialType, $fileId){
        $material = null;
        if($materialType == MaterialTypeEnums::IMAGE){
            $oceanImage = OceanImageModel::find($fileId);
            if(empty($oceanImage)){
                return null;
            }

            $imageModel = new \App\Models\Material\ImageModel();
            $image = $imageModel->whereRaw("
                signature = '{$oceanImage->signature}'
            ")->first();

            $n8MaterialId = 0;
            if(!empty($image)){
                $n8MaterialId = $image->id;
            }

            $material = [
                'material_id' => $oceanImage->material_id,
                'n8_material_id' => $n8MaterialId,
                'signature' => $oceanImage->signature,
            ];
        }elseif($materialType == MaterialTypeEnums::VIDEO){
            $oceanVideo = OceanVideoModel::find($fileId);
            if(empty($oceanVideo)){
                return null;
            }

            $videoModel = new \App\Models\Material\VideoModel();
            $video = $videoModel->whereRaw("
                (signature = '{$oceanVideo->signature}' OR source_signature = '{$oceanVideo->signature}')
            ")->first();

            $n8MaterialId = 0;
            if(!empty($video)){
                $n8MaterialId = $video->id;
            }

            $material = [
                'material_id' => $oceanVideo->material_id,
                'n8_material_id' => $n8MaterialId,
                'signature' => $oceanVideo->signature,
            ];

        }else{
            throw new CustomException([
                'code' => 'UNKNOWN_MATERIAL_TYPE',
                'message' => '未知的素材类型',
            ]);
        }

        return $material;
    }

    /**
     * @param $item
     * @return bool
     * 保存
     */
    protected function save($item){
        $oceanMaterialCreativeModel = new OceanMaterialCreativeModel();
        $oceanMaterialCreative = $oceanMaterialCreativeModel->where('material_id', $item['material_id'])
            ->where('creative_id', $item['creative_id'])
            ->first();

        if(empty($oceanMaterialCreative)){
            $oceanMaterialCreative = new OceanMaterialCreativeModel();
        }

        $oceanMaterialCreative->material_id = $item['material_id'];
        $oceanMaterialCreative->creative_id = $item['creative_id'];
        $oceanMaterialCreative->material_type = $item['material_type'];
        $oceanMaterialCreative->n8_material_id = $item['n8_material_id'] ?? 0;
        $oceanMaterialCreative->signature = $item['signature'] ?? '';
        $ret = $oceanMaterialCreative->save();

        return $ret;
    }
}
