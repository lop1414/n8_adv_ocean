/*
 Navicat Premium Data Transfer

 Source Server         : 虚拟机9.7.2
 Source Server Type    : MySQL
 Source Server Version : 50732
 Source Host           : localhost:3306
 Source Schema         : n8_adv_ocean

 Target Server Type    : MySQL
 Target Server Version : 50732
 File Encoding         : 65001

 Date: 17/03/2022 15:07:41
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for roi_convert_callbacks
-- ----------------------------
DROP TABLE IF EXISTS `roi_convert_callbacks`;
CREATE TABLE `roi_convert_callbacks` (
  `convert_callback_id` bigint(20) NOT NULL,
  `extends` text COMMENT '扩展字段',
  `fail_data` text COMMENT '失败数据',
  `callback_at` timestamp NULL DEFAULT NULL COMMENT '回传时间',
  PRIMARY KEY (`convert_callback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量roi转化上报日志表';

SET FOREIGN_KEY_CHECKS = 1;
