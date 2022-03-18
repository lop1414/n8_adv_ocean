ALTER TABLE `clicks`
MODIFY COLUMN `muid`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '安卓为IMEI, IOS为IDFA' AFTER `convert_id`,
MODIFY COLUMN `android_id`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '安卓id' AFTER `muid`,
MODIFY COLUMN `oaid`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'Android Q及更高版本的设备号' AFTER `android_id`;

