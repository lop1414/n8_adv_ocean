/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-10-26 09:52:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for admin_request_logs
-- ----------------------------
DROP TABLE IF EXISTS `admin_request_logs`;
CREATE TABLE `admin_request_logs` (
  `request_id` varchar(128) NOT NULL,
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `ip` varchar(255) DEFAULT '' COMMENT 'IP',
  `uri` varchar(255) DEFAULT '' COMMENT '接口',
  `request` text COMMENT '请求参数',
  `response` mediumtext COMMENT '响应参数',
  `exec_time` float NOT NULL DEFAULT '0' COMMENT '执行时间',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`request_id`),
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `uri` (`uri`) USING BTREE,
  KEY `ip` (`ip`) USING BTREE,
  KEY `admin_id` (`admin_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='请求日志表';
