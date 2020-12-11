/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-12-11 10:02:15
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_campaigns
-- ----------------------------
DROP TABLE IF EXISTS `ocean_campaigns`;
CREATE TABLE `ocean_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(64) NOT NULL DEFAULT '' COMMENT '广告账户id',
  `campaign_id` varchar(64) NOT NULL DEFAULT '' COMMENT '广告组id',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '广告组名称',
  `budget` int(11) NOT NULL DEFAULT '0' COMMENT '广告组预算',
  `budget_mode` varchar(50) NOT NULL DEFAULT '' COMMENT '广告组预算类型',
  `landing_type` varchar(50) NOT NULL DEFAULT '' COMMENT '广告组推广目的',
  `modify_time` varchar(64) NOT NULL DEFAULT '' COMMENT '广告组时间戳',
  `status` varchar(50) NOT NULL DEFAULT '' COMMENT '广告组状态',
  `campaign_create_time` timestamp NULL DEFAULT NULL COMMENT '广告组创建时间',
  `campaign_modify_time` timestamp NULL DEFAULT NULL COMMENT '广告组修改时间',
  `delivery_related_num` varchar(50) NOT NULL DEFAULT '' COMMENT '广告组商品类型',
  `delivery_mode` varchar(50) NOT NULL DEFAULT '' COMMENT '投放类型',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaign_id` (`campaign_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='巨量广告组表';
