/*
Navicat MySQL Data Transfer

Source Server         : 120.24.144.128(测试)
Source Server Version : 50505
Source Host           : localhost:3367
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2021-04-13 11:51:32
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for clicks
-- ----------------------------
DROP TABLE IF EXISTS `clicks`;
CREATE TABLE `clicks` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `campaign_id` varchar(100) NOT NULL DEFAULT '' COMMENT '广告组id',
  `ad_id` varchar(100) NOT NULL DEFAULT '' COMMENT '计划id',
  `creative_id` varchar(100) NOT NULL DEFAULT '' COMMENT '创意id',
  `request_id` varchar(100) NOT NULL,
  `product_id` int(11) NOT NULL DEFAULT 0 COMMENT '产品id',
  `creative_type` varchar(50) NOT NULL DEFAULT '' COMMENT '创意样式',
  `creative_site` varchar(100) NOT NULL DEFAULT '' COMMENT '广告投放位置',
  `convert_id` varchar(100) NOT NULL DEFAULT '' COMMENT '转化id',
  `muid` varchar(100) NOT NULL DEFAULT '' COMMENT '安卓为IMEI, IOS为IDFA',
  `android_id` varchar(100) NOT NULL DEFAULT '' COMMENT '安卓id',
  `oaid` varchar(100) NOT NULL DEFAULT '' COMMENT 'Android Q及更高版本的设备号',
  `oaid_md5` varchar(64) NOT NULL DEFAULT '' COMMENT 'Android Q及更高版本的设备号的md5摘要',
  `os` varchar(50) NOT NULL DEFAULT '' COMMENT '操作系统平台',
  `ip` varchar(15) NOT NULL DEFAULT '' COMMENT 'IP地址',
  `ua` varchar(255) NOT NULL DEFAULT '' COMMENT 'user agent',
  `click_at` timestamp NULL DEFAULT NULL COMMENT '点击时间',
  `callback_param` varchar(512) NOT NULL DEFAULT '' COMMENT '回调参数',
  `model` varchar(100) NOT NULL DEFAULT '' COMMENT '手机型号',
  `union_site` varchar(100) NOT NULL DEFAULT '',
  `caid` varchar(100) NOT NULL DEFAULT '' COMMENT '不同版本的中国广告协会互联网广告标识，CAID1是20201230版，暂无CAID2',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `muid` (`muid`) USING BTREE,
  KEY `oaid` (`oaid`) USING BTREE,
  KEY `ip` (`ip`) USING BTREE,
  KEY `oaid_md5` (`oaid_md5`) USING BTREE,
  KEY `request_id` (`request_id`) USING BTREE,
  KEY `click_at` (`click_at`) USING BTREE,
  KEY `product_id` (`product_id`) USING BTREE,
  KEY `ad_id` (`ad_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COMMENT='巨量点击表';
