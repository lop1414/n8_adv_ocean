ALTER TABLE `ocean_videos`
DROP INDEX `signature` ,
ADD INDEX `signature` (`signature`) USING BTREE ;

