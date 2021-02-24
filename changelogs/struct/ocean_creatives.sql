/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-02-24 14:08:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_creatives
-- ----------------------------
DROP TABLE IF EXISTS `ocean_creatives`;
CREATE TABLE `ocean_creatives` (
  `id` varchar(255) NOT NULL,
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告主ID',
  `ad_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告计划ID',
  `title` varchar(255) DEFAULT NULL COMMENT '创意素材标题',
  `status` varchar(255) DEFAULT NULL COMMENT '创意素材状态',
  `opt_status` varchar(255) DEFAULT NULL COMMENT '创意素材操作状态',
  `image_mode` varchar(255) DEFAULT NULL COMMENT '创意素材类型',
  `creative_create_time` timestamp NULL DEFAULT NULL COMMENT '广告创意创建时间',
  `creative_modify_time` timestamp NULL DEFAULT NULL COMMENT '广告创意更新时间',
  `extends` text COMMENT '扩展字段',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`) USING BTREE,
  KEY `opt_status` (`opt_status`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `ad_id` (`ad_id`) USING BTREE,
  KEY `image_mode` (`image_mode`) USING BTREE,
  KEY `creative_create_time` (`creative_create_time`) USING BTREE,
  KEY `creative_modify_time` (`creative_modify_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量广告创意表';
