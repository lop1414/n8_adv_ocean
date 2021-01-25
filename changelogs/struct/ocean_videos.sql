/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50724
Source Host           : localhost:3306
Source Database       : n8_adv_ocean

Target Server Type    : MYSQL
Target Server Version : 50724
File Encoding         : 65001

Date: 2020-12-11 10:00:39
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for ocean_videos
-- ----------------------------
DROP TABLE IF EXISTS `ocean_videos`;
CREATE TABLE `ocean_videos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `video_id` varchar(128) NOT NULL DEFAULT '' COMMENT '视频id',
  `size` varchar(100) NOT NULL DEFAULT '' COMMENT '视频大小',
  `width` int(11) NOT NULL DEFAULT '0' COMMENT '视频宽度',
  `height` int(11) NOT NULL DEFAULT '0' COMMENT '视频高度',
  `format` varchar(255) NOT NULL DEFAULT '' COMMENT '视频格式',
  `signature` varchar(64) NOT NULL DEFAULT '' COMMENT '视频签名',
  `poster_url` varchar(512) NOT NULL DEFAULT '' COMMENT '视频首帧截图',
  `bit_rate` varchar(100) NOT NULL DEFAULT '' COMMENT '码率',
  `duration` float NOT NULL DEFAULT '0' COMMENT '视频时长',
  `material_id` varchar(128) NOT NULL DEFAULT '' COMMENT '素材id',
  `source` varchar(50) NOT NULL DEFAULT '' COMMENT '素材来源',
  `create_time` timestamp NULL DEFAULT NULL COMMENT '素材上传时间',
  `filename` varchar(50) NOT NULL DEFAULT '' COMMENT '素材文件名',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `signature` (`signature`) USING BTREE,
  UNIQUE KEY `video_id` (`video_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=146 DEFAULT CHARSET=utf8 COMMENT='巨量视频表';
