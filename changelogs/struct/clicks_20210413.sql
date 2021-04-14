ALTER TABLE `clicks`
CHANGE COLUMN `product_id` `channel_id`  int(11) NOT NULL DEFAULT 0 COMMENT '产品id' AFTER `request_id`,
DROP INDEX `product_id` ,
ADD INDEX `channel_id` (`channel_id`) USING BTREE ;

