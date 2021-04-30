/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-04-29 10:28:37
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_creative_group_templetes
-- ----------------------------
DROP TABLE IF EXISTS `ocean_creative_group_templetes`;
CREATE TABLE `ocean_creative_group_templetes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `description` varchar(500) NOT NULL DEFAULT '' COMMENT '描述',
  `data` text COMMENT '数据',
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '管理员id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='巨量创意组模板表';
