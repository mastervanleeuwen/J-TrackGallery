<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @author      Christian Knorr, InJooOSM  <christianknorr@users.sourceforge.net>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        http://jtrackgallery.net/
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;

/*
 *  Helper class for mapping functios
 *
 */

class JtgMapHelper {
	
	/**
	 * generate JavaScript initialisation part for maps 
	 *     (common for overview and track map) 
	 * @param  $targetid  string name of html div in which the map is displayed
	 *
	 * @return string with JavaSript to set up map
	 */
	static public function parseMapInitJS($targetid='jtg_map') {
		$mapUnits = 'metric';
		$cfg = JtgHelper::getConfig();
		if ($cfg->unit == 'miles') $mapUnits = 'imperial';
		$map = "\n<script type=\"text/javascript\">\n".
				"	jtgBaseUrl = \"".Uri::root()."\";\n".
   			"	jtgTemplateUrl = \"".Uri::root()."components/com_jtg/assets/template/".$cfg->template."\";\n".
				"	jtgMapInit('".JText::_("COM_JTG_MAP_LAYERS")."','".$targetid."');\n";
		$params = JComponentHelper::getParams('com_jtg');
		if ($params->get('jtg_param_show_scale')) $map .= "	jtgMap.addControl(new ol.control.ScaleLine({units: '$mapUnits'}));\n";
		if ($params->get('jtg_param_show_mouselocation')) $map .= "	jtgMap.addControl(new ol.control.MousePosition( {coordinateFormat: ol.coordinate.createStringXY(4), projection: 'EPSG:4326' } ));\n";
		if ($params->get('jtg_param_show_panzoombar')) $map .= "	jtgMap.addControl(new ol.control.ZoomSlider());\n";
		return $map;
	}

	/**
	 * generate JavaScript for a map with this gps track
	 *
	 * @param   gpsClass $gpsTrack  parsed GPS file
	 * @param   integer  $trackid   track ID in database
	 * @param   integer  $mapid  map ID
	 *
	 * @return string with JavaSript to set up map
	 */
	static public function parseTrackMapJS($gpsTrack, $trackid, $mapid, $imageList, $makepreview = false, $showLocationButton = true, $layerSwitcher = false, $trackColors = array(), $targetid = 'jtg_map')
	{
		$mainframe = JFactory::getApplication();
		$cfg = JtgHelper::getConfig();
		$iconurl = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";
		$iconpath = JPATH_SITE . "/components/com_jtg/assets/template/" . $cfg->template . "/images/";

		$map = JtgMapHelper::parseMapInitJS($targetid);
		$map .= JtgMapHelper::parseMapLayersJS($mapid,$layerSwitcher);
		
		$params = JComponentHelper::getParams('com_jtg');
		if ($params->get('jtg_param_add_startmarker')) 
		{
			$trackDrawOptions = 'true'; 
		}
		else
		{
			$trackDrawOptions = 'false'; 
		}
		$animCursor = 'false';
		if ($params->get('jtg_param_disable_map_animated_cursor')) 
		{
			$trackDrawOptions = $trackDrawOptions.', false'; 
		}
		else
		{
			$trackDrawOptions = $trackDrawOptions.', true'; 
		}

      $geoImgsArrayJS = JtgMapHelper::parseGeotaggedImgs($trackid, $cfg->max_geoim_height, $iconpath, $iconurl, $imageList);

		$trkArrJS = array();
		for ($itrk = 0; $itrk < $gpsTrack->trackCount; $itrk++) {
			$segCoordsArrJS = array();
			for ($iseg = 0; $iseg < $gpsTrack->track[$itrk]->segCount; $iseg++) {
				$segCoordsArrJS[] = '[ '.implode(', ',array_map(function($coord) { return "[ $coord[0], $coord[1] ]"; }, $gpsTrack->track[$itrk]->coords[$iseg])).' ]';
			}
			$trkArrJS[] = "{name : '".htmlentities(trim($gpsTrack->track[$itrk]->trackname),ENT_QUOTES)."', ".
				"coords : [ ".implode(",\n",$segCoordsArrJS)." ]}";
		}
		if ($gpsTrack->trackCount == 0) {
			for ($irte = 0; $irte < $gpsTrack->routeCount; $irte++) {
				$segCoordsArrJS = array();
				$segCoordsArrJS[] = '[ '.implode(', ',array_map(function($coord) { return "[ $coord[0], $coord[1] ]"; }, $gpsTrack->route[$irte]->coords)).' ]';
				$trkArrJS[] = "{name : '".htmlentities(trim($gpsTrack->route[$irte]->trackname),ENT_QUOTES)."', ".
					"coords : [ ".implode(",\n",$segCoordsArrJS)." ]}";
			}
		}
		$map .= "	trackData = [".implode(",\n",$trkArrJS)."];\n";
		$colorsJS = ", ['#ff00ff']";
		if (count($trackColors)) {
			$colorsJS=", ['".implode("','",$trackColors)."']";
		}
		$distInt = 0;
		$distUnit = 1000;
		if ($params->get('jtg_param_show_dist_markers') && $gpsTrack->distance > 1 && $gpsTrack->distance < 1000) {
			$trackLength = $gpsTrack->distance;
			if (strtolower($cfg->unit)=='miles') {
				$trackLength = JtgHelper::getMiles($trackLength);
				$distUnit = 1609;
			}
			$distInt = ceil($trackLength/25);
			$distIntScale = pow(10,floor(log10($distInt)));
			$distInt /= $distIntScale;
			if ($distInt > 2 && $distInt < 5) $distInt = 5;
			if ($distInt > 5 && $distInt < 10) $distInt = 10;
			$distInt *= $distIntScale;
		}
		$map .= "	drawTrack(trackData, ".$trackDrawOptions.$colorsJS.", {$distInt}, {$distUnit});\n";

		if ($showLocationButton) {
			JFactory::getDocument()->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons'); // For geolocation/center icon
   		$map .= "	jtgMap.addControl(new ShowLocationControl());\n";
		}
      
		if ($layerSwitcher) {
			JFactory::getDocument()->addStyleSheet(Uri::root().'media/com_jtg/js/ol-layerswitcher/ol-layerswitcher.css');
			JFactory::getDocument()->addScript(Uri::root().'media/com_jtg/js/ol-layerswitcher/ol-layerswitcher.js');
   		$map .= "	jtgMap.addControl(new ol.control.LayerSwitcher());\n";
		}

		if (strlen($geoImgsArrayJS) != 0) {
			$map .= "\t".$geoImgsArrayJS;	
			$map .= "\n	addGeoPhotos(geoImages);\n";
   	}
		$map .= $gpsTrack->parseWPs();
		if ($makepreview) $map .= "	addPreviewTrigger();\n";
		$map .= "</script>\n";
		return $map;
	}

	static public function getMapTypes() {
		return array('Open Street Map','IGN Geoportail (FR)','Bing Map');
	}

	/**
	 * generate JavaScript for map layers
	 *
	 * @param  integer $mapid default map ID (from database)
	 * @param  boolean $layerSwitcher  flag to show layer switcher
	 *
	 */
	static function parseMapLayersJS($mapid=0,$layerSwitcher=false)
	{
		$db = JFactory::getDBO();
		if ($mapid != 0) {
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__jtg_maps')
				->where($db->quoteName('id')." = ".$db->quote($mapid));
			$db->setQuery($query);
			$result = $db->loadAssoc();
			$mapLayersJS = "	jtgAddMapLayer(".$result['type'].",'".$result['param']."','".$result['apikey']."','".JText::_($result['name'])."');\n";
			if ($layerSwitcher) {
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__jtg_maps')
					->where($db->quoteName('id').' != '.$db->quote($mapid).' AND published=1');
				$db->setQuery($query);
				$results = $db->loadAssocList();
				foreach ($results as $result) {
					$mapLayersJS .= "	jtgAddMapLayer(".$result['type'].",'".$result['param']."','".$result['apikey']."','".JText::_($result['name'])."', false);\n";
				}
			}
		}
		if ($mapid == 0 || $result == null) {
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__jtg_maps')
				->order($db->quoteName('ordering'))
				->where('published=1');
			if (!$layerSwitcher) $query->setLimit('1');
			$db->setQuery($query);
			$results = $db->loadAssocList();
			$visible = true;
			$mapLayersJS = '';
			foreach ($results as $result) {
				$mapLayersJS .= "	jtgAddMapLayer(".$result['type'].",'".$result['param']."','".$result['apikey']."','".JText::_($result['name'])."', ".($visible?'true':'false').");\n";
				if ($visible) $visible = false;
			}
		}
		return $mapLayersJS;
	}

	/**
	 * Openlayers write maps
	 *
	 * @param   unknown_type  $where   param_description
	 * @param   unknown_type  $tracks  param_description
	 * @param   unknown_type  $params  param_description
	 *
	 * @return return_description
	 */
	static public function parseOverviewMapJS($items,$mapCatId=0,$showtracks=false,$zoomlevel=6,$lon=null,$lat=null,$centerOnGeo=false)
	{
		$cfg = JtgHelper::getConfig();

		// Need to set up map here
		$map = JtgMapHelper::parseMapInitJS();
		$map .= JtgMapHelper::parseMapLayersJS();

		if ($showtracks)
		{
			// Slow when there are many tracks
			$map .= JtgMapHelper::parseTracksJS($items);
		}

		$retval = JtgMapHelper::parseTracksInfoJS($items, $mapCatId);
		$tracksJS = $retval[0];
		$catIcons = $retval[1];
		$map .= "	tracks = [".implode(',',$retval[0])."];\n";
		$map .= "	catIcons = [".implode(',',$retval[1])."];\n";
		$map .= "	addTracksOverviewLayer(tracks, catIcons);\n";
		if (!is_null($lat) && !is_null($lon))
		{
			$map .= "	jtgView.setCenter(ol.proj.fromLonLat([ $lon, $lat ], jtgView.getProjection()));\n";
			$map .= "	jtgView.setZoom($zoomlevel);\n";
		}
		if ($centerOnGeo)
		{
			$map .= "	geoControl = new CenterOnGeoControl();\n";
			$map .= "	geoControl.handleCenterOnGeo();\n";
		}
		else { // Show button
			JFactory::getDocument()->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons'); // For geolocation/center icon
			$map .= "	jtgMap.addControl(new CenterOnGeoControl);\n";
		}
		$map .= "</script>\n";
		return $map;
	}

	/**	
	 * get track and category info for overview map
	 *
	*/
	static private function parseTracksInfoJS($track_array, $mapCatId=0) {
		$markersJS = array();
		$catIdx = array();
		$curCatId = -1;
		$curCatIdx = -1;
		$iCat = 0;
		foreach ( $track_array AS $row )
		{
			$url = JRoute::_("index.php?option=com_jtg&view=track&id=" . $row->id);
			$lon = $row->icon_e;
			$lat = $row->icon_n;
			$catids = explode(',',$row->catid);
			$catid = (int) $catids[0];
			if ($mapCatId && array_search($mapCatId, $catids)) $catid = $mapCatId;
			if ($catid == null) $catid = 0;
			if ($catid != $curCatId) {
				$curCatId = $catid;
				if (isset($catIdx[$catid]))
				{
					$curCatIdx = $catIdx[$catid];
				}
				else {
					$catIdx[$catid] = $iCat;
					$curCatIdx = $iCat;
					$iCat++;
				}
			}
			
			$link = "<a href=\"" . $url . "\">";
			if ($row->title)
			{
				$link .= str_replace(array("'"), array("\'"), $row->title);
			}
			else
			{
				$link .= "<i>" . str_replace(array("'"), array("\'"), JText::_('COM_JTG_NO_TITLE')) . "</i>";
			}
			$link .= "</a>";

			$description = JtgMapHelper::parseDescription($row->description);
			$markersJS[] = "{".
					'		"lon" : ' . $lon . ",\n" .
					'		"lat" : ' . $lat . ",\n" .
					'		"catIdx" : \'' . $curCatIdx . "',\n" .
					'		"link": \'' . $link . "',\n" .
					'		"description": \'' . $description . "'\n" .
					"}\n";
		}
		$catIconNames = array();
		foreach ($catIdx as $catId => $idx)
		{
			if ($catId != 0) $catIconNames[$idx] = "'".jtgHelper::getCatIconName($catId)."'";
			else $catIconNames[$idx] = "'symbol_inter.png'";
		}
		return array($markersJS,$catIconNames);
	}

	/**
	 * Produce JavaScript for a collection of tracks
	 *   Used for overview map
	 *
	 * @param   array of objects  $rows  list of tracks (database rows)
	 *
	 * @return string
	 */
	static private function parseTracksJS($rows)
	{
		if ($rows === null)
		{
			return false;
		}
		else
		{
			// Dummy line for Coding standard
		}

		$params = JComponentHelper::getParams('com_jtg');
		$colors[] = $params->get('jtg_param_track_colors_1');
		$colors[] = $params->get('jtg_param_track_colors_2');
		$colors[] = $params->get('jtg_param_track_colors_3');
		$colors[] = $params->get('jtg_param_track_colors_4');
		$string = "// <!-- parseOLTracks BEGIN -->\n";
		$i = 0;

		// TODO: the vectors are now added one by one instead of as layers with many vectors.
		foreach ($rows AS $row)
		{
			$file = JUri::base()."images/jtrackgallery/uploaded_tracks/" . $row->file;
			$filename = $file;
			// TODO: check file type; this code builds the overview map?
			$string .= "layer_vector = new ol.layer.Vector({";
			$string.="source: new ol.source.Vector({ url: '".$file."', format: new ol.format.GPX() }),\n";
			$string .= "   style: new ol.style.Style({ stroke: new ol.style.Stroke({ color:'" . $colors[$i%4] . "',\n width: 4}) })";
			$string .= "});\n";
			$string .= "jtgMap.addLayer(layer_vector);\n";
			$i++;
		}

		$string .= "// <!-- parseOLTracks END -->\n";

		return $string;
	}

	/**
	 * function_description
	 *
	 * @param   integer  $id                track ID
	 * @param   integer  $max_geoim_height  geotagged image height on maps
	 * @param   string   $iconfolder        icons folder path
	 * @param   string   $httpiconpath      icons folder URL
	 *
	 * @return string html code to display geotagged images
	 */
	static function parseGeotaggedImgs($id, $max_geoim_height, $iconfolder, $httpiconpath, $imageList)
	{
		jimport('joomla.filesystem.folder');
		$max_geoim_height = (int) $max_geoim_height;
		$foundpics = false;
		$httppath = JUri::root() . "images/jtrackgallery/uploaded_tracks_images/track_" . $id . "/";
		$folder = JPATH_SITE . "/images/jtrackgallery/uploaded_tracks_images/" . 'track_' . $id . '/';

		$imgJSarr = '';
		if ($imageList)
		{
			$imgsJS = array();
			foreach ($imageList AS $image)
			{
            if ($image->lon) {
					// Retrieve thumbnail path
					if ( JFile::exists($folder . 'thumbs/thumb1_' . $image->filename))
					{
						$imginfo = getimagesize($folder.'thumbs/thumb1_'.$image->filename);
						$imagepath = $httppath . 'thumbs/thumb1_' . $image->filename;
					}
					else
					{
						// TODO recreate thumbnail if it does not exists (case direct FTP upload of images)
						$imginfo = getimagesize($folder.$image->filename);
						$imagepath = $httppath . $image->filename;
					}

					$foundpics = true;
					$width = $imginfo[0];
					$height = $imginfo[1];

					// TODO: could do this in CSS ?
					if ( ( $height > $max_geoim_height ) OR ( $width > $max_geoim_height ) )
					{
						if ( $height == $width ) // Square
						{
							$height = $max_geoim_height;
							$width = $max_geoim_height;
						}
						elseif ( $height < $width ) // Landscape
						{
							$height = $max_geoim_height / $width * $height;
							$width = $max_geoim_height;
						}
						else // Portrait
						{
							$height = $max_geoim_height;
							$width = $height * $max_geoim_height / $width;
						}
					}

					$size = "width=\"" . (int) $width . "\" height=\"" . (int) $height . "\"";
					$imagehtml = "<img " . $size . " src=\"" . $imagepath . "\" alt=\"" . $image->filename . "\" title=\"" . htmlentities($image->title, ENT_QUOTES) . "\">";
					if (strlen($image->title)) $imagehtml .= "<div class=\"jtg-caption\">".htmlentities($image->title,ENT_QUOTES)."</div>";
					$imgsJS[] = "{long: $image->lon, lat: $image->lat, imghtml : '$imagehtml' }";
            }
			}
			$imgJSarr = "geoImages = [".implode(',',$imgsJS)."];";
		}

		return $imgJSarr;
	}

	static public function parseGraphAxisJS($label, $units, $linecolor, $opposite) 
	{
		$axisJS = <<<EOS
	{ 
		labels: {
			formatter: function() {
               return this.value + ' $units';
			},
			style: {
				color: '$linecolor'
			}
		},
		title: {
			text: '$label ($units)',
				style: {
					color: '$linecolor'
				}
		},
		opposite: $opposite
	}
EOS;

		return $axisJS;
	}

	static public function parseGraphSeriesJS($seriesData, $label, $units, $linecolor, $iaxis, $hide = false) 
	{
		$visible = 'true';
		if ($hide) $visible = 'false';
		$seriesJS = <<<EOS
	{
		name: '$label',
		unit: '$units',
		color: '$linecolor',
		yAxis: $iaxis,
		data: $seriesData,
		visible: $visible,
		marker: {
			enabled: false
		},
		tooltip: {
			valueSuffix: ' $units'
		}
	}
EOS;
		return $seriesJS;
	}

	/**
	 * Generate JavaScript for elevation, speed etc, graphs
	 *
	 * @param   object   $gpsTrack          track object
	 * @param   object   $cfg               jtg configuration object
	 * @param   object   $params            jtg parameters object
	 * @param   boolean  $usepace           use pace instead of speed
	 * @param   string   $elementid         id of element on the page where the graph is shown
    **/
	static public function parseGraphJS($gpsTrack, $cfg, $params, $usepace, $elementid='elevation') {
		$defaultlinecolor = "#000000";
		$bgColor = $cfg->charts_bg? '#' . $cfg->charts_bg :"#ffffff";

		$axesJS = Array();
		$seriesJS = Array();

		$axisnumber = 0;

		if ( $params->get("jtg_param_show_heightchart") AND $gpsTrack->elevationDataExists)
		{
			$charts_linec = $cfg->charts_linec? '#' . $cfg->charts_linec: $defaultlinecolor;
			$axesJS[] = JtgMapHelper::parseGraphAxisJS(JText::_('COM_JTG_ELEVATION'), JText::_('COM_JTG_ELEVATION_UNIT'), $charts_linec,'false');
			$seriesJS[] = JtgMapHelper::parseGraphSeriesJS($gpsTrack->elevationData, JText::_('COM_JTG_ELEVATION'), JText::_('COM_JTG_ELEVATION_UNIT'), $charts_linec, $axisnumber);
			$axisnumber ++;
		}

		if ( $params->get("jtg_param_show_speedchart") AND $gpsTrack->speedDataExists )
		{
			$speedcharthide = 0;
			if ($usepace)
			{
				// Pace is on same axis but speed must be hidden
				$pacechartopposite = (($axisnumber % 2) == 1) ? 'true' : 'false';
				$paceunittxt = JText::_('COM_JTG_PACE_UNIT_' . strtoupper($cfg->unit));
				$charts_linec_pace = $cfg->charts_linec_pace? '#' . $cfg->charts_linec_pace: $defaultlinecolor;
				$axesJS[] = JtgMapHelper::parseGraphAxisJS(JText::_('COM_JTG_PACE'), $paceunittxt, $charts_linec_pace, $pacechartopposite);
				$seriesJS[] = JtgMapHelper::parseGraphSeriesJS($gpsTrack->paceData, JText::_('COM_JTG_PACE'), $paceunittxt, $charts_linec_pace, $axisnumber);
				$axisnumber ++;
				$speedcharthide = 1;
			}

			$speedunittxt = JText::_('COM_JTG_SPEED_UNIT_' . strtoupper($cfg->unit));
			$speedchartopposite = (($axisnumber % 2) == 1) ? 'true' : 'false';
			$charts_linec_speed = $cfg->charts_linec_speed? '#' . $cfg->charts_linec_speed: $defaultlinecolor;
			$axesJS[] = JtgMapHelper::parseGraphAxisJS(JText::_('COM_JTG_SPEED'), $speedunittxt, $charts_linec_speed, $speedchartopposite);
			$seriesJS[] = JtgMapHelper::parseGraphSeriesJS($gpsTrack->speedData, JText::_('COM_JTG_SPEED'), $speedunittxt, $charts_linec_speed, $axisnumber, $speedcharthide);
			$axisnumber ++;
		}

		if ($params->get("jtg_param_show_speedchart") AND $gpsTrack->beatDataExists )
		{
			// Beatchart is on left (first) axis or on right axis when there is a heighchart or a speed chart
			$beatchartaxis = $axisnumber + 1;
			$beatchartopposite = (($axisnumber % 2) == 1) ? 'true' : 'false';
			$charts_linec_heartbeat = $cfg->charts_linec_heartbeat? '#' . $cfg->charts_linec_heartbeat: $defaultlinecolor;
			$axesJS[] = JtgMapHelper::parseGraphAxisJS(JText::_('COM_JTG_HEARTFREQU'), JText::_('COM_JTG_HEARTFREQU_UNIT'), $charts_linec_heartbeat, $beartchartopposite);
			$seriesJS[] = JtgMapHelper::parseGraphSeriesJS($gpsTrack->beatData, JText::_('COM_JTG_HEARTFREQU'), JText::_('COM_JTG_FREQU_UNIT'), $charts_linec_heartbeat, $axisnumber);
			$axisnumber ++;
		}

		$graphJS = "";
		if ($axisnumber)
		{
			$clicktohide = "";
			if ($axisnumber > 1) $clicktohide = JText::_('COM_JTG_CLICK_TO_HIDE'); 
			$graphJS ='<script type="text/javascript">'."\n".
				"	jtgAxes = [ ".implode(',',$axesJS)." ];\n".
				"	jtgSeries = [ ".implode(',',$seriesJS)." ];\n".
				"</script>\n";
			$graphJS .= <<<EOG

<!-- begin Graphs -->

<script type="text/javascript">
	jQuery.noConflict();
</script>
EOG;
			JFactory::getDocument()->addScript("///code.highcharts.com/highcharts.js");
			$autocenter = (bool) $params->get("jtg_param_use_map_autocentering", true) ? 'true':'false';
			if (! (bool) $params->get("jtg_param_disable_map_animated_cursor", false)) $animatedCursor = 'true, animatedCursorLayer, animatedCursorIcon, allpoints'; else $animatedCursor='false';
			$graphJS .= '<script type="text/javascript">'."\n".
				"makeGraph('$elementid',jtgAxes, jtgSeries, '".JText::_('COM_JTG_DISTANCE')."', '".JText::_('COM_JTG_DISTANCE_UNIT_'.strtoupper($cfg->unit))."', '$clicktohide', '$bgColor', $autocenter, $animatedCursor); \n".
				"</script>\n";
		}
		return $graphJS;
	}
	
	/**
	 * Parse DPCalendar location for marker drawing
	 *
	 * @param   unknown_type  $items calendar items
	 *
	 * @return string: javascript array
	 */
	static public function parseDPCalLocations($items)
	{
		$DPCalLocArray = array();
		foreach ($items as $item)
		{
			$DPCalItem = "  \n{\n    'lon' : $item->longitude,\n";
			$DPCalItem .= "    'lat' : $item->latitude,\n";
			$DPCalItem .= "    'title' : '".htmlentities($item->title,ENT_QUOTES)."',\n";
			$DPCalItem .= "    'url' : '".JRoute::_("index.php?option=com_dpcalendar&view=location&id=$item->id")."',\n";
			$DPCalItem .= "    'color' : '".$item->color."'\n";
			$DPCalItem .= "  }";
			$DPCalLocArray[] = $DPCalItem;
		}
		return "[ ".implode(',', $DPCalLocArray)." ];";	
	}

	/**
	 * function_description
	 *
	 * @param   string  $desc  track description
	 *
	 * @return string
	 */
	static public function parseDescription($desc)
	{
		$stringlength = 280;

		// Strip all tags but <p>
		$desc = str_replace(array("'","\n","\r"), array("\'","<br/>"," "), $desc);
		$desc = strip_tags($desc, '<p>');

		// Trennung nach <p>Katitel</p> BEGIN
		$desc = str_replace('</p>', "", $desc);
		$desc = explode('<p>', $desc);
		$newdesc = array();
		$count_letters = 0;
		$return = "";

		foreach ( $desc AS $chapter )
		{
			if ( $chapter != "" )
			{
				$chapter = strip_tags($chapter);
				$chapter = trim($chapter);

				// Trennung nach Wörter BEGIN
				$words = explode(' ', $chapter);
				$return .= "<p class=\"jtg-centered\">";
				$rowlen = 0;

				foreach ($words AS $word)
				{
					// Strip additional (non <p>) tags, quote and return "1" wegen der Leerstelle
					$count_letters = ( $count_letters + strlen($word) + 1);

					// Einfügung von Zeilensprung BEGIN
					$rowlen = ( $rowlen + strlen($word) );

					if ( ( $count_letters + strlen($word) ) > $stringlength )
					{
						return $return . "[...]</p>";
					}

					// Einfügung von Zeilensprung END
					$return .= $word . " ";
				}

				$return = trim($return) . "</p>";

				// Trennung nach Wörter END
				$newdesc[] = $chapter;
			}
		}
		// Trennung nach <p>Katitel</p> END

		if ( $count_letters == 0 )
		{
			return "<p>" . str_replace(array("'","\n","\r"), array("\'","<br/>"," "), JText::_('COM_JTG_NO_DESC')) . "</p>";
		}

		return $return;
	}
	/**
	 * function_description
	 *
	 * @param   unknown_type  $t  param_description
	 *
	 * @return return_description
	 */
	static public function transformTtRGB($t)
	{
		if ($t <= 60)
		{
			$r = dechex(255);
			$g = dechex(round($t * 4.25));
			$b = dechex(0);
		}
		elseif ($t <= 120)
		{
			$r = dechex(round(255 - (($t - 60) * 4.25)));
			$g = dechex(255);
			$b = dechex(0);
		}
		elseif ($t <= 180)
		{
			$r = dechex(0);
			$g = dechex(255);
			$b = dechex(round((($t - 120) * 4.25)));
		}elseif ($t <= 240) {
			$r = dechex(0);
			$g = dechex(round(255 - (($t - 180) * 4.25)));
			$b = dechex(255);
		}elseif ($t <= 300) {
			$r = dechex(round((($t - 240) * 4.25)));
			$g = dechex(0);
			$b = dechex(255);
		}elseif ($t < 360) {
			$r = dechex(255);
			$g = dechex(0);
			$b = dechex(round(255 - (($t - 300) * 4.25)));
		}
		elseif ($t >= 360)
		{
			return false;
		}

		if (strlen($r) == 1)
		{
			$r = (string) "0" . $r;
		}

		if (strlen($g) == 1)
		{
			$g = (string) "0" . $g;
		}

		if (strlen($b) == 1)
		{
			$b = (string) "0" . $b;
		}

		return $r . $g . $b;
	}

	/**
	 * function_description
	 *
	 * @param   integer  $count  color number
	 *
	 * @return array of count color in RGB format
	 */
	static public function calculateAllColors($count)
	{
		$color = array();

		for ($i = 1;$i <= $count;$i++)
		{
			$color[($i - 1)] = JtgMapHelper::transformTtRGB(round(300 / $count * $i));
		}

		return $color;
	}

	/**
	 * function_description
	 *
	 * @param   string  $wish  optionnal expected color
	 *
	 * @return color (#000000 - #ffffff) or own wish
	 */
	static public function getHexColor($wish = false)
	{
		if ($wish !== false)
		{
			return $wish;
		}

		$color = "";

		for ($i = 0;$i < 3;$i++)
		{
			$dec = (int) rand(16, 128);
			$color .= dechex($dec);
		}

		return ("#" . $color);
	}
}
?>
