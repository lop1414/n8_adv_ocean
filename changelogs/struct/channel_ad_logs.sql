CREATE TABLE `channel_ad_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `channel_ad_id` bigint(20) NOT NULL DEFAULT '0' COMMENT '渠道计划关联id',
  `ad_id` varchar(255) NOT NULL DEFAULT '' COMMENT '计划id',
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `platform` varchar(50) NOT NULL DEFAULT '' COMMENT '平台',
  `extends` text COMMENT '扩展信息',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`) USING BTREE,
  KEY `ad_id` (`ad_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='渠道计划日志表';

