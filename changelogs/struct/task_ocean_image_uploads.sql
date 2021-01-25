/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-11-18 17:58:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for task_ocean_image_uploads
-- ----------------------------
DROP TABLE IF EXISTS `task_ocean_image_uploads`;
CREATE TABLE `task_ocean_image_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL DEFAULT '0' COMMENT '父任务id',
  `app_id` varchar(255) NOT NULL DEFAULT '' COMMENT '应用id',
  `account_id` varchar(255) NOT NULL DEFAULT '' COMMENT '账户id',
  `n8_material_image_path` varchar(512) NOT NULL DEFAULT '' COMMENT 'n8素材系统视频地址',
  `n8_material_image_name` varchar(50) NOT NULL DEFAULT '' COMMENT 'n8素材系统视频名称',
  `exec_status` varchar(50) NOT NULL DEFAULT '' COMMENT '执行状态',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `created_at` (`created_at`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='巨量视频上传任务表';
