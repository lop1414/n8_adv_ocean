ALTER TABLE `task_ocean_image_uploads`
ADD COLUMN `n8_material_image_id`  int NOT NULL DEFAULT 0 COMMENT 'n8素材系统图片id' AFTER `account_id`,
ADD COLUMN `n8_material_image_signature`  varchar(64) NOT NULL DEFAULT '' COMMENT 'n8素材系统图片签名' AFTER `n8_material_image_name`;

