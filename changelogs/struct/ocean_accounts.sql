SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_account
-- ----------------------------
DROP TABLE IF EXISTS `ocean_accounts`;
CREATE TABLE `ocean_accounts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adv_app_id` varchar(50) NOT NULL DEFAULT '' COMMENT '应用id',
  `name` varchar(50) NOT NULL COMMENT '名称',
  `type` varchar(50) NOT NULL DEFAULT '' COMMENT '类型',
  `account_id` varchar(50) NOT NULL COMMENT '广告账户id',
  `token` varchar(50) NOT NULL COMMENT '',
  `refresh_token` varchar(50) NOT NULL COMMENT '',
  `fail_at` timestamp NULL DEFAULT NULL COMMENT 'token 过期时间',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `extend` text,
  `parent_id` varchar(50) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT '' COMMENT '状态',
  `admin_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `ad` (`adv_app_id`,`account_id`) USING BTREE,
  KEY `fail_at` (`fail_at`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量账户信息';
