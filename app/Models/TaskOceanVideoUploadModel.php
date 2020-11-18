<?php

namespace App\Models;

use App\Common\Models\BaseModel;

class TaskOceanVideoUploadModel extends BaseModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'task_ocean_video_uploads';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';
}
