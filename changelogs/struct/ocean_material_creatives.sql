/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-08-12 16:41:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_material_creatives
-- ----------------------------
DROP TABLE IF EXISTS `ocean_material_creatives`;
CREATE TABLE `ocean_material_creatives` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `material_id` varchar(255) NOT NULL DEFAULT '' COMMENT '素材id',
  `creative_id` varchar(255) NOT NULL DEFAULT '' COMMENT '创意id',
  `material_type` varchar(50) NOT NULL DEFAULT '' COMMENT '素材类型',
  `n8_material_id` int(11) NOT NULL DEFAULT '0' COMMENT 'n8素材id',
  `signature` varchar(128) NOT NULL DEFAULT '' COMMENT '签名',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`material_id`,`creative_id`) USING BTREE,
  KEY `creative_id` (`creative_id`) USING BTREE,
  KEY `n8_material_id` (`n8_material_id`) USING BTREE,
  KEY `signature` (`signature`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=120 DEFAULT CHARSET=utf8 COMMENT='巨量素材-创意关联表表';
