/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2022-04-08 17:21:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_account_fund_daily_stats
-- ----------------------------
DROP TABLE IF EXISTS `ocean_account_fund_daily_stats`;
CREATE TABLE `ocean_account_fund_daily_stats` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(100) NOT NULL DEFAULT '' COMMENT '账户id',
  `stat_datetime` timestamp NULL DEFAULT NULL COMMENT '统计时间',
  `balance` int(11) NOT NULL DEFAULT '0' COMMENT '日终结余',
  `cash_cost` int(11) NOT NULL DEFAULT '0' COMMENT '现金支出',
  `frozen` int(11) NOT NULL DEFAULT '0' COMMENT '冻结',
  `income` int(11) NOT NULL DEFAULT '0' COMMENT '总存入',
  `reward_cost` int(11) NOT NULL DEFAULT '0' COMMENT '赠款支出',
  `shared_wallet_cost` int(11) NOT NULL DEFAULT '0' COMMENT '共享钱包支出',
  `transfer_in` int(11) NOT NULL DEFAULT '0' COMMENT '总转入',
  `transfer_out` int(11) NOT NULL DEFAULT '0' COMMENT '	',
  `extends` text COMMENT '扩展字段',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`stat_datetime`,`account_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='巨量账户日流水统计表';
