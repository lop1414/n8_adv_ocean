/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-01-13 11:50:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_audience_templetes
-- ----------------------------
DROP TABLE IF EXISTS `ocean_audience_templetes`;
CREATE TABLE `ocean_audience_templetes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '定向模板名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '描述',
  `landing_type` varchar(255) NOT NULL DEFAULT '' COMMENT '推广类型',
  `delivery_range` varchar(255) NOT NULL DEFAULT '' COMMENT '投放范围',
  `audience` text COMMENT '定向内容',
  `estimate` text COMMENT '预估结果',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='定向模板表';
