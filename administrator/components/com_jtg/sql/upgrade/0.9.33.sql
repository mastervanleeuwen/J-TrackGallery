ALTER TABLE `#__jtg_files`
ALTER `hits` SET DEFAULT '0';
ALTER TABLE `#__jtg_maps`
DROP `usepace`,
DROP `default_map`,
DROP `default_overlays`,
DROP `script`,
DROP `code`,
ADD `type` int(2) DEFAULT '0',
ADD `apikey` varchar(150);
UPDATE `#__jtg_maps`
SET type = '0', `param` = '' WHERE name='COM_JTG_MAP_MAPNIK';
UPDATE `#__jtg_maps`
SET type = '0', `param` = 'https://{a-c}.tiles.wmflabs.org/hikebike/{z}/{x}/{y}.png' WHERE name='COM_JTG_MAP_OSM_HIKE_AND_BIKE';
UPDATE `#__jtg_maps`
SET type = '0', `param` = 'https://{a-c}.tile.thunderforest.com/cycle/{z}/{x}/{y}.png' WHERE name='COM_JTG_MAP_CYCLEMAP';
UPDATE `#__jtg_maps`
SET type = '2', `param` = 'Aerial' WHERE name='COM_JTG_MAP_BING_AERIAL';
UPDATE `#__jtg_maps`
SET type = '2', `param` = 'RoadOnDemand' WHERE name='COM_JTG_MAP_BING_ROAD';
UPDATE `#__jtg_maps`
SET type = '2', `param` = 'AerialWithLabelsDemand' WHERE name='COM_JTG_MAP_BING_HYBRID';
UPDATE `#__jtg_maps`
SET type = '1', `param` = '', `apikey` = 'choisirgeoportail' WHERE name='COM_JTG_MAP_FRENCH_IGN_GEOPORTAL';
