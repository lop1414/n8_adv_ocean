ALTER TABLE `clicks`
CHANGE COLUMN `click_id` `adv_click_id`  varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `request_id`,
DROP INDEX `click_id` ,
ADD INDEX `adv_click_id` (`adv_click_id`) USING BTREE ;

