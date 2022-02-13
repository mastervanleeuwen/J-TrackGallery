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
	 * generate JavaScript for a map with this gps track
	 *
	 * @param   unknown_type  $track   param_description
	 * @param   unknown_type  $params  param_description
	 *
	 * @return return_description
	 */
	static public function parseTrackMapJS($gpsTrack, $trackid, $mapid, $imageList, $makepreview = false, $showLocationButton = true)
	{
		$mainframe = JFactory::getApplication();
		$cfg = JtgHelper::getConfig();
		$iconurl = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";
		$iconpath = JPATH_SITE . "/components/com_jtg/assets/template/" . $cfg->template . "/images/";
		$animCursor = 'false';
		if (JComponentHelper::getParams('com_jtg')->get('jtg_param_disable_map_animated_cursor') == 0) 
		{
			$animCursor = 'true';
		}

		$map = "\n<script type=\"text/javascript\">\n".
				"	jtgBaseUrl = \"".Uri::root()."\";\n".
   			"	jtgTemplateUrl = \"".Uri::root()."components/com_jtg/assets/template/".$cfg->template."\";\n";
		$map .= JtgMapHelper::parseMapInitJS($mapid);
		$trkArrJS = array();
		for ($itrk = 0; $itrk < $gpsTrack->trackCount; $itrk++) {
			$segCoordsArrJS = array();
			for ($iseg = 0; $iseg < $gpsTrack->track[$itrk]->segCount; $iseg++) {
				$segCoordsArrJS[] = '[ '.implode(', ',array_map(function($coord) { return "[ $coord[0], $coord[1] ]"; }, $gpsTrack->track[$itrk]->coords[$iseg])).' ]';
			}
			$trkArrJS[] = "{name : '".htmlentities(trim($gpsTrack->track[$itrk]->trackname))."', ".
				"coords : [ ".implode(",\n",$segCoordsArrJS)." ]}";
		}
		$map .= "	trackData = [".implode(",\n",$trkArrJS)."];\n";
		$map .= "	drawTrack(trackData, ".$animCursor.");\n";

		if ($showLocationButton) {
			JFactory::getDocument()->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons'); // For geolocation/center icon
   		$map .= "	jtgMap.addControl(new ShowLocationControl());\n";
		}
      
      $geoImgsArrayJS = JtgMapHelper::parseGeotaggedImgs($trackid, $cfg->max_geoim_height, $iconpath, $iconurl, $imageList);
		if (strlen($geoImgsArrayJS) != 0) {
			$map .= $geoImgsArrayJS;	
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
	 *
	 *
	 */
	static function parseMapInitJS($mapid=0)
	{
		$db = JFactory::getDBO();
		if ($mapid != 0) {
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__jtg_maps')
				->where($db->quoteName('id')." = ".$db->quote($mapid));
			$db->setQuery($query);
			$result = $db->loadAssoc();
		}
		if ($mapid == 0 || $result == null) {
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__jtg_maps')
				->order($db->quoteName('ordering'))
				->setLimit('1');
			$db->setQuery($query);
			$result = $db->loadAssoc();
		}
		return " jtgMapInit(".$result['type'].",'".$result['param']."','".$result['apikey']."');\n";
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
	static public function parseOverviewMapJS($items,$showtracks=false,$showLocationButton=true)
	{
		$cfg = JtgHelper::getConfig();

		// Need to set up map here
		$map = "\n<script type=\"text/javascript\">\n".
            "  jtgBaseUrl = \"".Uri::root()."\";\n".
            "  jtgTemplateUrl = \"".Uri::root()."components/com_jtg/assets/template/".$cfg->template."\";\n";
		$map .= JtgMapHelper::parseMapInitJS();
		if ($showLocationButton) {
			JFactory::getDocument()->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons'); // For geolocation/center icon
   		$map .= "	jtgMap.addControl(new ShowLocationControl());\n";
		}

		if ($showtracks)
		{
			// Slow when there are many tracks
			$map .= JtgMapHelper::parseTracksJS($items);
		}

		/*
		$file = JPATH_SITE . "/components/com_jtg/models/jtg.php";
		require_once $file;
		$this->sortedcats = JtgModeljtg::getCatsData(true);
		*/
		$retval = JtgMapHelper::parseTracksInfoJS($items);
		$tracksJS = $retval[0];
		$catIcons = $retval[1];
		$map .= "	tracks = [".implode(',',$retval[0])."];\n";
		$map .= "	catIcons = [".implode(',',$retval[1])."];\n";
		$map .= "	addTracksOverviewLayer(tracks, catIcons);\n";
		$map .= "</script>\n";
		return $map;
	}

	/**	
	 * get track and category info for overview map
	 *
	*/
	static private function parseTracksInfoJS($track_array) {
		$markersJS = array();
		$catIdx = array();
		$curCatId = -1;
		$curCatIdx = -1;
		$iCat = 0;
		foreach ( $track_array AS $row )
		{
			$url = JRoute::_("index.php?option=com_jtg&view=track&id=" . $row->id);
			$lon = $row->start_e;
			$lat = $row->start_n;
			$catids = explode(',',$row->catid);
			$catid = (int) $catids[0];
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

		$color = JtgMapHelper::calculateAllColors(count($rows));
		$string = "// <!-- parseOLTracks BEGIN -->\n";
		// MvL: TODO see whether we can keep the name and other options \"" . JText::_('COM_JTG_TRACKS') . "\", { displayInLayerSwitcher: true } );\n";
		//$string .= "olmap.addLayer(layer_vectors);\n";
		$i = 0;

		// TODO: the vectors are now added one by one instead of as layers with many vectors.
		foreach ($rows AS $row)
		{
			$file = JUri::base()."images/jtrackgallery/uploaded_tracks/" . $row->file;
			$filename = $file;
			// TODO: check file type; this code builds the overview map?
			$string .= "layer_vector = new ol.layer.Vector({";
			$string.="source: new ol.source.Vector({ url: '".$file."', format: new ol.format.GPX() }),\n";
			$string .= "   style: new ol.style.Style({ stroke: new ol.style.Stroke({ color:'" . JtgMapHelper::getHexColor("#" . $color[$i]) . "',\n width: 2}) })";
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
						$imagepath = $httppath . 'thumbs/thumb1_' . $image->filename;
					}
					else
					{
						// TODO recreate thumbnail if it does not exists (case direct FTP upload of images)
						$imagepath = $httppath . $image->filename;
					}

					$foundpics = true;
					$imginfo = getimagesize($folder.'/thumbs/thumb1_'.$image->filename);
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
					$imagehttp = "<img " . $size . " src=\"" . $imagepath . "\" alt=\"" . $image->filename . "\" title=\"" . $image->title . "\">";
					if (strlen($image->title)) $imagehttp .= "<p>".$image->title."</p>";
					$imgsJS[] = "{long: $image->lon, lat: $image->lat, imghtml : '$imagehttp' }";
            }
			}
			$imgJSarr = "geoImages = [".implode(',',$imgsJS)."];";
		}

		return $imgJSarr;
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
			$DPCalItem .= "    'title' : '".htmlentities($item->title)."',\n";
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
		$stringlength = 200;
		$maxslperrow = 50;

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
					// Strip additionnal (non <p>) tags, quote and return "1" wegen der Leerstelle
					$count_letters = ( $count_letters + strlen($word) + 1);

					// Einfügung von Zeilensprung BEGIN
					$rowlen = ( $rowlen + strlen($word) );

					if ( $rowlen > $maxslperrow )
					{
						$return = trim($return) . "<br />";
						$rowlen = 0;
					}

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
