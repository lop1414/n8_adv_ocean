/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-02-04 10:25:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_account_reports
-- ----------------------------
DROP TABLE IF EXISTS `ocean_account_reports`;
CREATE TABLE `ocean_account_reports` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告主id',
  `stat_datetime` timestamp NULL DEFAULT NULL COMMENT '数据起始时间',
  `cost` int(11) NOT NULL DEFAULT '0' COMMENT '展现数据-总花费',
  `show` int(11) NOT NULL DEFAULT '0' COMMENT '展现数据-展示数',
  `click` int(11) NOT NULL DEFAULT '0' COMMENT '展现数据-点击数',
  `convert` int(11) NOT NULL DEFAULT '0' COMMENT '转化数',
  `extends` text COMMENT '扩展字段',
  PRIMARY KEY (`id`),
  UNIQUE KEY `stat_datetime_account_id` (`stat_datetime`,`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10554 DEFAULT CHARSET=utf8 COMMENT='巨量广告主数据报表';
