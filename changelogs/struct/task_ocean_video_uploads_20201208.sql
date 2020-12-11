ALTER TABLE `task_ocean_video_uploads`
ADD COLUMN `n8_material_video_id`  int NOT NULL DEFAULT 0 COMMENT 'n8素材系统视频id' AFTER `account_id`,
ADD COLUMN `extends`  text NULL COMMENT '扩展字段' AFTER `admin_id`;

ALTER TABLE `task_ocean_video_uploads`
ADD COLUMN `n8_material_video_signature`  varchar(64) NOT NULL DEFAULT '' COMMENT 'n8素材系统视频签名' AFTER `n8_material_video_name`;