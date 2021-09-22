ALTER TABLE `ocean_creative_logs`
MODIFY COLUMN `before_status`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '原始状态' AFTER `creative_id`,
MODIFY COLUMN `after_status`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' AFTER `before_status`;

