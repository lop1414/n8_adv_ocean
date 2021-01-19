ALTER TABLE `task_ocean_video_uploads`
ADD COLUMN `fail_data`  text NULL COMMENT '失败数据' AFTER `extends`;

