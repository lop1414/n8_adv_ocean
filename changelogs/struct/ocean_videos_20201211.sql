ALTER TABLE `ocean_videos`
DROP INDEX `signature` ,
ADD INDEX `signature` (`signature`) USING BTREE ;

ALTER TABLE `ocean_videos`
MODIFY COLUMN `filename`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '素材文件名' AFTER `create_time`;
