/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-04-19 10:38:33
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for channel_ads
-- ----------------------------
DROP TABLE IF EXISTS `channel_ads`;
CREATE TABLE `channel_ads` (
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `ad_id` varchar(100) NOT NULL DEFAULT '' COMMENT '计划id',
  UNIQUE KEY `channel_ad` (`channel_id`,`ad_id`) USING BTREE,
  KEY `ad_id` (`ad_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='渠道-计划关联表';
