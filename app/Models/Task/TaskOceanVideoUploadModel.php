<?php

namespace App\Models\Task;

use App\Common\Models\SubTaskModel;

class TaskOceanVideoUploadModel extends TaskOceanModel
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
