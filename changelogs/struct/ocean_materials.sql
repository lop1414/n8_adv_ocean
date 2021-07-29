/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-07-29 19:43:48
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_materials
-- ----------------------------
DROP TABLE IF EXISTS `ocean_materials`;
CREATE TABLE `ocean_materials` (
  `id` varchar(255) NOT NULL,
  `material_type` varchar(50) NOT NULL DEFAULT '' COMMENT '素材类型',
  `file_id` varchar(255) NOT NULL DEFAULT '' COMMENT '文件id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`material_type`,`file_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量素材表';
