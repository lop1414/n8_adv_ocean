/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-07-29 19:44:08
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_images
-- ----------------------------
DROP TABLE IF EXISTS `ocean_images`;
CREATE TABLE `ocean_images` (
  `id` varchar(255) NOT NULL,
  `size` varchar(100) NOT NULL DEFAULT '',
  `width` int(11) NOT NULL DEFAULT '0' COMMENT '宽度',
  `height` int(11) NOT NULL DEFAULT '0' COMMENT '高度',
  `url` varchar(512) NOT NULL DEFAULT '' COMMENT '图片预览地址',
  `format` varchar(255) NOT NULL DEFAULT '' COMMENT '图片格式',
  `signature` varchar(64) NOT NULL DEFAULT '' COMMENT '图片签名',
  `material_id` varchar(128) NOT NULL DEFAULT '' COMMENT '素材id',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '上传时间',
  `filename` varchar(255) NOT NULL DEFAULT '' COMMENT '素材文件名',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `signature` (`signature`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量图片表';
