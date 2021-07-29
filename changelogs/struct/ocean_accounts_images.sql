/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-07-29 19:43:41
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_accounts_images
-- ----------------------------
DROP TABLE IF EXISTS `ocean_accounts_images`;
CREATE TABLE `ocean_accounts_images` (
  `account_id` varchar(128) NOT NULL DEFAULT '' COMMENT '账户id',
  `image_id` varchar(128) NOT NULL DEFAULT '' COMMENT '图片id',
  UNIQUE KEY `account_image` (`account_id`,`image_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量账户-图片关联表';
