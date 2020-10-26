/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-10-26 09:52:38
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for error_logs
-- ----------------------------
DROP TABLE IF EXISTS `error_logs`;
CREATE TABLE `error_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `exception` varchar(50) NOT NULL DEFAULT '' COMMENT '异常名称',
  `code` varchar(50) NOT NULL DEFAULT '' COMMENT '错误码',
  `message` varchar(512) NOT NULL DEFAULT '' COMMENT '错误提示',
  `data` text NOT NULL COMMENT '错误数据',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `code` (`code`) USING BTREE,
  KEY `exception` (`exception`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='错误日志表';
