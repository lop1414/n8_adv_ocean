<?php

namespace App\Services\Ocean;

use App\Common\Enums\MaterialTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Tools\CustomException;
use App\Models\Ocean\OceanAccountImageModel;
use App\Models\Ocean\OceanImageModel;
use App\Models\Ocean\OceanMaterialModel;

class OceanImageService extends OceanService
{
    /**
     * OceanImageService constructor.
     * @param string $appId
     */
    public function __construct($appId = ''){
        parent::__construct($appId);
    }

    /**
     * @param $accountId
     * @param $signature
     * @param $file
     * @param string $filename
     * @return mixed
     * @throws CustomException
     * 上传图片
     */
    public function uploadImage($accountId, $signature, $file, $filename = ''){
        $this->setAccessToken();

        return $this->sdk->uploadImage($accountId, $signature, $file, $filename);
    }


    /**
     * @param $accountIds
     * @param $accessToken
     * @param $filtering
     * @param $page
     * @param $pageSize
     * @param array $param
     * @return mixed|void
     * sdk并发获取列表
     */
    public function sdkMultiGetList($accountIds, $accessToken, $filtering, $page, $pageSize, $param = []){
        return $this->sdk->multiGetImageList($accountIds, $accessToken, $filtering, $page, $pageSize, $param);
    }

    /**
     * @param array $option
     * @return bool
     * @throws CustomException
     * 同步
     */
    public function sync($option = []){
        ini_set('memory_limit', '2048M');

        $accountIds = [];
        // 账户id过滤
        if(!empty($option['account_ids'])){
            $accountIds = $option['account_ids'];
        }

        // 并发分片大小
        if(!empty($option['multi_chunk_size'])){
            $multiChunkSize = min(intval($option['multi_chunk_size']), 8);
            $this->sdk->setMultiChunkSize($multiChunkSize);
        }

        $filtering = [];
        if(!empty($option['date'])){
            $filtering['start_time'] = Functions::getDate($option['date']);
            $filtering['end_time'] = Functions::getDate($option['date']);
        }

        if(!empty($option['ids'])){
            $filtering['image_ids'] = $option['ids'];
        }

        $accountGroup = $this->getSubAccountGroup($accountIds);

        $t = microtime(1);

        $pageSize = 100;
        foreach($accountGroup as $g){
            $images = $this->multiGetPageList($g, $filtering, $pageSize);
            Functions::consoleDump('count:'. count($images));

            // 保存
            foreach($images as $image) {
                $this->save($image);
            }
        }

        $t = microtime(1) - $t;
        var_dump($t);

        return true;
    }

    /**
     * @param $image
     * @return mixed
     * @throws CustomException
     * 保存
     */
    public function save($image){
        $oceanImageModel = new OceanImageModel();
        $oceanImage = $oceanImageModel->where('id', $image['id'])->first();

        if(empty($oceanImage)){
            $oceanImage = new OceanImageModel();
        }

        $oceanImage->id = $image['id'];
        $oceanImage->size = $image['size'] ?? 0;
        $oceanImage->width = $image['width'];
        $oceanImage->height = $image['height'];
        $oceanImage->format = $image['format'] ?? '';
        $oceanImage->signature = $image['signature'];
        $oceanImage->url = $image['url'];
        $oceanImage->material_id = $image['material_id'];
        $oceanImage->create_time = $image['create_time'];
        $oceanImage->filename = $image['filename'] ?? '';

        $ret = $oceanImage->save();

        if($ret){
            // 图片-账户关联
            $oceanAccountImageModel = new OceanAccountImageModel();
            $oceanAccountImage = $oceanAccountImageModel->where('account_id', $image['advertiser_id'])
                ->where('image_id', $image['id'])
                ->first();

            if(empty($oceanAccountImage)){
                $oceanAccountImage = new OceanAccountImageModel();
                $oceanAccountImage->account_id = $image['advertiser_id'];
                $oceanAccountImage->image_id = $image['id'];
                $oceanAccountImage->save();
            }

            // 图片-素材关联
            $oceanMaterial = OceanMaterialModel::find($image['material_id']);
            if(empty($oceanMaterial)){
                $oceanMaterialService = new OceanMaterialService();
                $oceanMaterialService->create([
                    'id' => $image['material_id'],
                    'material_type' => MaterialTypeEnums::IMAGE,
                    'file_id' => $image['id']
                ]);
            }
        }

        return $ret;
    }
}
