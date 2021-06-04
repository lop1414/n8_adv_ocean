ALTER TABLE `clicks`
MODIFY COLUMN `link`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '落地页原始url' AFTER `caid`;

