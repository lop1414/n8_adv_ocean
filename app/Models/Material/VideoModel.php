<?php

namespace App\Models\Material;

use App\Common\Enums\SystemAliasEnum;
use App\Common\Models\BaseModel;

class VideoModel extends BaseModel
{
    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'n8_material.videos';

    /**
     * @param $value
     * @return string
     * 属性访问器
     */
    public function getPathAttribute($value)
    {
        $systemConfig = config('common.system_api');

        return $systemConfig[SystemAliasEnum::MATERIAL]['storage_url'] .'/'. $value;
    }

    /**
     * @param $value
     * @return string
     * 属性访问器
     */
    public function getFramePathAttribute($value)
    {
        $systemConfig = config('common.system_api');

        return $systemConfig[SystemAliasEnum::MATERIAL]['storage_url'] .'/'. $value;
    }
}
