<?php

namespace App\Models\Task;

use App\Common\Models\BaseModel;

class TaskOceanImageUploadModel extends BaseModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'task_ocean_image_uploads';

    /**
     * 关联到模型数据表的主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @param $value
     * @return array
     * 属性访问器
     */
    public function getExtendsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setExtendsAttribute($value)
    {
        $this->attributes['extends'] = json_encode($value);
    }

    /**
     * @param $value
     * @return array
     * 属性访问器
     */
    public function getFailDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setFailDataAttribute($value)
    {
        $this->attributes['fail_data'] = json_encode($value);
    }
}
