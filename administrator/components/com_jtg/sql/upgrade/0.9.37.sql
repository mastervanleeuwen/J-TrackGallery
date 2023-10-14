ALTER TABLE `#__jtg_files`
ADD `icon_n` FLOAT(20),
ADD `icon_e` FLOAT(20);
UPDATE `#__jtg_files`
SET icon_n = start_n, icon_e = start_e;
