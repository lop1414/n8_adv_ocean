ALTER TABLE `ocean_accounts`
ADD COLUMN `belong_platform` varchar(50) NOT NULL COMMENT '归宿平台' AFTER `type`;
