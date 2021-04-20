ALTER TABLE `clicks`
MODIFY COLUMN `ua`  varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT 'user agent' AFTER `ip`;

