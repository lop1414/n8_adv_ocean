ALTER TABLE `ocean_accounts`
ADD COLUMN `company`  varchar(100) NOT NULL DEFAULT '' COMMENT '公司' AFTER `name`;

