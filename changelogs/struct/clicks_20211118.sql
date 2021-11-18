ALTER TABLE `clicks`
ADD COLUMN `click_id`  varchar(500) NULL AFTER `request_id`,
ADD INDEX `click_id` (`click_id`) USING BTREE ;

