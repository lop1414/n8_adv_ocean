ALTER TABLE `ocean_ads`
MODIFY COLUMN `status`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '广告计划投放状态' AFTER `ad_create_time`,
MODIFY COLUMN `opt_status`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '广告计划操作状态' AFTER `status`;

