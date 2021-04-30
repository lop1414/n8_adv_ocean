ALTER TABLE `task_ocean_ad_creative_creates`
ADD COLUMN `ad_id`  varchar(255) NOT NULL DEFAULT '' COMMENT '计划id' AFTER `fail_data`,

