ALTER TABLE `ocean_ad_extends`
ADD COLUMN `convert_callback_strategy_group_id`  int NOT NULL DEFAULT 0 COMMENT '回传策略组id' AFTER `convert_callback_strategy_id`;

