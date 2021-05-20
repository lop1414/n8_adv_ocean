<?php

namespace App\Models\Task;


class TaskOceanAdUpdateModel extends TaskOceanModel
{
    /**
     * 关联到模型的数据表
     *
     * @var string
     */
    protected $table = 'task_ocean_ad_updates';

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
    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     * 属性修饰器
     */
    public function setDataAttribute($value)
    {
        $this->attributes['data'] = json_encode($value);
    }
}
