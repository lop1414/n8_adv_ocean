ALTER TABLE `ocean_accounts`
MODIFY COLUMN `name`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '名称' AFTER `adv_app_id`,
CHANGE COLUMN `type` `account_role`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '账户角色' AFTER `name`,
MODIFY COLUMN `belong_platform`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '归宿平台' AFTER `account_role`,
MODIFY COLUMN `account_id`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '广告账户id' AFTER `belong_platform`,
MODIFY COLUMN `token`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `account_id`,
MODIFY COLUMN `refresh_token`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `token`,
MODIFY COLUMN `created_at`  timestamp NULL DEFAULT NULL COMMENT '创建时间' AFTER `fail_at`,
MODIFY COLUMN `updated_at`  timestamp NULL DEFAULT NULL COMMENT '更新时间' AFTER `created_at`,
MODIFY COLUMN `extend`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '扩展字段' AFTER `updated_at`,
MODIFY COLUMN `parent_id`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '父级id' AFTER `extend`,
MODIFY COLUMN `admin_id`  int(11) NOT NULL DEFAULT 0 COMMENT '管理员id' AFTER `status`;


ALTER TABLE `ocean_accounts`
CHANGE COLUMN `adv_app_id` `app_id`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '应用id' AFTER `id`,
DROP INDEX `ad` ,
ADD UNIQUE INDEX `ad` (`app_id`, `account_id`) USING BTREE ;



ALTER TABLE `ocean_accounts`
CHANGE COLUMN `token` `access_token`  varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' AFTER `account_id`;

