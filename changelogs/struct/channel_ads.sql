CREATE TABLE `channel_ads` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ad_id` varchar(100) NOT NULL DEFAULT '' COMMENT '计划id',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `platform` varchar(50) NOT NULL DEFAULT '' COMMENT '平台',
  `extends` text COMMENT '扩展字段',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `channel_ad` (`channel_id`,`ad_id`,`platform`) USING BTREE,
  KEY `ad_id` (`ad_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='渠道-计划关联表';

