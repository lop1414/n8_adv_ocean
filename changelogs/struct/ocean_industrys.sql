/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-12-22 11:07:22
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_industrys
-- ----------------------------
DROP TABLE IF EXISTS `ocean_industrys`;
CREATE TABLE `ocean_industrys` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '行业名称',
  `type` varchar(255) NOT NULL DEFAULT '' COMMENT '类型',
  `parent_id` varchar(255) NOT NULL DEFAULT '' COMMENT '父级id',
  `level` int(11) NOT NULL DEFAULT '0' COMMENT '层级',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量行业类别表';
