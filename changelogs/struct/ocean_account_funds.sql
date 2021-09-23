/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-09-23 14:28:26
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_account_funds
-- ----------------------------
DROP TABLE IF EXISTS `ocean_account_funds`;
CREATE TABLE `ocean_account_funds` (
  `account_id` varchar(100) NOT NULL DEFAULT '' COMMENT '账户id',
  `balance` int(11) NOT NULL DEFAULT '0' COMMENT '账户总余额',
  `valid_balance` int(11) NOT NULL DEFAULT '0' COMMENT '账户可用总余额',
  `cash` int(11) NOT NULL DEFAULT '0' COMMENT '现金余额',
  `valid_cash` int(11) NOT NULL DEFAULT '0' COMMENT '现金可用余额',
  `grant` int(11) NOT NULL DEFAULT '0' COMMENT '赠款余额',
  `valid_grant` int(11) NOT NULL DEFAULT '0' COMMENT '赠款可用余额',
  `extends` text COMMENT '扩展字段',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量账户余额表';
