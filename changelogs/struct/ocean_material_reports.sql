/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-07-30 10:45:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_material_reports
-- ----------------------------
DROP TABLE IF EXISTS `ocean_material_reports`;
CREATE TABLE `ocean_material_reports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '账户id',
  `campaign_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告组id',
  `ad_id` varchar(255) NOT NULL DEFAULT '' COMMENT '计划id',
  `material_id` varchar(255) NOT NULL DEFAULT '' COMMENT '素材id',
  `stat_datetime` timestamp NULL DEFAULT NULL COMMENT '统计时间',
  `cost` int(11) NOT NULL DEFAULT '0' COMMENT '消耗',
  `show` int(11) NOT NULL DEFAULT '0' COMMENT '展示数',
  `click` int(11) NOT NULL DEFAULT '0' COMMENT '点击数',
  `convert` int(11) NOT NULL DEFAULT '0' COMMENT '转化数',
  `extends` text COMMENT '扩展字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`stat_datetime`,`material_id`,`ad_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `campaign_id` (`campaign_id`) USING BTREE,
  KEY `ad_id` (`ad_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1605 DEFAULT CHARSET=utf8 COMMENT='巨量素材报表';


