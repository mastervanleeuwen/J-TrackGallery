
CREATE TABLE IF NOT EXISTS `#__jtg_maps` (
	`id` int(2) NOT NULL AUTO_INCREMENT,
	`name` varchar(50) NOT NULL,
	`ordering` int(2) NOT NULL,
	`published` int(1) NOT NULL,
	`type` int(2) DEFAULT '0',
	`param` varchar(500),
	`apikey` varchar(150),
	`checked_out` int(10) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jtg_cats` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`parent_id` INT( 10 ) NOT NULL DEFAULT '0',
	`title` varchar(30) NOT NULL,
	`description` varchar(255),
	`image` varchar(60),
	`ordering` int(10) NOT NULL,
	`usepace` TINYINT(1) NOT NULL DEFAULT '0', 
	`published` int(10) NOT NULL,
	`default_map` INT(2) NULL DEFAULT NULL,
	`checked_out` int(10) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jtg_config` (
  `id` int(10) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `type` varchar(30) NOT NULL,
  `max_size` int(10) NOT NULL,
  `max_geoim_height` int(10) NOT NULL,
  `max_thumb_height` int(10) NOT NULL,
  `terms` int(10) NOT NULL,
  `terms_id` int(10) NOT NULL,
  `map_height` varchar(10) NOT NULL,
  `map_width` varchar(10) NOT NULL,
  `charts_width` varchar(10) NOT NULL,
  `charts_height` varchar(10) NOT NULL,
  `charts_linec` varchar(6) NOT NULL,
  `charts_linec_pace` varchar(6) NOT NULL,
  `charts_linec_speed` varchar(6) NOT NULL,
  `charts_linec_heartbeat` varchar(6) NOT NULL,
  `charts_bg` varchar(6) NOT NULL,
  `profile` varchar(5) NOT NULL DEFAULT '0',
  `template` text NOT NULL,
  `inform_autor` int(5) NOT NULL DEFAULT '0',
  `captcha` int(5) NOT NULL DEFAULT '0',
  `ordering` varchar(5) NOT NULL,
  `comments` int(1) NOT NULL,
  `access` int(2) NOT NULL DEFAULT '0',
  `approach` varchar(5) DEFAULT '0',
  `routingiconset` varchar(10) DEFAULT 'real',
  `usevote` tinyint(1) unsigned zerofill DEFAULT '1',
  `max_images` int(10) NOT NULL DEFAULT '10',
  `gallery` varchar(10) NOT NULL DEFAULT 'straight',
  `gallery_code` varchar(200) NOT NULL DEFAULT '',
  `showcharts` tinyint(1) unsigned zerofill DEFAULT '2',
  `uselevel` tinyint(1) unsigned zerofill DEFAULT '1',
  `level` text,
  `maxTrkptDisplay` int(11) NOT NULL DEFAULT '1200'
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


TRUNCATE `#__jtg_config`;

INSERT  IGNORE INTO `#__jtg_config`(`id`, `unit`, `type`, `max_size`, `max_geoim_height`, `max_thumb_height`, `terms`, `terms_id`, `map_height`, `map_width`, `charts_width`, `charts_height`, `charts_linec`, `charts_linec_pace`, `charts_linec_speed`, `charts_linec_heartbeat`, `charts_bg`, `profile`, `template`, `inform_autor`, `captcha`, `ordering`, `comments`, `access`, `approach`, `routingiconset`, `usevote`, `max_images`, `gallery`, `gallery_code`, `showcharts`, `uselevel`, `level`, `maxTrkptDisplay`) VALUES
(1, 'kilometer', 'jpg,jpeg,png,gif', 1500, 400, 210, 0, 0, '500px', '100%', '100%', '180px', '33FF66', 'FFCC33', '66CCFF', 'FF99CC', 'FFFFFF', '0', 'default', 1, 0, 'DESC', 0, 0, 'no', 'real', 1, 10, 'highslide', '{gallery}%folder%{/gallery}', 2, 1, 'COM_JTG_LEVEL_1\r\nCOM_JTG_LEVEL_2\r\nCOM_JTG_LEVEL_3\r\nCOM_JTG_LEVEL_4\r\nCOM_JTG_LEVEL_5', 6000);

CREATE TABLE IF NOT EXISTS `#__jtg_files` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`uid` int(10) NOT NULL,
	`catid` varchar(255),
	`title` varchar(255) NOT NULL,
	`alias` varchar(255) DEFAULT '' NOT NULL,
	`file` varchar(127),
	`terrain` varchar(255),
	`description` text,
	`published` int(10) NOT NULL DEFAULT '1',
	`default_map` INT(2) NULL DEFAULT NULL,
	`date` date NOT NULL,
	`hits` int(10) DEFAULT '0',
	`checked_out` int(10) DEFAULT NULL,
	`start_n` varchar(20) NOT NULL,
	`start_e` varchar(20) NOT NULL,
	`icon_n` float(20) NOT NULL,
	`icon_e` float(20) NOT NULL,
	`distance` float(10,2),
	`ele_asc` int(10) NOT NULL,
	`ele_desc` int(10) NOT NULL,
	`level` int(5) NOT NULL DEFAULT '1',
	`access` INT(2) NOT NULL,
	`istrack` TINYINT(1) DEFAULT NULL,
	`iswp` TINYINT(1) DEFAULT NULL,
	`isroute` TINYINT(1) DEFAULT NULL,
	`iscache` TINYINT(1) DEFAULT NULL,
	`isroundtrip` TINYINT(1) NOT NULL DEFAULT '0',
	`vote` FLOAT(5,3) UNSIGNED ZEROFILL DEFAULT '0.000',
	`hidden` int(1) UNSIGNED DEFAULT '0',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jtg_photos` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`trackID` int(10) NOT NULL,
	`title` varchar(256),
	`filename` varchar(50) NOT NULL,
	`lat` float(24),
	`lon` float(24), 
	PRIMARY KEY (id),
	FOREIGN KEY (trackID) REFERENCES #__jtg_files (id)
) ENGINE=MyISAM	DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jtg_votes` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`trackid` int(10) NOT NULL,
	`rating` int(3) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM	DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__jtg_terrains` (
	`id` int(10) NOT NULL AUTO_INCREMENT,
	`title` varchar(30) NOT NULL,
	`published` int(5) NOT NULL,
	`checked_out` int(5) DEFAULT '0',
	`ordering` int(5) NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `#__jtg_comments` (
	`id` INT( 10 ) NOT NULL AUTO_INCREMENT ,
	`uid` INT( 10 ) ,
	`tid` INT( 10 ) NOT NULL ,
	`user` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	`title` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	`text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	`email` VARCHAR( 80 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	`homepage` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
	`published` INT( 5 ) NOT NULL ,
	`date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM;

CREATE TABLE IF NOT EXISTS `#__jtg_users` (
	`user_id` int(10) unsigned,
	`jtglat` FLOAT(20,15),
	`jtglon` FLOAT(20,15),
	`jtgvisible` VARCHAR(3),
PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `#__content_types` (type_title, `table`, rules, field_mappings, router, type_alias)
VALUES ('GPS Track',
'{"special":{"dbtable":"#__jtg_files","key":"id","type":"Jtg_files","prefix":"Table","config":"array()"},"common":{"dbtable":"#__ucm_content","key":"ucm_id","type":"Corecontent","prefix":"Table","config":"array()"}}',
'',
'{"common": {
    "core_content_item_id": "id",
    "core_title": "title",
    "core_state": "published",
    "core_access": "access",
    "core_alias": "alias",
    "core_created_user_id": "uid",
    "core_body": "description"
  }}',
'JtgHelperRoute::getFileRoute',
'com_jtg.file');

INSERT IGNORE INTO `#__jtg_maps` (`name`, `ordering`, `published`, `type`, `param`, `apikey`) VALUES
('COM_JTG_MAP_MAPNIK', 1, 1, 0, '', ''),
('Open Topomap', 2, 1, 0, 'https://{a-c}.tile.opentopomap.org/{z}/{x}/{y}.png', ''),
('COM_JTG_MAP_OSM_HIKE_AND_BIKE', 99, 0, 0, 'https://{a-c}.tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png', ''),
('COM_JTG_MAP_CYCLEMAP', 3, 1, 0, 'https://{a-c}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png', ''),
('COM_JTG_MAP_BING_AERIAL', 4, 0, 2, 'Aerial', ''),
('COM_JTG_MAP_BING_ROAD', 5, 0, 2, 'RoadOnDemand', ''),
('COM_JTG_MAP_BING_HYBRID', 6, 0, 2, 'AerialWithLabelsOnDemand', ''),
('COM_JTG_MAP_FRENCH_IGN_GEOPORTAL', 7, 0, 1, '' ,'choisirgeoportail');

INSERT IGNORE INTO `#__jtg_cats` (`id`, `parent_id`, `title`, `description`, `image`, `ordering`, `published`, `checked_out`) VALUES
    (1, 0, 'dummy','','',0,1,0),
    (2, 0, 'dummy','','',0,1,0),
    (3, 0, 'dummy','','',0,1,0),
    (4, 0, 'dummy','','',0,1,0),
    (5, 0, 'dummy','','',0,1,0),
    (6, 0, 'dummy','','',0,1,0),
    (7, 0, 'dummy','','',0,1,0),
    (8, 0, 'dummy','','',0,1,0),
    (9, 0, 'dummy','','',0,1,0),
    (12, 0, 'COM_JTG_CAT_TREKKING', 'COM_JTG_CAT_TREKKING_DESCRIPTION', 'hiking.png', 0, 1, 0),
    (19, 0, 'COM_JTG_CAT_MOUNTAIN_BIKE', 'COM_JTG_CAT_MOUNTAIN_BIKE_DESCRIPTION', 'mountainbiking-3.png', 1, 1, 0),
    (17, 0, 'COM_JTG_CAT_HORSE_RIDING', 'COM_JTG_CAT_HORSE_RIDING_DESCRIPTION', 'horseriding.png', 2, 1, 0),
    (10, 0, 'COM_JTG_CAT_CAR', 'COM_JTG_CAT_CAR_DESCRIPTION', 'sportscar.png', 7, 1, 0),
    (11, 0, 'COM_JTG_CAT_CAR_44', 'COM_JTG_CAT_CAR_44_DESCRIPTION', 'fourbyfour.png', 4, 1, 0),
    (13, 0, 'COM_JTG_CAT_BIKE', 'COM_JTG_CAT_BIKE_DESCRIPTION', 'cycling.png', 5, 1, 0),
    (14, 0, 'COM_JTG_CAT_MOTORBIKE', 'COM_JTG_CAT_MOTORBIKE_DESCRIPTION', 'motorbike.png', 6, 1, 0),
    (15, 0, 'COM_JTG_CAT_PEDESTRIAN', 'COM_JTG_CAT_PEDESTRIAN_DESCRIPTION', 'hiking.png', 3, 1, 0),
    (16, 0, 'COM_JTG_CAT_GEOCACHE', 'COM_JTG_CAT_GEOCACHE_DESCRIPTION', 'geocachinginternational.png', 8, 1, 0),
    (20, 0, 'COM_JTG_CAT_SNOWSHOEING', 'COM_JTG_CAT_SNOWSHOEING_DESCRIPTION', 'snowshoeing.png',9, 1, 0),
    (21, 0, 'COM_JTG_CAT_TRAIL', 'COM_JTG_CAT_TRAIL_DESCRIPTION', 'hiking.png', 10, 1, 0)
;

DELETE FROM `#__jtg_cats` WHERE title = 'dummy';

INSERT IGNORE INTO `#__jtg_terrains` 
    (`id`,`title`,`published`,`checked_out`,`ordering`) 
VALUES 
    ('1','COM_JTG_TERRAIN_STREET','1','0','0'),
    ('2','COM_JTG_PUBLIC_ACCESS','1','0','0'),
    ('3','COM_JTG_TERRAIN_FARM_TRACK','1','0','0'),
    ('4','COM_JTG_PRIVATE','1','0','0');

INSERT IGNORE INTO `#__jtg_files` (`id`, `uid`, `catid`, `title`, `alias`, `file`, `terrain`, `description`, `published`, `date`, `hits`, `checked_out`, `start_n`, `start_e`, `icon_n`, `icon_e`, `distance`, `ele_asc`, `ele_desc`, `level`, `access`, `istrack`, `iswp`, `isroute`, `iscache`, `vote`, `hidden`) VALUES
(1, 430, '19', '-sample- Woodhead Reconnaissance', 'woodhead-reconnaissane', 'sample_woodhead_reconnaissance.gpx', '2', '<p>This tracks by has been provided by Richard from RSInfotech <a href="http://www.rsinfotech.co.uk/">http://www.rsinfotech.co.uk/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-09-22', 52, 0, '53.4947858175', '-1.8294689814', '53.494786', '-1.829469', 5.94, 357, 284, 1, 0, 1, 0, 0, NULL, 4.000, 0),
(2, 430, '15', '-sample- Circuit de Bavay', 'circuit-de-bavay', 'sample_bavay.gpx', '2', '<p>This tracks by has been provided by Arnaud  from the French Alpin Club:  Club Alpin de Lille <a href="http://clubalpinlille.fr/">http://clubalpinlille.fr/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-09-23', 27, 0, '50.297820', '3.792730', '50.297820', '3.792730', 21.92, 254, 254, 1, 0, 1, 0, 0, 0, 2.667, 0),
(3, 430, '15', '-sample- Circuit Honnelles-Belgique', 'circuit-de-hobelles-belgique', 'sample_honnelles_belgique.gpx', '2', '<p>This tracks by has been provided by Arnaud  from the French Alpin Club:  Club Alpin de Lille <a href=&#34;http://clubalpinlille.fr/&#34;>http://clubalpinlille.fr/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-09-23', 20, 0, '50.364941', '3.775907', '50.364941', '3.775907', 21.29, 256, 256, 1, 0, 1, 0, 0, 0, 4.000, 0),
(4, 430, '15', '-sample- Circuit Vandegie sur Ecaillon', 'circuit-vandegie-sur-ecaillon', 'sample_vandegie_sur_ecaillon.gpx', '2', '<p>This tracks by has been provided by Arnaud  from the French Alpin Club:  Club Alpin de Lille <a href=&#34;http://clubalpinlille.fr/&#34;>http://clubalpinlille.fr/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-09-23', 99, 0, '50.262709', '3.511753', '50.262709', '3.511753', 26.79, 829, 799, 2, 0, 1, 0, 0, 0, 6.500, 0),
(5, 430, '15', '-sample- Via Alpina: Alzarej', 'via-alpina-alzarej', 'sample_via_alpina_alzarej.gpx', '2', '', 1, '2013-10-02', 23, 0, '46.412453', '13.846174', '46.412453', '13.846174', 14.18, 1441, 1304, 1, 0, 1, 0, 0, 0, 0.000, 0),
(13, 430, '12', '-sample- Trek Valroc Secteur 3', 'trek-valroc-secteur-3', 'sample_trek_valroc_3.gpx', '2', '<p>This tracks by has been provided by Pascal from the French Alpin Club:  club alpin français de l''ouest dijonnais <a href="http://valroc.net">http://valroc.net</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-03', 14, 0, '47.510856846', '4.529040931', '47.51086', '4.52904', 34.99, 915, 844, 3, 0, 1, 0, 0, NULL, 0.000, 0),
(12, 430, '12', '-sample- Trek Valroc Secteur 2', 'trek-valroc-secteur-2', 'sample_trek_valroc_2.gpx', '2', '<p>This tracks by has been provided by Pascal from the French Alpin Club:  club alpin français de l''ouest dijonnais <a href=&#34;http://valroc.net&#34;>http://valroc.net</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-03', 10, 0, '47.318670190', '4.598339923', '47.31867', '4.59834', 49.88, 1256, 1235, 1, 0, 1, 0, 0, NULL, 0.000, 0),
(11, 430, '12', '-sample- Trek Valroc Secteur 1', 'trek-valroc-secteur-1', 'sample_trek_valroc_1.gpx', '2', '<p>This tracks by has been provided by Pascal from the French Alpin Club:  club alpin français de l''ouest dijonnais <a href=&#34;http://valroc.net&#34;>http://valroc.net</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-08', 1, 0, '47.323145801', '5.028378814', '47.323146', '5.02879', 53.79, 2017, 1890, 1, 0, 1, 0, 0, NULL, 0.000, 0),
(14, 430, '12', '-sample- Trek Valroc Secteur 4', 'trek-valroc-secteur-4', 'sample_trek_valroc_4.gpx', '2', '<p>This tracks by has been provided by Pascal from the French Alpin Club:  club alpin français de l''ouest dijonnais <a href="http://valroc.net">http://valroc.net</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-04', 5, 0, '47.489614505', '4.685898861', '47.48961', '4.68590',50.14, 1232, 1442, 1, 0, 1, 0, 0, NULL, 0.000, 0),
(15, 430, '12', '-sample- Via Alpina Dobrci', 'via-alpina-dobrci', 'sample_via_alpina_dobrci.gpx', '2', '<p>This tracks by has been provided by Henri from the French Alpin Club:  Club Alpin de Lille <a href="http://clubalpinlille.fr/">http://clubalpinlille.fr/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-04', 6, 0, '46.417789', '14.212165', '46.417789', '14.212165', 8.07, 589, 779, 1, 0, 1, 0, 0, NULL, 0.000, 0),
(16, 430, '12', '-sample- Via Alpina Presernova', 'via-alpina-presernova', 'sample_via_alpina_presernova.gpx', '2', '<p>This tracks by has been provided by Henri from the French Alpin Club:  Club Alpin de Lille <a href=&#34;http://clubalpinlille.fr/&#34;>http://clubalpinlille.fr/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-04', 0, 0, '46.486000', '14.061904', '46.4860', '14.0619904', 13.62, 1079, 475, 1, 0, 1, 0, 0, NULL, 0.000, 0),
(17, 430, '12', '-sample-Via Alpina Roblekov', 'via-alpine-reblekov', 'sample_via_alpina_roblekov.gpx', '2', '<p>This tracks by has been provided by Henri from the French Alpin Club:  Club Alpin de Lille <a href="http://clubalpinlille.fr/">http://clubalpinlille.fr/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-04', 40, 0, '46.431385', '14.174806', '46.431385', '14.174806', 7.26, 1561, 2068, 1, 0, 1, 0, 0, NULL, 4.000, 0),
(6, 430, '19', '-sample- North York Moors - Hutton-le-Hole', 'nort-york-moors-hutton-le-hole', 'sample_north_york_moors.gpx', '2', '<p>This tracks by has been provided by Richard from RSInfotech <a href="http://www.rsinfotech.co.uk/">http://www.rsinfotech.co.uk/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>', 1, '2013-10-20', 10, 0, '54.30400945', '-0.917664888', '54.30400945', '-0.917664888', 20.66, 352, 352, 2, 0, 1, 0, 0, NULL, 0.000, 0),
(8, 430, '12', '-sample- everest-base-camp', 'everest-base-camp', 'everest-base-camp-without-baggage.gpx', '2', '<div class="description">\r\n<p><em>These tracks and the attached image gallery  (see credit below) are only intended to be used as a sample file for testing and demonstrating J!Track Gallery features.</em></p>\r\n</div>\r\n<p><strong>Track : everest-base-camp-without-baggage</strong> from Hank Leukart a TV producer, writer, and travel addict can be downloaded on <a href="http://withoutbaggage.com/gps/everest-base-camp/">Hank Leukart website</a>. <br />--------------<br /><strong>Image Gallery </strong> from <a href="https://www.flickr.com/photos/mckaysavage/sets/72157600191376713">McKay Savage Nepal-Trekking</a><br />licenced under  <a href="https://creativecommons.org/licenses/by/2.0/">Creative Commons Attribution 2.0 Generic (CC BY 2.0)</a></p>\r\n<p>--------------</p>\r\n<p><strong>Hank Leukart trek program:</strong><br /><br />Day 01: Sukhe to Lukla<br />Day 02: Lukla to Monjo<br />Day 03: Monjo to Namche Bazaar<br />Day 05: Namche Bazaar to Tengboche<br />Day 06: Tengboche Geocache<br />Day 07: Tengboche to Pheriche<br />Day 08: Pheriche to Dingboche<br />Day 09: Dingboche to Lobuche<br />Day 12a: Lobuche to Gorak Shep<br />Day 12b: Gorak Shep to Kala Pattar<br />Day 13: Gorak Shep to Everest Base Camp<br />Day 14a: Gorak Shep to Lobuche<br />Day 14b: Lobuche to Pheriche<br />Day 15: Pheriche to Tengboche<br />Day 16: Tengboche to Namche Bazaar<br />Day 17: Namche Bazaar to Lukla</p>', 1, '2011-05-08', 74, 0, '27.671166966', '86.714214999', '27.671167', '86.714215', 140.67, 16362, 15521, 5, 0, 1, 0, 0, NULL, 4.000, 0),
(7, 430, '15', '-sample- Circuit de Bavinchove', 'circuit-de-bavinchove', 'sample_bavinchove.gpx', '2', '<div class="description">\r\n<p>This tracks by has been provided by Arnaud  from the French Alpin Club:  Club Alpin de Lille <a href="http://clubalpinlille.fr/">http://clubalpinlille.fr/</a></p>\r\n<p><em>It its only intended to be used as a sample file for testing and demonstrating J!Track Gallery component.</em></p>\r\n</div>', 1, '0000-00-00', 27, 0, '50.786657000', '2.453629000', '50.786657', '2.453629', 12.58, 0, 0, 1, 0, 1, 0, 0, NULL, 0.000, 0);

INSERT IGNORE INTO `#__jtg_votes` (`id`, `trackid`, `rating`) VALUES
(1, 2, 2),
(2, 2, 5),
(3, 2, 1),
(4, 4, 3),
(5, 4, 10),
(6, 17, 4),
(7, 3, 4),
(8, 1, 4),
(9, 3, 4),
(10, 8, 4);
