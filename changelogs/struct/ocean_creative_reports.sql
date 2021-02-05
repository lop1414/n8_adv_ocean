/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-02-05 15:06:43
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_creative_reports
-- ----------------------------
DROP TABLE IF EXISTS `ocean_creative_reports`;
CREATE TABLE `ocean_creative_reports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告主账户id',
  `campaign_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告组id',
  `ad_id` varchar(255) NOT NULL DEFAULT '' COMMENT '计划id',
  `creative_id` varchar(255) NOT NULL DEFAULT '' COMMENT '创意id',
  `stat_datetime` timestamp NULL DEFAULT NULL COMMENT '数据起始时间',
  `cost` int(11) NOT NULL DEFAULT '0' COMMENT '展现数据-总花费',
  `show` int(11) NOT NULL DEFAULT '0' COMMENT '展现数据-展示数',
  `click` int(11) NOT NULL DEFAULT '0' COMMENT '展现数据-点击数',
  `convert` int(11) NOT NULL DEFAULT '0' COMMENT '转化数据-转化数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stat_datetime_creative_id` (`stat_datetime`,`creative_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=37160 DEFAULT CHARSET=utf8 COMMENT='巨量广告创意数据报表';
