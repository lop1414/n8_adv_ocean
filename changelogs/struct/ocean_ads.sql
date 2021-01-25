/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-12-24 17:01:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_ads
-- ----------------------------
DROP TABLE IF EXISTS `ocean_ads`;
CREATE TABLE `ocean_ads` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '计划名称',
  `account_id` varchar(64) NOT NULL DEFAULT '' COMMENT '账户id',
  `campaign_id` varchar(255) NOT NULL DEFAULT '' COMMENT '广告组id',
  `modify_time` varchar(64) NOT NULL DEFAULT '' COMMENT '上次修改时间标识',
  `ad_modify_time` timestamp NULL DEFAULT NULL COMMENT '计划上次修改时间',
  `ad_create_time` timestamp NULL DEFAULT NULL COMMENT '计划创建时间',
  `status` varchar(255) NOT NULL DEFAULT '' COMMENT '广告计划投放状态',
  `opt_status` varchar(255) NOT NULL DEFAULT '' COMMENT '广告计划操作状态',
  `delivery_range` varchar(255) DEFAULT NULL COMMENT '投放范围',
  `union_video_type` varchar(255) DEFAULT NULL COMMENT '投放形式（穿山甲视频创意类型）',
  `download_type` varchar(255) DEFAULT NULL COMMENT '应用下载方式',
  `app_type` varchar(255) DEFAULT NULL COMMENT '下载类型',
  `download_mode` varchar(255) DEFAULT NULL COMMENT '优先从系统应用商店下载（下载模式）',
  `convert_id` varchar(255) DEFAULT NULL COMMENT '转化目标id',
  `smart_bid_type` varchar(255) DEFAULT NULL COMMENT '投放场景(出价方式)',
  `adjust_cpa` varchar(255) DEFAULT NULL COMMENT '是否调整自动出价',
  `flow_control_mode` varchar(255) DEFAULT NULL COMMENT '竞价策略(投放方式)',
  `budget_mode` varchar(255) DEFAULT NULL COMMENT '预算类型',
  `budget` varchar(255) DEFAULT NULL COMMENT '预算',
  `pricing` varchar(255) DEFAULT NULL COMMENT '付费方式（计划出价类型）',
  `bid` varchar(255) DEFAULT NULL COMMENT '点击出价/展示出价，当pricing为"CPC"、"CPM"、"CPV"出价方式时有值',
  `cpa_bid` varchar(255) DEFAULT NULL COMMENT '目标转化出价/预期成本， 当pricing为"OCPM"、"OCPC"、"CPA"出价方式时有值',
  `deep_bid_type` varchar(255) DEFAULT NULL COMMENT '深度优化方式',
  `deep_cpabid` varchar(255) DEFAULT NULL COMMENT '深度优化出价',
  `extends` text COMMENT '扩展字段',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `campaign_id` (`campaign_id`) USING BTREE,
  KEY `ad_modify_time` (`ad_modify_time`) USING BTREE,
  KEY `ad_create_time` (`ad_create_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量广告计划表';
