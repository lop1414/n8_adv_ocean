ALTER TABLE `ocean_campaigns`
DROP COLUMN `id`,
CHANGE COLUMN `campaign_id` `id`  varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '广告组id' FIRST ,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`),
DROP INDEX `campaign_id`;

