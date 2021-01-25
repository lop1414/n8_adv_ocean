ALTER TABLE `task_ocean_image_uploads`
ADD COLUMN `extends`  text NULL COMMENT '扩展字段' AFTER `admin_id`,
ADD COLUMN `fail_data`  text NULL COMMENT '失败数据' AFTER `extends`;

