ALTER TABLE `n8_adv_ocean`.`ocean_accounts`
    ADD COLUMN `roi_callback_status` varchar(50) NOT NULL COMMENT 'roi回传状态' AFTER `status`;
