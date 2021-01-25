ALTER TABLE `ocean_videos`
DROP COLUMN `id`,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`video_id`),
DROP INDEX `video_id`;

ALTER TABLE `ocean_videos`
CHANGE COLUMN `video_id` `id`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '视频id' FIRST ;