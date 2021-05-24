<?php

namespace App\Enums;

use App\Common\Enums\SystemAliasEnum;
use App\Models\Task\TaskOceanAdCreativeCreateModel;
use App\Models\Task\TaskOceanAdUpdateModel;
use App\Models\Task\TaskOceanImageUploadModel;
use App\Models\Task\TaskOceanSyncModel;
use App\Models\Task\TaskOceanVideoUploadModel;

class TaskTypeEnum
{
    const OCEAN_VIDEO_UPLOAD = 'OCEAN_VIDEO_UPLOAD';
    const OCEAN_IMAGE_UPLOAD = 'OCEAN_IMAGE_UPLOAD';
    const OCEAN_SYNC = 'OCEAN_SYNC';
    const OCEAN_AD_CREATIVE_CREATE = 'OCEAN_AD_CREATIVE_CREATE';
    const OCEAN_AD_UPDATE = 'OCEAN_AD_UPDATE';

    /**
     * @var string
     * 名称
     */
    static public $name = '任务类型';

    /**
     * @var array
     * 列表
     */
    static public $list = [
        [
            'id' => self::OCEAN_VIDEO_UPLOAD,
            'name' => '巨量视频上传',
            'sub_model_class' => TaskOceanVideoUploadModel::class,
            'system_alias' => SystemAliasEnum::ADV_OCEAN,
        ],
        [
            'id' => self::OCEAN_IMAGE_UPLOAD,
            'name' => '巨量图片上传',
            'sub_model_class' => TaskOceanImageUploadModel::class,
            'system_alias' => SystemAliasEnum::ADV_OCEAN,
        ],
        [
            'id' => self::OCEAN_SYNC,
            'name' => '巨量同步',
            'sub_model_class' => TaskOceanSyncModel::class,
            'system_alias' => SystemAliasEnum::ADV_OCEAN,
        ],
        [
            'id' => self::OCEAN_AD_CREATIVE_CREATE,
            'name' => '巨量计划创意创建',
            'sub_model_class' => TaskOceanAdCreativeCreateModel::class,
            'system_alias' => SystemAliasEnum::ADV_OCEAN,
        ],
        [
            'id' => self::OCEAN_AD_UPDATE,
            'name' => '巨量计划批量更新',
            'sub_model_class' => TaskOceanAdUpdateModel::class,
            'system_alias' => SystemAliasEnum::ADV_OCEAN,
        ],
    ];
}