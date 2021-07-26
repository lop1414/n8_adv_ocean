/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-07-26 18:46:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_creative_logs
-- ----------------------------
DROP TABLE IF EXISTS `ocean_creative_logs`;
CREATE TABLE `ocean_creative_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '账户id',
  `ad_id` varchar(255) NOT NULL DEFAULT '' COMMENT '计划id',
  `creative_id` varchar(255) NOT NULL DEFAULT '' COMMENT '创意id',
  `before_status` varchar(255) NOT NULL DEFAULT '' COMMENT '原始状态',
  `after_status` varchar(255) NOT NULL DEFAULT '',
  `notice_status` varchar(50) NOT NULL DEFAULT '' COMMENT '通知状态',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量创意日志表';
