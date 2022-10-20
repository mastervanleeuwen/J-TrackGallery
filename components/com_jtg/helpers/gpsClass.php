<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
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

/**
 * Mainclass to write the map
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class GpsDataClass
{
	var $gpsFile = null;

	var $sortedcats = null;

	// Array tracks[j]->coords; // array containing longitude latitude elevation time and heartbeat data
	var $track = array();

	var $speedDataExists = false;

	var $elevationDataExists  = false;

	var $beatDataExists = false;

	var $speedData = '';

	var $paceData = '';

	var $elevationData = '';

	var $beatData = '';

	var $error = false;

	var $errorMessages = array();

	var $trackCount = 0;

	var $wps = array();

	var $isTrack = false;

	var $isCache = false;

	var $isRoute = false;

	var $isWaypoint = false;

	var $isroundtrip = false;

	var $distance = 0;

	var $Date = false;

	var $trackname = "";

	var $fileChecked = false;

	var $description = "";

	const earthRadius = 6378.137;

	/**
	 * function_description
	 *
	 * @param   unknown_type  $unit  param_description
	 */
	public function __construct($unit)
	{
		$this->unit = $unit;
	}

	/**
	 * This function load and xml file if it exits
	 *
	 * @param   string  $file  the xml file (path) to load
	 *
	 * @return <boolean> gpsclass object
	 */
	public function loadFile($file)
	{
		if (file_exists($file))
		{
			$xml = simplexml_load_file($file);

			return $xml;
		}
		else
		{
			return false;
		}
	}

	/**
	 * function_description
	 *
	 * @param   string  $gpsFile        the gps file (path) to load
	 * @param   string  $trackfilename  track filename
	 *
	 * @return <boolean> gpsclass object
	 */
	public function loadFileAndData($gpsFile, $trackfilename)
	{
		// $xml can not belong to $this (SimpleXMLElement can not be serialized => not cached)

		$this->gpsFile = $gpsFile;
		$this->trackfilename = $trackfilename;

		$xml = $this->loadXmlFile($gpsFile);

		if ($this->error)
		{
			$this->fileChecked = 6;

			return $this;
		}

		if ($xml === false)
		{
			$this->error = true;
			$this->fileChecked = 6;
			$this->errorMessages[] = JText::sprintf('COM_JTG_GPS_FILE_ERROR_0', $this->trackfilename);

			return $this;
		}

		// Extract datas from xml
		switch ($this->ext)
		{
			case "gpx":
				$extract_result = $this->extractCoordsGPX($xml);
				unset($xml);
				break;
			case "kml":
				$extract_result = $this->extractCoordsKML($xml);
				break;
			case "tcx":
				$extract_result = $this->extractCoordsTCX($xml);
				break;
			default:
				$extract_result = null;
				$this->error = true;
				$this->errorMessages[] = JText::_('COM_JTG_GPS_FILE_ERROR');

				return $this;
		}

		if ($this->trackCount == 0 && $this->routeCount == 0)
		{
			$this->fileChecked = 7;
			$this->error = true;
			$this->errorMessages[] = JText::sprintf('COM_JTG_GPS_FILE_ERROR_2', $this->trackfilename);

			return $this;
		}

		// Calculate start
		if ($this->trackCount) 
			$this->start = $this->track[0]->coords[0][0];
		else
			$this->start = $this->route[0]->coords[0];
	
		$this->speedDataExists = ( ( isset ($this->start[3])  && $this->start[3] > 0) ? true: false);
		$this->elevationDataExists = ( isset ($this->start[2])? true: false);
		$this->beatDataExists = ( (isset ($this->start[4]) && $this->start[4] > 0)? true: false);

		// Calculate allCoords, distance, elevation max lon...
		$this->extractAllTracksCoords();

		// Calculate chartData
		$this->createChartData();

		// TODO include WP in new function extractCoordsGPX
		$this->extractWPs();

		$this->fileChecked = true;

		return $this;
	}

	/**
	 * function_description
	 *
	 * @param   string  $gpsFile  the gps file to load
	 *
	 * @return <simplexmlelement> if file exists and is loaded , null otherwise
	 */
	public function loadXmlFile($gpsFile=false)
	{
		jimport('joomla.filesystem.file');
		$xml = false;

		if ( ($gpsFile) and (JFile::exists($gpsFile)) )
		{
			$this->gpsFile = $gpsFile;
			$this->ext = JFile::getExt($gpsFile);
		}
		elseif  (JFile::exists($this->gpsFile))
		{
			// $this->gpsFile = $gpsFile;
			$this->ext = JFile::getExt($this->gpsFile);
		}
		else
		{
			$this->error = true;
			$this->errorMessages[] = JText::sprintf('COM_JTG_GPS_FILE_ERROR_1', ($this->trackfilename?  $this->trackfilename: $gpsFile));

			return false;
		}

		// Enable user error handling
		libxml_use_internal_errors(true);
		libxml_clear_errors();

		if ($this->ext == 'gpx')
		{
			// Open (don't load) GPX xml files using XMLReader
			$xml = new XMLReader;
			$xml->open($this->gpsFile);
		}
		else
		{
			// Load KML and TCX xml files using simplexml_load_file
			$xml = simplexml_load_file($this->gpsFile);
		}

		if ($xml === false)
		{
			// "Failed loading XML\n";

			$this->error = true;

			foreach (libxml_get_errors() as $error)
			{
				switch ($error->level)
				{
					case LIBXML_ERR_WARNING:
						$this->errorMessages[] = "Warning $error->code: ";
						break;
					case LIBXML_ERR_ERROR:
						$this->errorMessages[] = "Error $error->code: ";
						break;
					case LIBXML_ERR_FATAL:
						$this->errorMessages[] = "Fatal Error $error->code: ";
						break;
				}

				$this->errorMessages[] = trim($error->message) .
				"\n  Line: $error->line" .
				"\n  Column: $error->column";
			}
		}

		return $xml;
	}

	/**
	 * function_description
	 *
	 * @return void
	 */
	public function displayErrors()
	{
		$error = "";

		foreach ($this->errorMessages as $errorMessage)
		{
			JFactory::getApplication()->enqueueMessage($errorMessage, 'Warning');
			$error .= '\n' . $errorMessage;
		}

		return $error;
	}

	/**
	 * function_description
	 *
	 * @param   string  $xml  track xml object
	 *
	 * @return array
	 */
	private function extractCoordsKML($xml)
	{
		// TODO use XMLReader
		$xmldom = new DOMDocument;
		$xmldom->loadXML($xml->asXML());

		$rootNamespace = $xmldom->lookupNamespaceUri($xmldom->namespaceURI);
		$xpath = new DomXPath($xmldom);
		$xpath->registerNamespace('kml', $rootNamespace);

		$documentNodes = $xpath->query('kml:Document/kml:name|kml:Document/kml:description');
		$gps_file_name = '';
		$gps_file_description = '';

		// Search for NAME (Title) and description of GPS file
		foreach ($documentNodes as $documentNode)
		{
			switch ($documentNode->nodeName)
			{
				case 'name':
					$gps_file_name .= preg_replace('/<!\[CDATA\[(.*?)\]\]>/s', '', $documentNode->nodeValue);
					break;
				case 'description':
					$gps_file_description .= preg_replace('/<!\[CDATA\[(.*?)\]\]>/s', '', $documentNode->nodeValue);
					break;
			}
		}

		// Search for tracks (name (title), description and coordinates
		$placemarkNodes = $xpath->query('//kml:Placemark');
		$this->trackCount = 0;
		$tracks_description = '';
		$track_name = '';

		foreach ($placemarkNodes as $placemarkNode)
		{
			$nodes = $xpath->query('.//kml:name|.//kml:description|.//kml:LineString|.//kml:coordinates', $placemarkNode);

			if ($nodes)
			{
				$found_linestring = false;
				$name = '';
				$description = '';
				$coordinates = null;

				foreach ($nodes as $node)
				{
					switch ($node->nodeName)
					{
						case 'name':
							$name = $node->nodeValue;
							break;
						case 'description':
							$description = $node->nodeValue;
							$tracks_description .= $description;
							$description = ( ($description = '&nbsp;')? '' : $description);
							break;
						case 'LineString':
							$found_linestring = true;
							break;
						case 'coordinates':
							// Exploit coordinates only when it is a child of LineString

							if ($found_linestring)
							{
								$coordinates = $this->extractKmlCoordinates($node->nodeValue);

								if ($coordinates)
								{
									$coordinatesCount = count($coordinates);
									$this->track[$this->trackCount] = new stdClass;
									$this->track[$this->trackCount]->coords[] = $coordinates;
									$this->track[$this->trackCount]->segCount = 1;
									$this->track[$this->trackCount]->trackname = ($name? $name : $description);
									$this->track[$this->trackCount]->description = $description;
									$this->trackCount++;
								}
							}
							break;
					}
				}
			}

			// Use description and name for file description
			if ($name OR $description)
			{
				$gps_file_description .= '<br />' . $name . ':' . $description;
			}
		}

		if ($this->trackCount)
		{
			// GPS file name (title) and description
			$this->trackname = $gps_file_name;

			if ( strlen($gps_file_name) > 2)
			{
				$this->trackname = $gps_file_name;
			}

			if ( ( strlen($this->trackname) < 10 ) AND ($this->trackCount == 1))
			{
				$this->trackname .= $this->track[0]->trackname;
			}

			if ( strlen($this->trackname) < 10 )
			{
				$this->trackname .= $this->gpsFile;
			}

			if (($gps_file_description) AND ($tracks_description))
			{
				$this->description = $gps_file_description . '<br />' . $tracks_description;
			}
			elseif ($tracks_description)
			{
				$this->description = $tracks_description;
			}
			else
			{
				$this->description = $this->trackname;
			}

			$this->isTrack = ($this->trackCount > 0);
			$this->isCache = $this->isThisCache($xml);
		}
		// Nothing to return
		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $xml  param_description
	 *
	 * @return return_description
	 */
	public function isThisCache($xml)
	{
		$pattern = "/groundspeak/";

		if ( preg_match($pattern, $xml->attributes()->creator))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $coord_sets  param_description
	 *
	 * @return return_description
	 */
	private function extractKmlCoordinates($coord_sets)
	{
		$coordinates = array();

		if ($coord_sets)
		{
			$coord_sets = str_replace("\n", '/', $coord_sets);
			$coord_sets = str_replace(" ", '/', $coord_sets);
			$coord_sets = explode('/', $coord_sets);

			foreach ($coord_sets as $set_string)
			{
				$set_string = trim($set_string);

				if ($set_string)
				{
					$set_array = explode(',', $set_string);
					$set_size = count($set_array);

					if ($set_size == 2)
					{
						array_push($coordinates, array($set_array[0],$set_array[1], 0, 0, 0));
					}
					elseif ($set_size == 3)
					{
						array_push($coordinates, array($set_array[0],$set_array[1],$set_array[2], 0, 0));
					}
				}
			}
			// Suppress coordinates set with less than 5 points

			if (count($coordinates) < 5)
			{
				$coordinates = null;
			}
		}

		return $coordinates;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $xmlcontents  XMLreader object
	 *
	 * @return return_description
	 */

	private function extractCoordsGPX($xmlcontents)
	{
		$this->trackname = '';
		$this->trackCount = 0;
		$this->routeCount = 0;
		$countElements = 0;
		$i_wpt = 0;
		$i_trk = 0;

		// Iterate nodes
		while ($xmlcontents->read() )
		{
			// Check to ensure nodeType is an Element not attribute or #Text
			if ($xmlcontents->nodeType == XMLReader::ELEMENT)
			{
				// Start element found
				$currentElement = $xmlcontents->localName;
				$endElement = '';
				$countElements++;

				switch ($currentElement)
				{
					case 'time':
						// GPS file Time
						$xmlcontents->read();
						$time = $xmlcontents->value;
						if ($time) {
							$dt = new DateTime($time);
							$this->Date = $dt->format('Y-m-d');
						}
						// Read end tag
						$xmlcontents->read();
						break;
					case 'name':
						$xmlcontents->read();
						$this->trackname = $xmlcontents->value;
						// Read end tag
						$xmlcontents->read();
						break;
					case 'wpt':
						$endWptElement = $xmlcontents->isEmptyElement;

						if (!$endWptElement) { // Skip waypoints with only lat, lon info
							$i_wpt++;
							$this->wps[$i_wpt] = new WpClass;
							$lat = (float) $xmlcontents->getAttribute('lat');
							$lon = (float) $xmlcontents->getAttribute('lon');
							$this->wps[$i_wpt]->sym = 'wp';
							$this->wps[$i_wpt]->lat = $lat;
							$this->wps[$i_wpt]->lon = $lon;
						}

						$readok = true;
						while ( $readok && !$endWptElement )
						{
							$readok = $xmlcontents->read();

							if ($xmlcontents->nodeType == XMLReader::END_ELEMENT)
							{
								$endWptElement = ($xmlcontents->localName == 'wpt');
							}
							else
							{
								$endWptElement = false;
							}

							// Extract wpt attributes
							if ($xmlcontents->nodeType == XMLReader::ELEMENT)
							{
								$key = $xmlcontents->localName;
								$readok = $xmlcontents->read();
								$value = $xmlcontents->value;
								$this->wps[$i_wpt]->$key = $value;
								$readok = $xmlcontents->read();
							}
						}
						break;

					case 'trk':
						// Track
						$i_trk++;

						$curTrack = new stdClass;
						$curTrack->description = '';
						$curTrack->trackname = '';
						$curTrack->segCount = 0;
						while ( ('trk' !== $endElement) )
						{
							$xmlcontents->read();

							if ($xmlcontents->nodeType == XMLReader::END_ELEMENT)
							{
								// </xxx> found
								$endElement = $xmlcontents->localName;
							}
							else
							{
								$endElement = '';
							}
							// Extract trk data
							if ( ($xmlcontents->name == 'name') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
							{
								$xmlcontents->read();
								$curTrack->trackname = $xmlcontents->value;

								// Read end tag
								$xmlcontents->read();
							}
							elseif ( ($xmlcontents->name == 'trkseg') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
							{
								// Trkseg found
								$endTrksegElement = false;
								$coords = array();
								$tracksegname = '';
								$i_trkpt = 0;
								$ele = 0;
								$time = '0';
								$readok = true;

								while ( $readok && !$endTrksegElement )
								{
									$readok = $xmlcontents->read();

									if ($xmlcontents->nodeType == XMLReader::END_ELEMENT)
									{
										$endTrksegElement = ($xmlcontents->localName == 'trkseg');
									}
									else
									{
										$endTrksegElement = false;
									}

									if ( ($xmlcontents->name == 'trkpt') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
									{
										// Trkpt found

										$i_trkpt++;
										$lat = (float) $xmlcontents->getAttribute('lat');
										$lon = (float) $xmlcontents->getAttribute('lon');
									}

									if ( ($xmlcontents->name == 'ele') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
									{
										// Trkpt elevation found
										$readok = $xmlcontents->read();
										$ele = (float) $xmlcontents->value;

										// Read end tag
										$readok = $xmlcontents->read();
									}

									if ( ($xmlcontents->name == 'time') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
									{
										// Trkpt time found
										$readok = $xmlcontents->read();
										$time = (string) $xmlcontents->value;
										if ($this->Date === false && $time) {
											$dt = new DateTime($time);
											$this->Date = $dt->format('Y-m-d');
										}
										// Read end tag
										$readok = $xmlcontents->read();
									}

									// set other elements a la waypoint? (cmt, desc, sym)
									if ( ($xmlcontents->name == 'trkpt') AND ($xmlcontents->nodeType == XMLReader::END_ELEMENT) )
									{
										// End Trkpt
										$coords[] = array((string) $lon, (string) $lat, (string) $ele, (string) $time, 0);
									}
								}

								// End trkseg

								$endTrksegElement = true;
								$coordinatesCount = count($coords);

								if ($coordinatesCount > 1 )
								{
									// This is a track with more than 2 points
									$this->isTrack = true;

									$curTrack->coords[] = $coords;
									$curTrack->segCount++;
								}
							}
						}
						if ($curTrack->segCount != 0) {
							$this->track[] = $curTrack;
							$this->trackCount++;
						}
						break;
					case 'rte':
						// Route
						$trackname = '';
						$i_trk++;
						$coords = array();
					 	$i_trkpt = 0;
						$ele = 0;
						$time = '0';

						$readok = true;
						while ( $readok && ('rte' !== $endElement) )
						{
							$readok = $xmlcontents->read();

							if ($xmlcontents->nodeType == XMLReader::END_ELEMENT)
							{
								// </xxx> found
								$endElement = $xmlcontents->localName;
							}
							else
							{
								$endElement = '';
							}
							// Extract rte data
							if ( ($xmlcontents->name == 'name') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
							{
								$readok = $xmlcontents->read();
								$trackname = $xmlcontents->value;

								// Read end tag
								$readok = $xmlcontents->read();
							}
							if ( ($xmlcontents->name == 'rtept') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
							{
								// Rtept found
								// Add to trkseg for line drawing and as waypoints

								$curWpt = new WpClass;
								$lat = (float) $xmlcontents->getAttribute('lat');
								$lon = (float) $xmlcontents->getAttribute('lon');
								$curWpt->lat = $lat;
								$curWpt->lon = $lon;

								$lat = (float) $xmlcontents->getAttribute('lat');
								$lon = (float) $xmlcontents->getAttribute('lon');

								// Read end tag
								$readok = $xmlcontents->read();

								$endRoutePoint = false;
								$extensionsFound  = false;
								while ($readok && !$endRoutePoint)
								{
								   if ( ($xmlcontents->name == 'ele') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
								   {
								      // rtept elevation found
							   	   $readok = $xmlcontents->read();
							      	$ele = (float) $xmlcontents->value;
								      $curWpt->ele = $ele;
								      // Read end tag
								      $readok = $xmlcontents->read();
								   }

								   if ( ($xmlcontents->name == 'time') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
								   {
										// rtept time found
										$readok = $xmlcontents->read();
										$time = (string) $xmlcontents->value;
										$curWpt->time = $time;
										// Read end tag
										$readok = $xmlcontents->read();
								   }
								   if ( ($xmlcontents->name == 'extensions') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) ) {
								      // Skip extensions, but push via/shaping point
								      $extensionsFound = true;
								      while ( !(($xmlcontents->name == 'extensions') AND ($xmlcontents->nodeType == XMLReader::END_ELEMENT))) {
								      if ( ($xmlcontents->name == 'gpxx:rpt') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
										{	
											$latsub = (float) $xmlcontents->getAttribute('lat');
											$lonsub = (float) $xmlcontents->getAttribute('lon');
											$coords[] = array((string) $lonsub, (string) $latsub, (string) $ele, (string) $time, 0);
										}
								      $readok = $xmlcontents->read();
									}
							   }	

							   if ( ($xmlcontents->name != 'time') AND ($xmlcontents->name != 'ele') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) ) {
							      $key = $xmlcontents->localName;
						   	   $readok = $xmlcontents->read();
						      	$value = $xmlcontents->value;
							      $curWpt->$key = $value;
							      $readok = $xmlcontents->read();
							   }
							   if ( ($xmlcontents->name == 'rtept') AND ($xmlcontents->nodeType == XMLReader::END_ELEMENT) )
							   {
							      // End Rtept
							      if (!$extensionsFound) 
									{
						      	   $coords[] = array((string) $lon, (string) $lat, (string) $ele, (string) $time, 0);
										$i_trkpt++;
									}
									if (isset($curWpt->name)) {
										$this->wps[$i_wpt] = $curWpt;
										$i_wpt++;
									}
							      $endRoutePoint = true;
						   	}
							   if ( !$endRoutePoint ) $readok = $xmlcontents->read();
							}
						}
					}
					$coordinatesCount = count($coords);

					if ($coordinatesCount > 1 )
					{
						// This is a route segment with 2 or more points
						$this->isRoute = true;
						$this->route[$this->trackCount] = new stdClass;
						$this->route[$this->trackCount]->description = '';
						if ($trackname != '')
						{
							$this->route[$this->routeCount]->trackname = $trackname;
							if (strlen($this->trackname) == 0) $this->trackname = $trackname;
						}
						else
						{
							$this->route[$this->routeCount]->trackname = $this->trackfilename . '-' . (string) $this->trackCount;
						}

						$this->route[$this->routeCount]->coords = $coords;
						$this->routeCount++;
					}
					
					break;
				}
			}
		}

		if (strlen($this->trackname) == 0)
		{
			if ($this->trackCount)
			{
				if ($this->track[0]->trackname != '')
				{
					$this->trackname = $this->track[0]->trackname;
				}
				else
				{
					$this->trackname = $this->trackfilename;
				}
			}
			else
			{
				$this->trackname = $this->trackfilename;
			}
		}

		if (!$this->description)
		{
			if ($this->trackCount == 1)
			{
				$this->description = strlen($this->track[0]->description)? $this->track[0]->description: '';
			}
			elseif ($this->trackCount > 1)
			{
				$this->description = strlen($this->track[0]->description)? $this->track[0]->description: '';

				for ($i = 1; $i < $this->trackCount; $i++)
				{
					if (strlen($this->track[$i]->description)) {
						$this->description .= '<br>' . $this->track[$i]->description;
					}
				}
			}
		}

		$xmlcontents->close();

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   string   $file     param_description
	 * @param   integer  $trackid  track id
	 *
	 * @return array
	 */
	private function getCoordsTCX($file,$trackid=0)
	{
		// TODO REWRITE TCX FILE NOT YET SUPPORTED
		$this->error = true;
		$this->errorMessages[] = " ERROR TCX file not yet supported";

		return false;

		if (file_exists($file))
		{
			$xml = simplexml_load_file($file);

			if (isset($xml->Activities->Activity->Lap->Track))
			{
				$startpoint = $xml->Activities->Activity->Lap->Track[$trackid];
			}
			elseif (isset($xml->Courses->Course->Track))
			{
				$startpoint = $xml->Courses->Course->Track[$trackid];
			}

			$coords = array();

			if (!$startpoint[0])
			{
				return false;
			}

			foreach ($startpoint[0] as $start)
			{
				if (isset($start->Position->LatitudeDegrees) && isset($start->Position->LongitudeDegrees))
				{
					$lat = $start->Position->LatitudeDegrees;
					$lon = $start->Position->LongitudeDegrees;
					$ele = $start->AltitudeMeters;
					$time = $start->Time;

					if (isset($start->HeartRateBpm->Value))
					{
						$heart = $start->HeartRateBpm->Value;
					}
					else
					{
						$heart = "0";
					}

					$bak = array((string) $lon, (string) $lat, (string) $ele, (string) $time, (string) $heart);
					array_push($coords, $bak);
				}
			}

			return $coords;
		}
		else
		{
			return false;
		}
	}

	/**
	 *  addTrackCoords add track (segment) coordinates, elevation to global array 
    *     and calculate speed etc
	 *
	 *  $coords Array of coordinates (5 elements per row)
	*/
	private function addTrackCoords($coords)
	{
		// TODO: move this to initialisation ?
		$params = JComponentHelper::getParams('com_jtg');

		$filterMinAscent = (float) $params->get('jtg_param_elevation_filter_min_ascent');
		$filterMinAscent = max(0, $filterMinAscent);
		$this->allCoords = array_merge($this->allCoords, $coords);

		// Calculate distances
		$next_coord = $coords[0];
		$next_lat_rad = deg2rad($next_coord[1]);
		$next_lon_rad = deg2rad($next_coord[0]);

		if ($this->elevationDataExists)
		{
			$current_elv = $next_coord[2];
			$this->allElevation[] = (int) $current_elv;
		}

		if ($this->beatDataExists)
		{
			$this->allBeat[] = $next_coord[4];
		}

		if ($this->speedDataExists)
		{
			$next_time = $this->giveTimestamp($next_coord[3]);
		}

		$datacount = count($coords);
		$curCoordIdx = count($this->allDistances)-1;
		if ($curCoordIdx > 1)
		{
			$this->allDistances[] = $this->allDistances[$curCoordIdx];
			$curCoordIdx++;
		}

		// TODO: deal gracefully with cases where a time is missing...
		for ($i = 0; $i < $datacount - 1; $i++)
		{
			$next_coord = $coords[$i + 1];

			if (isset($next_coord))
			{
				$current_lat_rad = $next_lat_rad;
				$current_lon_rad = $next_lon_rad;

				$next_lat_rad = deg2rad($next_coord[1]);
				$next_lon_rad = deg2rad($next_coord[0]);

				// Distance in kilometer

				$dis = acos(
						(sin($current_lat_rad) * sin($next_lat_rad)) +
						(cos($current_lat_rad) * cos($next_lat_rad) *
								cos($next_lon_rad - $current_lon_rad))
						) * self::earthRadius;

				if (is_nan($dis))
				{
					$dis = 0;
				}

				$this->allDistances[] = $this->allDistances[$curCoordIdx] + $dis;

				if ($this->elevationDataExists)
				{
					$next_elv = $next_coord[2];
					$this->allElevation[] = (int) $next_elv;
					$ascent = $next_elv - $current_elv;

					/* elevationFilterOK is true when
					 * $filterMinAscent = 0 (no filtering)
					 * abs(ascent) is more then filterMinAscent
					 * the data point is the last of the given track
					 */
					$elevationFilterOK = ( ($filterMinAscent == 0) OR (abs($ascent) > $filterMinAscent) OR ($i == $datacount - 2) );

					if ($elevationFilterOK)
					{
						// Elevation data can be added to total ascent and descent
						$current_elv = $next_elv;

						if ($ascent >= 0)
						{
							$this->totalAscent = $this->totalAscent + $ascent;
						}
						else
						{
							$this->totalDescent = $this->totalDescent - $ascent;
						}
					}
				}

				// Speed
				if ($this->speedDataExists)
				{
					$current_time  = $next_time;
					$next_time = $this->giveTimestamp($next_coord[3]);

					$curSpeed = 0;
					if ($current_time and $next_time)
					{
						$elapsedTime = $next_time - $current_time;

						if ($elapsedTime > 0)
						{
							$curSpeed = $dis / $elapsedTime * 3600;
							$this->allSpeed[] = $curSpeed;
						}
						else
						{
							$this->allSpeed[] = 0;
						}
					}
					else
					{
						$this->allSpeed[] = 0;
					}
					if ($i == 0) $this->allSpeed[] = $curSpeed; // Use same speed for 1st and second point of track
				}

				// Heart Beat
				if ($this->beatDataExists)
				{
					$this->allBeat[] = $next_coord[4];
				}
				$curCoordIdx++;
			}
		}	
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	private function extractAllTracksCoords()
	{
		$this->allCoords = array();
		$this->allDistances = array();
		$this->totalAscent = 0;
		$this->totalDescent = 0;
		$d = 0;
		$this->allDistances[0] = 0;
		/*
		 if ( strtolower($this->unit) == "kilometer" )
		 {
		$earthRadius = 6378.137;
		}
		else
		{
		$earthRadius = 6378.137/1.609344;
		}
		*/
		$earthRadius = 6378.137;

		for ($t = 0; $t < $this->trackCount; $t++)
		{
			for ($s = 0; $s < $this->track[$t]->segCount; $s++)
			{
				$this->addTrackCoords($this->track[$t]->coords[$s]);
			}
		}

		if ($d == 0 && $this->routeCount != 0)  // TODO: check whether more than 1 route is possible
		{
			$this->addTrackCoords($this->route[0]->coords);
		}

		$this->distance = $this->allDistances[count($this->allDistances)-1];

		if ($this->elevationDataExists)
		{
			$this->totalAscent = (int) $this->totalAscent;
			$this->totalDescent = (int) $this->totalDescent;
		}

		if ( ( $this->totalAscent == 0 ) and ($this->totalDescent == 0) )
		{
			$this->elevationDataExists = false;
		}

		// Is this track a roundtrip ?
		if ($this->trackCount)
		{
			$t = $this->trackCount-1;
			$first_coord = $this->track[0]->coords[0][0];
			$s = $this->track[$t]->segCount-1;
			$n = count($this->track[$t]->coords[$s]);
			$last_coord = $this->track[$t]->coords[$s][$n-1];
		}
		else
		{
			$first_coord = $this->route[0]->coords[0];
			$r = $this->routeCount-1; // TODO: check whether only 1 route allowed per GPX file?
			$n = count($this->route[$r]->coords)-1;
			$last_coord = $this->route[$r]->coords[$n];
		}

		$first_lat_rad = deg2rad($first_coord[1]);
		$first_lon_rad = deg2rad($first_coord[0]);
		$last_lat_rad = deg2rad($last_coord[1]);
		$last_lon_rad = deg2rad($last_coord[0]);

		// Calculate distances in km
		$earthRadius = 6378.137;
		$dis_first_to_last = acos(
				(sin($last_lat_rad) * sin($first_lat_rad)) +
				(cos($last_lat_rad) * cos($first_lat_rad) *
						cos($first_lon_rad - $last_lon_rad))
				) * $earthRadius;

		if (is_nan($dis_first_to_last))
		{
			$dis_first_to_last = 0;
		}
		if (($dis_first_to_last < 0.2) OR ( $dis_first_to_last < $this->distance/50) )
		{
			$this->isroundtrip = true;
		}
		else
		{
			$this->isroundtrip = false;
		}

		return;
	}

	/**
	 * function_description
	 *
	 * @param   string  $date  param_description
	 *
	 * @return (int) timestamp
	 */
	public function giveTimestamp($date)
	{
		// ToDo: unterschiedliche Zeittypen können hier eingefügt werden
		if ( $date == 0 )
		{
			return false;
		}

		$date = explode('T', $date);
		$time_tmp_date = explode('-', $date[0]);
		$time_tmp_date_year = $time_tmp_date[0];
		$time_tmp_date_month = $time_tmp_date[1];
		$time_tmp_date_day = $time_tmp_date[2];
		$time_tmp_time = explode(':', str_replace("Z", "", $date[1]));
		$time_tmp_time_hour = $time_tmp_time[0];
		$time_tmp_time_minute = $time_tmp_time[1];
		$time_tmp_time_sec = (int) round($time_tmp_time[2], 0);

		return mktime(
				$time_tmp_time_hour, $time_tmp_time_minute, $time_tmp_time_sec,
				$time_tmp_date_month, $time_tmp_date_day, $time_tmp_date_year
		);
	}

	/**
	 * function_description
	 *
	 * @return (int) Anzahl
	 */
	public function extractWPs()
	{

		if (empty($this->wps))
		{
			$this->isWaypoint = false;
			$this->wps = null;

			return false;
		}

		$this->isWaypoint = true;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function createChartData()
	{
		$elevationChartData = "";
		$beatChartData = "";
		$speedChartData = "";
		$paceChartData = "";
		$longitudeChartData = "";
		$latitudeChartData = "";

		$cfg = JtgHelper::getConfig();
		$n = count($this->allDistances);

		/*
		* Adjust max number of points to display on tracks (maxTrkptDisplay)
		* $c is the step for scanning allDistances/speed and others datas
		* $width is half the width over which speed data are smoothed
		* Smoothed speed is average from $i-$witdh<=index<=$i+$width
		*/
		if ( ($cfg->maxTrkptDisplay > 0) AND ($n > $cfg->maxTrkptDisplay))
		{
			$c = $n / $cfg->maxTrkptDisplay / 2;
			$c = round($c, 0);
			$width = 2 * $c;
		}
		else
		{
			$c = 1;
			$width = 2;
		}

		for ($i = 0; $i < $n; $i = $i + $c)
		{
			$distance = (string) round($this->allDistances[$i], 2);
			$i2 = max($i - $width, 1);
			$i3 = min($i + $width, $n - 1);
			$longitudeChartData .= $this->allCoords[$i][0] . ',';
			$latitudeChartData .= $this->allCoords[$i][1] . ',';

			if ($this->speedDataExists)
			{
				// $speedChartData .= '[' . $distance  . ',' . round($this->allSpeed[$i2],1) . '],' ;
				// Calculate average speed (smoothing)
				$speed = 0;

				for ($j = $i2; $j <= $i3; $j++)
				{
					$speed = $speed + $this->allSpeed[$j];
				}

				$speed = $speed / ($i3 - $i2 + 1);

				// Pace is limited for low speed $pace <=60 min/km or min/miles
				$pace = 60/max($speed,1);
				$speedChartData .= '[' . $distance . ',' . round($speed, 1) . '],';
				$paceChartData .= '[' . $distance . ',' . round($pace, 1) . '],';
			}

			if ($this->elevationDataExists)
			{
				$elevationChartData .= '[' . $distance . ',' . round($this->allElevation[$i], 0) . '],';
			}

			if ($this->beatDataExists)
			{
				$beatChartData .= '[' . $distance . ',' . round($this->allBeat[$i2], 0) . '],';
			}
		}

		$this->longitudeData = '[' . substr($longitudeChartData, 0, -1) . ']';
		$this->latitudeData = '[' . substr($latitudeChartData, 0, -1) . ']';

		if ($this->speedDataExists)
		{
			$this->speedData = '[' . substr($speedChartData, 0, -1) . ']';
			$this->paceData = '[' . substr($paceChartData, 0, -1) . ']';
		}

		if ($this->elevationDataExists)
		{
			$this->elevationData = '[' . substr($elevationChartData, 0, -1) . ']';
		}

		if ($this->beatDataExists)
		{
			$this->beatData = '[' . substr($beatChartData, 0, -1) . ']';
		}

		return;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $ownicon  param_description
	 *
	 * @return (int) Anzahl
	 */
	public function parseOwnIcon($ownicon=false)
	{
		$cfg = JtgHelper::getConfig();
		$Tpath = JPATH_SITE . "/components/com_jtg/assets/template/" . $cfg->template . "/images/";
		$Tbase = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";
		$unknownicon = "";
		$jpath = JPATH_SITE . "/components/com_jtg/assets/images/symbols/";
		$jbase = JUri::root() . "components/com_jtg/assets/images/symbols/";

		$filename = JFile::makeSafe($ownicon);
		$pngfile = $jbase . $filename . ".png";
		$xmlfile = $jpath . $filename . ".xml";

		if ( $ownicon == false )
		{
			if ((!JFile::exists($xmlfile)) AND (is_writable($jpath)))
			{
				// Vorlage zur Erstellung unbekannter Icons
				$xmlcontent = "<xml>\n	<sizex>16</sizex>\n	<sizey>16</sizey>\n	<offsetx>8</offsetx>\n	<offsety>8</offsety>\n</xml>\n<!--\nUm dieses Icon verfügbar zu machen, erstelle dieses Bild: \"" . $filename . ".png\",\nund vervollständige obige 4 Parameter.\n\"offsetx\" beschreibt die Anzahl der Pixel von links bis zum Punkt (negativ) und\n\"offsety\" beschreibt die Anzahl der Pixel von oben bis zum Punkt (ebenfalls negativ).\nMit \"Punkt\" ist der Punkt gemeint, der auf der Koordinate sitzt.\n-->\n";
				JFile::write($xmlfile, $xmlcontent);
				JPath::setPermissions($xmlfile, "0666");
			}
			// Standardicon
			$pngfile = $Tbase . "unknown_WP.png";
			$xmlfile = $Tpath . "unknown_WP.xml";
			$unknownicon = "// Unknown Icon: \"" . $jpath . $ownicon . ".png\"\n";
		}

		if ( ($ownicon == false) OR (is_file($jpath . $filename . '.png')) ) {
			$icon = $pngfile;
			$xml = $this->loadFile($xmlfile);
			if ($xml === false) {
				echo "Error loading icon xml file: $xmlfile<br>\n";
			}
			else {
				$sizex = $xml->sizex;
				$sizey = $xml->sizey;
				$offsetx = -$xml->offsetx;
				$offsety = -$xml->offsety;
			}
			return "new ol.style.Icon({src: '" . $icon . "',\n			size: [" . $sizex . ", " . $sizey . "],\n			anchorXUnits: 'pixels', anchorYUnits: 'pixels',\n		 anchor: [" . $offsetx . ", " . $offsety . "]})";
		}
		return false;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $wp  param_description
	 *
	 * @return return_description
	 */
	public function isGeocache($wp)
	{
		if ( ( isset($wp->sym) ) AND ( preg_match('/Geocache/', $wp->sym) ) AND ( isset($wp->type) ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $wp  param_description
	 *
	 * @return return_description
	 */
	public function hasURL($wp)
	{
		if ( ( isset($wp->url) ) AND ( isset($wp->urlname) ) )
		{
			return true;
		}

		return false;
	}

	/**
	 * function_description
	 *
	 * @return (int) Anzahl
	 */
	public function parseWPs()
	{
		if ( empty($this->wps))
		{
			return false;
		}

		$wpJSarr = array();
		$wpJSicons = [ 'unknown' => $this->parseOwnIcon() ];
		foreach ($this->wps as $wp)
		{
			$replace = array("\n","'");
			$with = array("<br />","\'");
			$hasURL = isset($wp->url); // TODO: this should probably be 'link'
			$isGeocache = $this->isGeocache($wp->value);

			if ($hasURL)
			{
				$URL = " <a href=\"" . $wp->url . "\" target=\"_blank\">" .
						trim(str_replace($replace, $with, $wp->urlname)) . "</a>";
			}
			else
			{
				$URL = "";
			}

			$name = trim(str_replace($replace, $with, $wp->name));
			$cmt = trim(str_replace($replace, $with, $wp->cmt));
			$desc = trim(str_replace($replace, $with, $wp->desc));
			$ele = (float) $wp->ele;

			if ($isGeocache)
			{
				$sym = (string) $wp->type;
			}
			else
			{
				$sym = $wp->sym;
			}

			$wpcode = $name . $URL;
			if ($desc)
			{
				$wpcode .= "<br><b>" . JText::_('COM_JTG_DESCRIPTION') . ":</b> " . $desc;
			}

			if ( ($cmt) AND ($desc != $cmt) )
			{
				$wpcode .= "<br /><b>" . JText::_('COM_JTG_COMMENT') . ":</b> " . $cmt;
			}

			if ($ele)
			{
				// TODO unit in elevation !!
				$wpcode .= "<br /><b>" . JText::_('COM_JTG_ELEVATION') . " :</b> " . round($ele, 1) . "m<small>";
			}
			
			if (isset($wpJSicons[$sym])) {
				$iconname = $sym;
			}
			else if (($iconJS = $this->parseOwnIcon($sym))) {
				$wpJSicons[$sym] = $iconJS;
				$iconname = $sym;
			}
			else {
				$iconname = 'unknown';
			}
			$wpJSarr[] = "{ lon: $wp->lon, lat: $wp->lat, icon: '$iconname', html: '$wpcode' }";
		}
		$wpcode = "   var wpInfo = [ ".implode(',',$wpJSarr)." ];\n";
		$wpcode .= "   var wpIcons = new Map();\n";
		foreach ($wpJSicons as $key => $code) {
			$wpcode.= "    wpIcons.set('$key', $code);\n";
		}
		$wpcode .= "   addWPs(wpInfo, wpIcons);\n";

		return $wpcode;
	}

	/**
	 * function_description
	 *
	 * @param   object  $rows  rows
	 *
	 * @return array()
	 */
	public function maySee($rows)
	{
		if (!$rows)
		{
			return false;
		}

		$user = JFactory::getUser();
		$return = array();

		foreach ( $rows AS $row )
		{
			if (( (int) $row->published )
				AND ( ( !$row->access )
				OR ( ( $row->access )
				AND ( ( isset( $user->userid ) AND ( $user->userid ) )
				OR ( isset( $user->id ) AND ( $user->id ) ) ) ) ) )
			{
				$return[] = $row;
			}
		}

		return $return;
	}

	/**
	 * Löscht den aktuellen Track aus der
	 * Gesamtansicht
	 *
	 * @param   unknown_type  $rows   param_description
	 * @param   unknown_type  $track  param_description
	 *
	 * @return array()
	 */
	public function deleteTrack($rows, $track)
	{
		foreach ( $track AS $key => $value )
		{
			// Track-ID herausfinden und Schleife verlassen
			$trackid = $value;
			break;
		}

		$return = array();

		foreach ( $rows AS $key => $value )
		{
			foreach ( $value AS $key_b => $value_b )
			{
				if ( $value_b != $trackid )
				{
					$store = true;
				}
				else
				{
					$store = false;
				}

				break;
			}

			if ( $store == true )
			{
				$return[] = $value;
			}
		}

		return $return;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	private function getStartTCX()
	{
		$xml = $this->loadFile();

		if (isset($xml->Activities->Activity->Lap->Track))
		{
			$startpoint = $xml->Activities->Activity->Lap->Track[0]->Trackpoint;
		}
		elseif (isset($xml->Courses->Course->Track))
		{
			$startpoint = $xml->Courses->Course->Track[0]->Trackpoint;
		}

		$lat = $startpoint->Position->LatitudeDegrees;
		$lon = $startpoint->Position->LongitudeDegrees;

		$start = array((string) $lon, (string) $lat);

		return $start;
	}

	/**
	 * Pass in GPS.GPSLatitude or GPS.GPSLongitude or something in that format
	 *
	 * http://stackoverflow.com/questions/2526304/php-extract-gps-exif-data/2572991#2572991
	 * Thanks to Gerald Kaszuba http://geraldkaszuba.com/
	 *
	 * @param   unknown_type  $exifCoord  param_description
	 * @param   unknown_type  $hemi       param_description
	 *
	 * @return number
	 */
	private function getGps($exifCoord, $hemi)
	{
		$degrees = count($exifCoord) > 0 ? $this->gps2Num($exifCoord[0]) : 0;
		$minutes = count($exifCoord) > 1 ? $this->gps2Num($exifCoord[1]) : 0;
		$seconds = count($exifCoord) > 2 ? $this->gps2Num($exifCoord[2]) : 0;
		$flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

		return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $coordPart  param_description
	 *
	 * @return return_description
	 */
	private function gps2Num($coordPart)
	{
		$parts = explode('/', $coordPart);

		if ((count($parts)) <= 0)
		{
			return 0;
		}

		if ((count($parts)) == 1)
		{
			return $parts[0];
		}

		return floatval($parts[0]) / floatval($parts[1]);
	}

	/**
	 * Return Filename (trackid=-1) or track name
	 *
	 * @param   string   $file     GPS file name
	 * @param   object   $xml      parsed xml file
	 * @param   integer  $trackid  trackid
	 *
	 * @return string
	 */
	public function getTrackName($file, $xml, $trackid = -1)
	{
		// TODO function keeped for TCX, move this in extractCoordsTCX
		jimport('joomla.filesystem.file');
		$ext = JFile::getExt($file);

		if ($ext == 'tcx')
		{
			if ($trackid < 0) // Search for file name
			{
				$trackname = 'filename';

				if ( strlen($trackname) == 0)
				{
					$trackname = @$xml->trk[0]->name;
				}
			}
			else // Search for track name
			{
				$trackname = "track_$trackid";
			}

			return $trackname;
		}
		else
		{
			return null;
		}
	}
	// Osm END
}

/**
 * WpClass class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.9.10
 */
class WpClass
{
	var $lon = 0;

	var $lat = 0;

	var $value = null;

	var $url = null;

	var $urlname = null;

	var $name = null;

	var $cmt = null;

	var $desc = null;

	var $ele = null;

	var $type = null;

	var $sym = null;
}

/**
 * GpsCoordsClass class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class GpsCoordsClass
{
	/**
	 * counts the total distance of a track
	 * $koords look like this: $array($point1(array(lat,lon)),$point2(array(lat,lon)))...
	 *
	 * @param   array  $koord  param_description
	 *
	 * @return int kilometers
	 */
	public function getDistance($koord)
	{
		if (!is_array($koord))
		{
			return false;
		}

		$temp = 0;

		// Erdradius, ca. Angabe
		$earthRadius = 6378.137;

		foreach ($koord as $key => $fetch)
		{
			if (isset($koord[$key + 1]))
			{
				$first_latitude = $koord[$key][1];
				$first_longitude = $koord[$key][0];
				$first_latitude_rad = deg2rad($first_latitude);
				$first_longitude_rad = deg2rad($first_longitude);

				$second_latitude = $koord[$key + 1][1];
				$second_longitude = $koord[$key + 1][0];
				$second_latitude_rad = deg2rad($second_latitude);
				$second_longitude_rad = deg2rad($second_longitude);

				$dis = acos(
						(sin($first_latitude_rad) * sin($second_latitude_rad)) +
						(cos($first_latitude_rad) * cos($second_latitude_rad) *
								cos($second_longitude_rad - $first_longitude_rad))
				) * $earthRadius;

				if (!is_nan($dis))
				{
					$temp = $temp + $dis;
				}
			}
		}

		$distance = round($temp, 2);

		return $distance;
	}
}
