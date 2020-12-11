/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-12-11 17:26:31
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_regions
-- ----------------------------
DROP TABLE IF EXISTS `ocean_regions`;
CREATE TABLE `ocean_regions` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `parent_id` varchar(255) NOT NULL DEFAULT '' COMMENT '父级id',
  `region_level` varchar(50) NOT NULL DEFAULT '' COMMENT '地域所在层级',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量地域表';
