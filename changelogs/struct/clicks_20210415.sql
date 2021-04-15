ALTER TABLE `clicks`
ADD COLUMN `click_source`  varchar(50) NOT NULL DEFAULT '' COMMENT '来源' AFTER `id`,
ADD COLUMN `link`  varchar(512) NOT NULL DEFAULT '' COMMENT '落地页原始url' AFTER `caid`,
ADD COLUMN `extends`  text NOT NULL COMMENT '扩展字段' AFTER `link`;

