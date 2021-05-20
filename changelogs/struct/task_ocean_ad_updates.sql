/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-05-20 18:04:51
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for task_ocean_ad_updates
-- ----------------------------
DROP TABLE IF EXISTS `task_ocean_ad_updates`;
CREATE TABLE `task_ocean_ad_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL DEFAULT '0' COMMENT '任务id',
  `app_id` varchar(255) NOT NULL DEFAULT '' COMMENT '应用id',
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '账户id',
  `ad_id` varchar(255) NOT NULL DEFAULT '' COMMENT '计划id',
  `ad_update_type` varchar(50) NOT NULL DEFAULT '' COMMENT '更新类型',
  `data` text COMMENT '数据',
  `start_at` timestamp NULL DEFAULT NULL COMMENT '开始时间',
  `exec_status` varchar(50) NOT NULL DEFAULT '' COMMENT '执行状态',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `extends` text COMMENT '扩展字段',
  `fail_data` text COMMENT '失败数据',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`) USING BTREE,
  KEY `task_id` (`task_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='巨量计划更改任务表';
