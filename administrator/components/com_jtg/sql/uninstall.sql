DROP TABLE IF EXISTS `#__jtg_maps`;

DROP TABLE IF EXISTS `#__jtg_cats`;

DROP TABLE IF EXISTS `#__jtg_config`;

DROP TABLE IF EXISTS `#__jtg_files`;

DROP TABLE IF EXISTS `#__jtg_photos`;

DROP TABLE IF EXISTS `#__jtg_votes`;

DROP TABLE IF EXISTS `#__jtg_terrains`;

DROP TABLE IF EXISTS `#__jtg_comments`;

DROP TABLE IF EXISTS `#__jtg_users`;

DELETE FROM `#__ucm_base` WHERE ucm_type_id in 
	(select type_id from `#__content_types` WHERE type_alias = 'com_jtg.file');
DELETE FROM `#__ucm_content` WHERE core_type_alias = 'com_jtg.file';
DELETE FROM `#__contentitem_tag_map`WHERE type_alias = 'com_jtg.file';
DELETE FROM `#__content_types` WHERE type_alias = 'com_jtg.file';
