ALTER TABLE `#__jtg_files`
ADD IF NOT EXISTS `icon_n` FLOAT(20),
ADD IF NOT EXISTS `icon_e` FLOAT(20);
UPDATE `#__jtg_files`
SET icon_n = start_n, icon_e = start_e;
