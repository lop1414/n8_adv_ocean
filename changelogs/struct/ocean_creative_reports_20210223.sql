ALTER TABLE `ocean_creative_reports`
ADD INDEX `creative_id` (`creative_id`) USING BTREE ;

ALTER TABLE `ocean_creative_reports`
ADD INDEX `ad_id` (`ad_id`) USING BTREE ,
ADD INDEX `account_id` (`account_id`) USING BTREE ;

