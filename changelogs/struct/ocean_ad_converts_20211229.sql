ALTER TABLE `ocean_ad_converts`
MODIFY COLUMN `action_track_url`  varchar(2048) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '点击监测链接' AFTER `convert_type`,
MODIFY COLUMN `display_track_url`  varchar(2048) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '展示监测链接' AFTER `action_track_url`;

