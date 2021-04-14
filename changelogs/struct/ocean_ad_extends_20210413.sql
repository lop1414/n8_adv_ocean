ALTER TABLE `ocean_ad_extends`
ADD COLUMN `channel_id`  int NOT NULL DEFAULT 0 COMMENT '渠道id' AFTER `convert_callback_strategy_id`;

ALTER TABLE `ocean_ad_extends`
ADD INDEX `channel_id` (`channel_id`) USING BTREE ;
