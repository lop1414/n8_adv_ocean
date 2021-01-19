ALTER TABLE `task_ocean_syncs`
ADD COLUMN `fail_data`  text NULL COMMENT '失败数据' AFTER `extends`;

