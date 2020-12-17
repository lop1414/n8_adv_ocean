/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-12-17 18:50:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for task_ocean_syncs
-- ----------------------------
DROP TABLE IF EXISTS `task_ocean_syncs`;
CREATE TABLE `task_ocean_syncs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL DEFAULT '0' COMMENT '父任务id',
  `app_id` varchar(255) NOT NULL DEFAULT '' COMMENT '应用id',
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '账户id',
  `sync_type` varchar(50) NOT NULL DEFAULT '' COMMENT '同步类型',
  `exec_status` varchar(50) NOT NULL DEFAULT '' COMMENT '执行状态',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '账户id',
  `extends` text COMMENT '扩展字段',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='头条广告组同步任务表';
