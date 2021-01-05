/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2021-01-05 11:40:40
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_ad_converts
-- ----------------------------
DROP TABLE IF EXISTS `ocean_ad_converts`;
CREATE TABLE `ocean_ad_converts` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '转化名称',
  `account_id` varchar(64) NOT NULL DEFAULT '' COMMENT '账户id',
  `app_type` varchar(255) DEFAULT NULL COMMENT '应用类型',
  `package_name` varchar(255) DEFAULT NULL COMMENT '包名',
  `deep_external_action` varchar(255) DEFAULT NULL COMMENT '深度转化目标',
  `download_url` varchar(512) DEFAULT NULL COMMENT '下载地址',
  `opt_status` varchar(255) DEFAULT NULL COMMENT '操作状态',
  `convert_source_type` varchar(255) DEFAULT NULL COMMENT '转化来源',
  `status` varchar(255) DEFAULT NULL COMMENT '转化状态',
  `convert_type` varchar(255) DEFAULT NULL COMMENT '转化类型',
  `action_track_url` varchar(512) DEFAULT NULL COMMENT '点击监测链接',
  `display_track_url` varchar(512) DEFAULT NULL COMMENT '展示监测链接',
  `video_play_effective_track_url` varchar(512) DEFAULT NULL COMMENT '视频有效播放监测链接',
  `video_play_done_track_url` varchar(512) DEFAULT NULL COMMENT '视频播放完毕监测链接',
  `video_play_track_url` varchar(512) DEFAULT NULL COMMENT '视频播放监测链接',
  `convert_activate_callback_url` varchar(512) DEFAULT NULL COMMENT '激活回传地址',
  `app_id` varchar(255) DEFAULT NULL COMMENT 'APP ID',
  `external_url` varchar(512) DEFAULT NULL COMMENT '落地页链接',
  `convert_track_params` varchar(512) DEFAULT NULL COMMENT '监测参数',
  `convert_base_code` varchar(1000) DEFAULT NULL COMMENT '转化基础代码',
  `convert_js_code` varchar(1000) DEFAULT NULL COMMENT '转化代码（JS方式）',
  `convert_html_code` varchar(1000) DEFAULT NULL COMMENT '转化代码（HTML方式）',
  `convert_xpath_url` varchar(512) DEFAULT NULL COMMENT '转化页面',
  `convert_xpath_value` varchar(512) DEFAULT NULL COMMENT '转化路径',
  `open_url` varchar(512) DEFAULT NULL COMMENT '直达链接',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `modify_time` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  `ignore_params` varchar(512) DEFAULT NULL COMMENT '转化类型下匹配规则字段',
  `convert_data_type` varchar(255) DEFAULT NULL COMMENT '转化统计方式',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='巨量转化目标表';
