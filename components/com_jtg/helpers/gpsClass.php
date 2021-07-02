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

	var $bbox_lat_max = -90;

	var $bbox_lat_min = 90;

	var $bbox_lon_max = -180;

	var $bbox_lon_min = 180;

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

		if ($this->trackCount == 0)
		{
			$this->fileChecked = 7;
			$this->error = true;
			$this->errorMessages[] = JText::sprintf('COM_JTG_GPS_FILE_ERROR_2', $this->trackfilename);

			return $this;
		}

		// Calculate start,
		$this->start = $this->track[1]->coords[0];
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
									$this->trackCount++;
									$this->track[$this->trackCount] = new stdClass;
									$this->track[$this->trackCount]->coords = $coordinates;
									$this->track[$this->trackCount]->start = ($coordinates[0][0] . "," . $coordinates[0][1]);
									$this->track[$this->trackCount]->stop = ($coordinates[$coordinatesCount - 1][0] . "," . $coordinates[$coordinatesCount - 1][1]);
								}
							}
							break;
					}
				}
			}

			if ($this->trackCount AND $coordinates)
			{
				$this->track[$this->trackCount]->trackname = ($name? $name : $description);
				$this->track[$this->trackCount]->description = $description;
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
				$this->trackname .= $this->track[1]->trackname;
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
	{
		// Iterate nodes
		$countElements = 0;
		$i_wpt = 0;
		$i_trk = 0;
		$wp = array();
		$this->trackCount = 0;

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
						$dt = new DateTime($time);
						$this->Date = $dt->format('Y-m-d');

						// Read end tag
						$xmlcontents->read();
						break;
					case 'wpt':
						$wp[] = (array) $xmlcontents->readInnerXML();
						$i_wpt++;
						$this->wps[$i_wpt] = new WpClass;
						$lat = (float) $xmlcontents->getAttribute('lat');
						$lon = (float) $xmlcontents->getAttribute('lon');
						$this->wps[$i_wpt]->sym = 'wp';
						$this->wps[$i_wpt]->lat = $lat;
						$this->wps[$i_wpt]->lon = $lon;

						if ( $lat > $this->bbox_lat_max )
						{
							$this->bbox_lat_max = $lat;
						}

						if ( $lat < $this->bbox_lat_min )
						{
							$this->bbox_lat_min = $lat;
						}

						if ( $lon > $this->bbox_lon_max )
						{
							$this->bbox_lon_max = $lon;
						}

						if ( $lon < $this->bbox_lon_min )
						{
							$this->bbox_lon_min = $lon;
						}

						$endWptElement = false;

						while ( !$endWptElement )
						{
							$xmlcontents->read();

							if ($xmlcontents->nodeType == XMLReader::END_ELEMENT)
							{
								$endWptElement = ($xmlcontents->localName == 'wpt');
							}
							else
							{
								$endWptElement = false;
							}

							// Extract wpt data
							if ($xmlcontents->nodeType == XMLReader::ELEMENT)
							{
								$key = $xmlcontents->localName;
								$xmlcontents->read();
								$value = $xmlcontents->value;
								$this->wps[$i_wpt]->$key = $value;
								$xmlcontents->read();
							}
						}
						break;

					case 'trk':
						// Track
						$trackname = '';
						$i_trk++;

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
								$trackname = $xmlcontents->value;

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

								while ( !$endTrksegElement )
								{
									$xmlcontents->read();

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

										if ( $lat > $this->bbox_lat_max )
										{
											$this->bbox_lat_max = $lat;
										}

										if ( $lat < $this->bbox_lat_min )
										{
											$this->bbox_lat_min = $lat;
										}

										if ( $lon > $this->bbox_lon_max )
										{
											$this->bbox_lon_max = $lon;
										}

										if ( $lon < $this->bbox_lon_min )
										{
											$this->bbox_lon_min = $lon;
										}
										// Read end tag
										// $xmlcontents->read();
									}

									if ( ($xmlcontents->name == 'ele') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
									{
										// Trkpt elevation found
										$xmlcontents->read();
										$ele = (float) $xmlcontents->value;

										// Read end tag
										$xmlcontents->read();
									}

									if ( ($xmlcontents->name == 'time') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
									{
										// Trkpt time found
										$xmlcontents->read();
										$time = (string) $xmlcontents->value;
										if ($this->Date === false) {
											$dt = new DateTime($time);
											$this->Date = $dt->format('Y-m-d');
										}
										// Read end tag
										$xmlcontents->read();
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
									$this->trackCount++;
									$this->track[$this->trackCount] = new stdClass;
									$this->track[$this->trackCount]->description = '';

									if ($tracksegname)
									{
										$this->track[$this->trackCount]->trackname = $tracksegname;
									}
									elseif ($trackname)
									{
										$this->track[$this->trackCount]->trackname = $trackname;
									}
									else
									{
										$this->track[$this->trackCount]->trackname = $this->trackfilename . '-' . (string) $this->trackCount;
									}

									$this->track[$this->trackCount]->coords = $coords;
									$this->track[$this->trackCount]->start = ($coords[0][0] . "," . $coords[0][1]);
									$this->track[$this->trackCount]->stop = ($coords[$coordinatesCount - 1][0] . "," . $coords[$coordinatesCount - 1][1]);
								}
							}
							else
							{
								// Tag is not trk, trkseg, nor Name: proceed
							}
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

						while ( ('rte' !== $endElement) )
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
							// Extract rte data
							if ( ($xmlcontents->name == 'name') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
							{
								$xmlcontents->read();
								$trackname = $xmlcontents->value;

								// Read end tag
								$xmlcontents->read();
							}
						        if ( ($xmlcontents->name == 'rtept') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
							{
								// Rtept found
								// Add to trkseg for line drawing and as waypoints

								$i_wpt++;
								$this->wps[$i_wpt] = new WpClass;
								$lat = (float) $xmlcontents->getAttribute('lat');
								$lon = (float) $xmlcontents->getAttribute('lon');
								$this->wps[$i_wpt]->sym = 'wp';
								$this->wps[$i_wpt]->lat = $lat;
								$this->wps[$i_wpt]->lon = $lon;

								$i_trkpt++;
								$lat = (float) $xmlcontents->getAttribute('lat');
								$lon = (float) $xmlcontents->getAttribute('lon');

								if ( $lat > $this->bbox_lat_max )
								{
									$this->bbox_lat_max = $lat;
								}

								if ( $lat < $this->bbox_lat_min )
								{
									$this->bbox_lat_min = $lat;
								}

								if ( $lon > $this->bbox_lon_max )
								{
									$this->bbox_lon_max = $lon;
								}

								if ( $lon < $this->bbox_lon_min )
								{
									$this->bbox_lon_min = $lon;
								}
								// Read end tag
								$xmlcontents->read();

								$endRoutePoint = false;
								$extensionsFound  = false;
								while (!$endRoutePoint)
								{
								   if ( ($xmlcontents->name == 'ele') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
								   {
								      // rtept elevation found
								      $xmlcontents->read();
								      $ele = (float) $xmlcontents->value;
								      $this->wps[$i_wpt]->ele = $ele;
								      // Read end tag
								      $xmlcontents->read();
								   }

								   if ( ($xmlcontents->name == 'time') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
								   {
								     // rtept time found
								     $xmlcontents->read();
								     $time = (string) $xmlcontents->value;
     								     $this->wps[$i_wpt]->timr = $time;
								     // Read end tag
								     $xmlcontents->read();
								   }
								   if ( ($xmlcontents->name == 'extensions') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) ) {
								      // Skip extensions, but push via/shaping point
								      $extensionsFound = true;
								      while ( !(($xmlcontents->name == 'extensions') AND ($xmlcontents->nodeType == XMLReader::END_ELEMENT))) {
								      if ( ($xmlcontents->name == 'gpxx:rpt') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) )
								        {
								          $latsub = (float) $xmlcontents->getAttribute('lat');
								          $lonsub = (float) $xmlcontents->getAttribute('lon');
                                                                          $coords[] = array((string) $lonsub, (string) $latsub, (string) 0, (string) 0, 0);
								        }
								        $xmlcontents->read();
							              }
								   }	

								   if ( ($xmlcontents->name != 'time') AND ($xmlcontents->name != 'ele') AND ($xmlcontents->nodeType == XMLReader::ELEMENT) ) {
								      $key = $xmlcontents->localName;
								      $xmlcontents->read();
								      $value = $xmlcontents->value;
								      $this->wps[$i_wpt]->$key = $value;
								      $xmlcontents->read();
								   }
								   if ( ($xmlcontents->name == 'rtept') AND ($xmlcontents->nodeType == XMLReader::END_ELEMENT) )
								   {
								      // End Rtept
								      if (!$extensionsFound) 
								         $coords[] = array((string) $lon, (string) $lat, (string) $ele, (string) $time, 0);
								      $endRoutePoint = true;
								   }
								   if ( !$endRoutePoint )
								      $xmlcontents->read();
						             }
						       }
					 	}
						$coordinatesCount = count($coords);

						if ($coordinatesCount > 1 )
						{
							// This is a track with more than 2 points
							$this->isRoute = true;
							$this->trackCount++;
							$this->track[$this->trackCount] = new stdClass;
							$this->track[$this->trackCount]->description = '';
							if ($trackname)
							{
								$this->track[$this->trackCount]->trackname = $trackname;
							}
							else
							{
								$this->track[$this->trackCount]->trackname = $this->trackfilename . '-' . (string) $this->trackCount;
							}

							$this->track[$this->trackCount]->coords = $coords;
							$this->track[$this->trackCount]->start = ($coords[0][0] . "," . $coords[0][1]);
							$this->track[$this->trackCount]->stop = ($coords[$coordinatesCount - 1][0] . "," . $coords[$coordinatesCount - 1][1]);
						}
					
						break;
				}
			}
		}

		if (strlen($this->trackname) == 0)
		{
			if ($this->trackCount == 1)
			{
				if ($this->track[1]->trackname)
				{
					$this->trackname = $this->track[1]->trackname;
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
				$this->description = $this->track[1]->description? $this->track[1]->description: '';
			}
			elseif ($this->trackCount > 1)
			{
				$this->description = $this->track[1]->description? $this->track[1]->description: '';

				for ($i = 2; $i <= $this->trackCount; $i++)
				{
				$this->description .= '<br>' . $this->track[$i]->description? $this->track[$i]->description: '';
				}
			}
		}

		$xmlcontents->close();

	}

// Nothing to return
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
	 * function_description
	 *
	 * @return return_description
	 */
	private function extractAllTracksCoords()
	{
		$params = JComponentHelper::getParams('com_jtg');

		$filterMinAscent = (float) $params->get('jtg_param_elevation_filter_min_ascent');
		$filterMinAscent = max(0, $filterMinAscent);

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

		for ($t = 1; $t <= $this->trackCount; $t++)
		{
			$this->allCoords = array_merge($this->allCoords, $this->track[$t]->coords);

			// Calculate distances
			$next_coord = $this->track[$t]->coords[0];
			$next_lat_rad = deg2rad($next_coord[1]);
			$next_lon_rad = deg2rad($next_coord[0]);

			if ( $next_coord[1] > $this->bbox_lat_max )
			{
				$this->bbox_lat_max = $next_coord[1];
			}

			if ( $next_coord[1] < $this->bbox_lat_min )
			{
				$this->bbox_lat_min = $next_coord[1];
			}

			if ( $next_coord[0] > $this->bbox_lon_max )
			{
				$this->bbox_lon_max = $next_coord[0];
			}

			if ( $next_coord[0] < $this->bbox_lon_min )
			{
				$this->bbox_lon_min = $next_coord[0];
			}

			if ($this->elevationDataExists)
			{
				$current_elv = $next_coord[2];
				$this->allElevation[$d] = (int) $current_elv;
			}

			if ($this->speedDataExists)
			{
				$next_time = $this->giveTimestamp($next_coord[3]);
			}

			$datacount = count($this->track[$t]->coords);

			for ($i = 0; $i < $datacount - 1; $i++)
			{
				$next_coord = $this->track[$t]->coords[$i + 1];

				if (isset($next_coord))
				{
					$current_lat_rad = $next_lat_rad;
					$current_lon_rad = $next_lon_rad;

					$next_lat_rad = deg2rad($next_coord[1]);
					$next_lon_rad = deg2rad($next_coord[0]);

					if ( $next_coord[1] > $this->bbox_lat_max )
					{
						$this->bbox_lat_max = $next_coord[1];
					}

					if ( $next_coord[1] < $this->bbox_lat_min )
					{
						$this->bbox_lat_min = $next_coord[1];
					}

					if ( $next_coord[0] > $this->bbox_lon_max )
					{
						$this->bbox_lon_max = $next_coord[0];
					}

					if ( $next_coord[0] < $this->bbox_lon_min )
					{
						$this->bbox_lon_min = $next_coord[0];
					}

					// Distance in kilometer

					$dis = acos(
							(sin($current_lat_rad) * sin($next_lat_rad)) +
							(cos($current_lat_rad) * cos($next_lat_rad) *
									cos($next_lon_rad - $current_lon_rad))
					) * $earthRadius;

					if (is_nan($dis))
					{
						$dis = 0;
					}

					$this->allDistances[$d + 1] = $this->allDistances[$d] + $dis;

					if ($this->elevationDataExists)
					{
						$next_elv = $next_coord[2];
						$this->allElevation[$d + 1] = (int) $next_elv;
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

						if ($current_time and $next_time)
						{
							$elapsedTime = $next_time - $current_time;

							if ($elapsedTime > 0)
							{
								$this->allSpeed[$d + 1] = $dis / $elapsedTime * 3600;
							}
							else
							{
								$this->allSpeed[$d + 1] = 0;
							}
						}
						else
						{
							$this->allSpeed[$d + 1] = 0;
						}
					}

					// Heart Beat
					if ($this->beatDataExists)
					{
						$this->allBeat[$d + 1] = $next_coord[4];
					}

					$d++;
				}
			}
		}

		$this->distance = $this->allDistances[$d];

		if ($this->elevationDataExists)
		{
			$this->totalAscent = (int) $this->totalAscent;
			$this->totalDescent = (int) $this->totalDescent;
		}

		if ( ( $this->totalAscent == 0 ) or ($this->totalDescent == 0) )
		{
			$this->elevationDataExists = false;
		}

		// Is this track a roundtrip ?
		$t = $this->trackCount;
		$n = count($first_coord = $this->track[$t]->coords);
		$first_coord = $this->track[1]->coords[0];
		$first_lat_rad = deg2rad($first_coord[1]);
		$first_lon_rad = deg2rad($first_coord[0]);
		$last_coord = $this->track[$t]->coords[$n-1];
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
	 * @param   unknown_type  $t  param_description
	 *
	 * @return return_description
	 */
	public function transformTtRGB($t)
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
	public function calculateAllColors($count)
	{
		$color = array();

		for ($i = 1;$i <= $count;$i++)
		{
			$color[($i - 1)] = $this->transformTtRGB(round(300 / $count * $i));
		}

		return $color;
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

		$center = "// <!-- parseOLMapCenterSingleTrack BEGIN -->\n";
                $center .= "olview.fit( ol.proj.transformExtent( [ ".
                           "$this->bbox_lon_min, ".
                           "$this->bbox_lat_min, $this->bbox_lon_max, ".
                           "$this->bbox_lat_max ], 'EPSG:4326', olview.getProjection()), {padding: [50, 50, 50, 75]} );\n";
		$center .= "// <!-- parseOLMapCenterSingleTrack END -->\n";
		$this->wpCenter = $center;
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
	 * Function parseCatIcon
	 *
	 * @param   integer  $catid    category ID
	 * @param   boolean  $istrack  true if GPS file contains track(s)
	 * @param   boolean  $iswp     true if GPS file contains waypoint(s)
	 * @param   boolean  $isroute  true if GPS file contains route(s)
	 *
	 * @return return_description
	 */
	public function parseCatIcon($catid, $istrack = 0, $iswp = 0, $isroute = 0)
	{
		$catid = explode(",", $catid);
		$catid = $catid[0];
		$cfg = JtgHelper::getConfig();
		$iconpath = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";
		$catimage = false;
		$cats = $this->getCats();

		// TODO find a most efficient way to do this!!
		foreach ( $cats AS $cat )
		{
			if ( $cat->id == $catid )
			{
				if ( $cat->image )
				{
					$catimage = $cat->image;
					break;
				}
			}
		}

		if (! $catimage )
		{
			$catimage = 'symbol_inter.png';
		}

		$catimage = "images/jtrackgallery/cats/" . $catimage;

		if ( !is_file($catimage) )
		{
			$catimage = "images/jtrackgallery/cats/symbol_inter.png";
		}

		$simagesize = getimagesize(JPATH_SITE . '/' . $catimage);
		$sizex = $simagesize[0];
		$sizey = $simagesize[1];
		$maximagesize = 32;

		if ( ( $sizex > $maximagesize ) OR ( $sizey > $maximagesize) )
		{
			$oldsizex = $sizex;
			$oldsizey = $sizey;
			$ratio = $sizex / $sizey;

			if ( $ratio > 1 )
			{
				// Pic is letterbox
				$sizex = $maximagesize;
				$sizey = $sizex / $ratio;
			}
			else
			{
				// Pic is upright
				$sizey = $maximagesize;
				$sizex = $sizey * $ratio;
			}
		}

		$iconuri = JUri::root() . $catimage;
		$iconStyle = "new ol.style.Icon({size: [$sizex , $sizey], anchor: [0.5, 1], src: '$iconuri'})"; // MvL: could make a cache with these objects and use them once?

		return $iconStyle;
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

		if ( ( $ownicon == false ) OR (!is_file($jpath . $filename . ".png")) )
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
		return $unknownicon . "var icon = new ol.style.Icon({src: '" . $icon . "',\n			size: [" . $sizex . ", " . $sizey . "],\n			anchorXUnits: 'pixels', anchorYUnits: 'pixels',\n anchor: [" . $offsetx . ", " . $offsety . "]});\n";
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

		$wpcode = "// <!-- parseWPs BEGIN -->\n";
		$wpcode .= "wps = new ol.source.Vector();\n";
		$wpcode .= "wplayer = new ol.layer.Vector({title: \"" . JText::_('COM_JTG_WAYPOINTS') . "\", source: wps});";
		$wpcode .= "olmap.addLayer(wplayer);";
		$wpcode .= "addWPs();";
		$wpcode .= "function addWPs() {\n";

		foreach ($this->wps as $wp)
		{
			$replace = array("\n","'");
			$with = array("<br />","\'");
			$hasURL = isset($wp->URL);
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

			$wpcode .= "llwp = new ol.proj.fromLonLat([" . $wp->lon . "," . $wp->lat . "], olview.getProjection());\n ";
//			$wpcode .= "Projection(\"EPSG:4326\"), olmap.getProjectionObject());\n";
//			$wpcode .= "popupClasswp = AutoSizeFramedCloud;\n";
			$wpcode .= "popupContentHTMLwp = '" . $name . $URL;

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
				$wpcode .= "<br /><b>" . JText::_('COM_JTG_ELEVATION') . " :</b> ca. " . round($ele, 1) . "m<small>";
			}

			$wpcode .= "';\n";
			$wpcode .= $this->parseOwnIcon($sym);
			$wpcode .= "addWP(llwp, popupContentHTMLwp, true, true, icon);\n";
		}

		$wpcode .= "	}\n";
		/*
		 * $wp .= "	//
		* Function: addWP
		* Add a new marker to the markers layer given the following lonlat,
		*	 popupClass, and popup contents HTML. Also allow specifying
		*	 whether or not to give the popup a close box.
		*
		* Parameters:
		* ll - {<OpenLayers.LonLat>} Where to place the marker
		* popupClass - {<OpenLayers.Class>} Which class of popup to bring up
		*	 when the marker is clicked.
		* popupContentHTML - {String} What to put in the popup
		* closeBox - {Boolean} Should popup have a close box?
		* overflow - {Boolean} Let the popup overflow scrollbars?
		*/

		$wpcode .= "	function addWP(ll, popupContentHTML, closeBox, overflow, icon) {
		var wp = new ol.Feature({ geometry: new ol.geom.Point(ll), name: popupContentHTML });
                wp.setStyle(new ol.style.Style({image: icon}));
		wps.addFeature(wp);
	}\n";
		$wpcode .= "// <!-- parseWPs END -->\n";

		return $wpcode;
	}

	/**
	 * function_description
	 *
	 * @param   string  $desc  track description
	 *
	 * @return string
	 */
	public function showDesc($desc)
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
				$return .= "<p>";
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
	 *
	 */

	/**
	 * function_description
	 *
	 * @param   string  $wish  optionnal expected color
	 *
	 * @return color (#000000 - #ffffff) or own wish
	 */
	public function getHexColor($wish = false)
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
	 * function_description
	 *
	 * @return array
	 */
	function getMapNates()
	{
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$query = "SELECT start_n FROM #__jtg_files"
		. "\n ORDER BY start_n ASC"
		. "\n LIMIT 1";

		$db->setQuery($query);
		$north = $db->loadResult();

		$query = "SELECT start_e FROM #__jtg_files"
		. "\n ORDER BY start_e DESC"
		. "\n LIMIT 1";

		$db->setQuery($query);
		$east = $db->loadResult();

		$cnates = array();
		$cnates[] = $north;
		$cnates[] = $east;

		return $cnates;
	}

	/**
	 * function_description
	 *
	 * @param   sstring  $where  where query string
	 *
	 * @return array
	 */
	function getTracks($where="")
	{
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$query = "\nSELECT a.*, b.title AS cat FROM #__jtg_files AS a"
		. "\n LEFT JOIN #__jtg_cats AS b"
		. "\n ON a.catid=b.id" . $where;
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}

	/**
	 * function_description
	 *
	 * @return array
	 */
	function getCats()
	{
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__jtg_cats";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
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
	public function writeOLMap($where,$showtracks,$params,$items=null,$geoloc=false)
	{
		$cfg = JtgHelper::getConfig();

		// 	$cnates = $this->getMapNates();
		if (empty($items)) $items = $this->getTracks($where);

		/*
		 $user = JFactory::getUser();
		$userid = $user->id;
		$rows = $this->maySee($rows);
		*/

		$map = false;
		$map .= $this->parseScriptOLHead();
		$map .= $this->parseOLMapControl($params, false);
		$map .= $this->parseOLLayer($showtracks, $params);
		/*
		 $map .= $this->parseOLPOIs(); // Currently not active
		*/

		if ($showtracks)
		{
			// Slow when there are many tracks
			$map .= $this->parseOLTracks($items);
		}

		$file = JPATH_SITE . "/components/com_jtg/models/jtg.php";
		require_once $file;
		$this->sortedcats = JtgModeljtg::getCatsData(true);

		$map .= $this->parseOLMarker($items);
		if ($geoloc) {
			$map .= "  geolocate();\n";
			$map .= "  olview.setZoom(jtgMapZoomLevel);\n";
		}
		else
		{
			$map .= $this->parseOLMapCenter($items);
		}
		$map .= $this->parseScriptOLFooter();

		return $map;
	}

	/**
	 * counts the MapCenter and ZoomLevel of Boundingbox
	 *
	 * @param   unknown_type  $lon_min  param_description
	 * @param   unknown_type  $lon_max  param_description
	 * @param   unknown_type  $lat_min  param_description
	 * @param   unknown_type  $lat_max  param_description
	 *
	 * @return array('lon'=>lon,'lat'=>lat)
	 */
	public function calcMapCenter($lon_min, $lon_max, $lat_min, $lat_max)
	{
		// Weltansicht ohne Südpol
		$lat = 47;
		$lon = 0;
		$zoom_min = 2;
		$zoom_max = 14;
		$return = array();

		if ( ( $lon_min == $lon_max ) AND ( $lat_min == $lat_max ) )
		{
			// Nur eine Koordinate wurde übergeben
			$return['lon'] = $lon_min;
			$return['lat'] = $lat_min;
		}
		else
		{
			$return['lon'] = ( ( $lon_max + $lon_min ) / 2 );
			$return['lat'] = ( ( $lat_max + $lat_min ) / 2 );
		}

		return $return;
	}

	/**
	 * function_description
	 *
	 * @param   object  $rows  rows
	 *
	 * @return string
	 */
	private function parseOLMapCenter($rows)
	{
		if (!$rows)
		{
			// 		Worldview without southpole
			$bbox_lat_max = 47;
			$bbox_lat_min = 47;
			$bbox_lon_max = 180;
			$bbox_lon_min = -180;
		}
		else
		{
			$bbox_lat_max = -90;
			$bbox_lat_min = 90;
			$bbox_lon_max = -180;
			$bbox_lon_min = 180;

			foreach ( $rows AS $row )
			{
				if ( ( $row->start_n ) AND ( $row->start_n > $bbox_lat_max ) )
				{
					$bbox_lat_max = $row->start_n;
				}

				if ( ( $row->start_n ) AND ( $row->start_n < $bbox_lat_min ) )
				{
					$bbox_lat_min = $row->start_n;
				}

				if ( ( $row->start_e ) AND ( $row->start_e > $bbox_lon_max ) )
				{
					$bbox_lon_max = $row->start_e;
				}

				if ( ( $row->start_e ) AND ( $row->start_e < $bbox_lon_min ) )
				{
					$bbox_lon_min = $row->start_e;
				}
			}
		}

		$center = "// <!-- parseOLMapCenter BEGIN -->\n";
                $center .= "var min = ol.proj.fromLonLat(\n";
                $center .= "[" . $bbox_lon_min . "," . $bbox_lat_min . "],olview.getProjection());\n".
                           " var max = ol.proj.fromLonLat(\n";
                $center .= "[" . $bbox_lon_max . "," . $bbox_lat_max . "],olview.getProjection());\n";
                $center .= "olview.fit(min.concat(max), {padding: [50, 50, 50, 75]});\n";
		$center .= "// <!-- parseOLMapCenter END -->\n";

		return $center;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $track_array  param_description
	 * @param   unknown_type  $visibility   param_description
	 *
	 * @return string
	 */
	private function parseOLMarker($track_array, $visibility = true)
	{
		$cfg = JtgHelper::getConfig();
		$user = JFactory::getUser();
		$uid = $user->id;

		if (!$track_array)
		{
			return false;
		}

		$document = JFactory::getDocument();
		$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/jtgOverView.js');
		$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/geolocation.js');

		$marker = "// <!-- parseOLMarker BEGIN -->\n";
		$marker .= "markers = [\n";
		$i = 0;

		foreach ( $track_array AS $row )
		{
			$i++;
			$url = JROUTE::_("index.php?option=com_jtg&view=files&layout=file&id=" . $row->id);
			$lon = $row->start_e;
			$lat = $row->start_n;

			// view published or used tracks
			if ( ($row->published == 1 OR $row->uid == $uid)  AND ( ( $lon ) OR ( $lon ) ))
			{
				$link = "<a href=\"" . $url . "\"";

				switch ($row->access)
				{
					case 0:
						// Public
						$link .= ">";
						break;
					case 9:
						// Private
						$link .= " title=\\\"" . JText::_('COM_JTG_PRIVATE') . "\">";
						break;
					case 1:
						// Registered
						$link .= " title=\\\"" . JText::_('COM_JTG_REGISTERED') . "\">";
						break;
					case 2:
						// Admin
						$link .= " title=\\\"" . JText::_('COM_JTG_ADMINISTRATORS') . "\">";
						break;
				}

				if ($row->title)
				{
					$link .= str_replace(array("'"), array("\'"), $row->title);
				}
				else
				{
					$link .= "<i>" . str_replace(array("'"), array("\'"), JText::_('COM_JTG_NO_TITLE')) . "</i>";
				}

				if ( $row->access != 0 )
				{
					$iconpath = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";
				}

				switch ($row->access)
				{
					case 1:
						$link .= "&nbsp;<img alt=\\\"" . JText::_('COM_JTG_REGISTERED') . "\" src=\\\"" . $iconpath . "registered_only.png\\\" />";
						break;
					case 2:
						$link .= "&nbsp;<img alt=\\\"" . JText::_('COM_JTG_ADMINISTRATORS') . "\" src=\\\"" . $iconpath . "special_only.png\\\" />";
						break;
					case 9:
						$link .= "&nbsp;<img alt=\\\"" . JText::_('COM_JTG_PRIVATE') . "\" src=\\\"" . $iconpath . "private_only.png\\\" />";
						break;
				}
				$link .= "</a></b>";

				// TODO; check if cat data member exists; if not, use catid
				if ( $row->catid != "" )
				{
					$cats = str_replace(array("'"), array("\'"), JText::_('COM_JTG_CAT')) . ": ";
					$cats = JtgHelper::parseMoreCats($this->sortedcats, $row->catid, "box", true);
				}
				else
				{
					$cats = "<br /><i>" . str_replace(array("'"), array("\'"), JText::_('COM_JTG_CAT_NONE')) . "</i>";
				}

				// Add track description, after striping HTML tags
				$description = $this->showDesc($row->description);

				// Start icon
				$iconStyle = $this->parseCatIcon($row->catid, $row->istrack, $row->iswp, $row->isroute);

				// Javacsript code
				if ($i > 1)
				{
					$marker .= "	,\n";
				}

				$marker .= "	{\n" .
					'		"lon" : ' . $lon . ",\n" .
					'		"lat" : ' . $lat . ",\n" .
					'		"cats" : \'' . $cats . "',\n" .
					'		"link": \'' . $link . "',\n" .
					'		"description": \'' . $description . "',\n" .
					'		"iconStyle": ' . $iconStyle . "\n " .
					"	}\n";
			}
			else
			{
				// Dummy line for Coding standard
			}
		}

		$marker .= "	];\n";
		$marker .= "addClusteredLayerOfMarkers();\n";
		$marker .= "// <!-- parseOLMarker END -->\n";

		return $marker;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $rows  param_description
	 *
	 * @return string
	 */
	private function parseOLTracks($rows)
	{
		if ($rows === null)
		{
			return false;
		}
		else
		{
			// Dummy line for Coding standard
		}

		$color = $this->calculateAllColors(count($rows));
		$string = "// <!-- parseOLTracks BEGIN -->\n";
		// MvL: TODO see whether we can keep the name and other options \"" . JText::_('COM_JTG_TRACKS') . "\", { displayInLayerSwitcher: true } );\n";
		//$string .= "olmap.addLayer(layer_vectors);\n";
		$i = 0;
		$cache = JFactory::getCache();

		// MvL: things to check: the vectors are now added one by one instead of as layers with many vectors.
		foreach ($rows AS $row)
		{
			$file = JUri::base()."images/jtrackgallery/uploaded_tracks/" . $row->file;
			$filename = $file;
			// MvL TODO: check file type; this code builds the overview map?
			$string .= "layer_vector = new ol.layer.Vector({";
			$string.="source: new ol.source.Vector({ url: '".$file."', format: new ol.format.GPX() }),\n";

         /*
			// MvL: check remove
			$gpsData = new GpsDataClass("Kilometer");
			$gpsData = $cache->get(array ( $gpsData, 'loadFileAndData' ), array ($file, $filename ), "Kilometer");
			$coords = $gpsData->allCoords;
			$string .= "geometries = new Array();geometries.push(drawLine([\n";

			if ($coords)
			{
				$string .= "// <!-- parseOLSingleTrack BEGIN -->\n";
				foreach ($coords as $key => $fetch)
				{
					$string .= "[" . $coords[$key][0] . "," . $coords[$key][1] . "],";
				}
				$string .= "// <!-- parseOLSingleTrack END -->\n";
			}
			else
			{
				// Dummy line for Coding standard (else required!)
			}
			//$string .= "],\n{strokeColor:\"" . $this->getHexColor("#" . $color[$i]) . "\",\nstrokeWidth: 2,\nfillColor: \"" . $this->getHexColor("#" . $color[$i]) . "\",\nfillOpacity: 0.4}));\n";
			*/
			$string .= "   style: new ol.style.Style({ stroke: new ol.style.Stroke({ color:'" . $this->getHexColor("#" . $color[$i]) . "',\n width: 2}) })";
			$string .= "});\n";
			$string .= "olmap.addLayer(layer_vector);\n";
			$i++;
		}

		$string .= "// <!-- parseOLTracks END -->\n";

		return $string;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	private function parseOLPOIs()
	{
		$pois = "// <!-- parseOLPOIs BEGIN -->\n";
		$pois = "// <!-- POI not handled by J!TrackGallery -->\n";
		$pois .= "// <!-- parseOLPOIs END -->\n";

		return $pois;
	}

	/**
	 * function_description
	 *
	 * @param   string  $desc  query order
	 *
	 * @return maps Object
	 */
	private function getMaps($desc=false)
	{
		$db = JFactory::getDBO();
		$sql = 'Select * from #__jtg_maps';

		if ($desc)
		{
			$sql .= ' ORDER BY ' . $desc;
		}
		else
		{
			// Dummy line for Coding standard
		}

		$db->setQuery($sql);
		$maps = $db->loadObjectlist();

		return $maps;
	}

	/**
	 * Parse DPCalendar location for marker drawing
	 *
	 * @param   unknown_type  $items calendar items
	 *
	 * @return string: javascript array
	 */
	function parseDPCalLocations($items)
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
	 * @return Object
	 */
	private function buildMaps($track, $params)
	{
		$maps = $this->getMaps("ordering");
		$return = "";
		$document = JFactory::getDocument();

		$user = JFactory::getUser();
		$uid = $user->id;

		// Search maps and overlays Defaults
		$db = JFactory::getDBO();

		// Ordering by id is useful in case no order has been set.
		$query = "SELECT id FROM #__jtg_maps WHERE published=1 ORDER by ordering, id";
		$db->setQuery($query);
		$ids = $db->loadColumn();
		$defaultMap = $ids[0];
		$defaultOverlays = null;
		$overlays = '';

		// Search maps and overlays Defaults defined from categories (excluding unpublished maps)

		if (isset($track->catid))
		{
			if ( ($track->catid > 0) AND (in_array($track->catid, $ids)) )
			{
				$query = "SELECT default_map, default_overlays FROM #__jtg_cats WHERE id IN($track->catid)";
				$db->setQuery($query);
				$cat_defaults = $db->loadObjectlist();
				if (count($cat_defaults))
				{
					$catDefaultMap = $cat_defaults[0]->default_map;
					$catDefaultOverlays = $cat_defaults[0]->default_overlays;
				}
				$defaultMap = $catDefaultMap? $catDefaultMap: $defaultMap;
				$defaultOverlays = $catDefaultOverlays? $catDefaultOverlays: $defaultOverlays;
			}

			// Search maps and overlays Defaults defined by track (excluding unpublished map)

			if ( $track->default_map AND (in_array($track->default_map, $ids)))
			{
				$defaultMap = $track->default_map;
			}
			$defaultOverlays = $track->default_overlays? $track->default_overlays: $defaultOverlays;
			$defaultOverlays = unserialize($defaultOverlays);
		}

		for ($i = 0;$i < count($maps);$i++)
		{
			$map = $maps[$i];
			$name = strtolower(str_replace(array(" ","_"), "", html_entity_decode($map->name)));
			$realname = JText::_(html_entity_decode($map->name));
			$param = str_replace("{name}", $realname, html_entity_decode($map->param));
			$script = html_entity_decode($map->script);

			if ( ($map->published == 1) OR ( isset($iud) AND ($map->uid == $uid) ))
			{
				if ($script)
				{
					if (!preg_match("/|/", $script))
					{
						$document->addScript($script);
					}
					else
					{
						$scripts = explode("|", $script);

						foreach ($scripts AS $eachscript)
						{
							$document->addScript($eachscript);
						}
					}
				}

				$code = html_entity_decode($map->code);

				if ($code != "")
				{
					$return .= $code . "\n";
				}

				if ($defaultMap == $map->id)
				{
					//$baselayer = "		olmap.setBaseLayer(layer" . $name . ");\n";
					$baselayer = "		\n";// MvL TODO check how to deal with base layers
				}

				// Activate default overlays
				if (is_array($defaultOverlays))
				{
					if (in_array($map->id, $defaultOverlays))
					{
						// Set this as an active overlay
						$overlays .= "layer" . $name . ".setVisibility(true);\n";
					}
				}

				$return .= "layer" . $name . " = new " . $param . ";\n" .
						$overlays .
						"olmap.addLayer(layer" . $name . ");\n ";
			}
		}

		// TODO osm_getTileURL see http://wiki.openstreetmap.org/wiki/Talk:Openlayers_POI_layer_example
		// TODO: can probably remove this?
		$document->addScript(JUri::root(true) . '/components/com_jtg/assets/js/jtg_getTileURL.js');

		if ( !isset($baselayer))
		{
			// No map available
			return false;
		}

		$return = $return . $baselayer;

		return $return;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	private function parseOLLayer($track, $params)
	{
		$maps = $this->buildMaps($track, $params);
		$layer = "// <!-- parseOLLayer BEGIN -->\n";
		$layer .= $maps . "\n// <!-- parseOLLayer END -->\n";

		return $layer;

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
	 * function_description
	 *
	 * @param   integer  $id                track ID
	 * @param   integer  $max_geoim_height  geotagged image height on maps
	 * @param   string   $iconfolder        icons folder path
	 * @param   string   $httpiconpath      icons folder URL
	 *
	 * @return string html code to display geotagged images
	 */
	private function parseOLGeotaggedImgs($id, $max_geoim_height, $iconfolder, $httpiconpath, $imageList)
	{
		jimport('joomla.filesystem.folder');
		$max_geoim_height = (int) $max_geoim_height;
		$foundpics = false;
		$map = "// <!-- parseOLGeotaggedImgs BEGIN -->\n";
		$httppath = JUri::root() . "images/jtrackgallery/uploaded_tracks_images/track_" . $id . "/";
		$folder = JPATH_SITE . "/images/jtrackgallery/uploaded_tracks_images/" . 'track_' . $id . '/';

		if ($imageList)
		{
			$xml = simplexml_load_file($httpiconpath . "foto.xml");
 			$sizex = $xml->sizex;
			$sizey = $xml->sizey;
			$offsetx = -$xml->offsetx;
			$offsety = -$xml->offsety;
			$map .= "photoIcon = new ol.style.Icon({src: '" . $iconfolder . "foto.png', anchorXUnits: 'pixels', anchorYUnits: 'pixels', anchor: [".$offsetx.", ".$offsety."] } );\n";
			$map .= "layer_geotaggedImgs = new ol.layer.Vector({title: \"" . JText::_('COM_JTG_GEOTAGGED_IMAGES') . "\"," .
		      " displayInLayerSwitcher: true, source: new ol.source.Vector(), style: new ol.style.Style( { image: photoIcon} ) });" .
			"\n	olmap.addLayer(layer_geotaggedImgs);";
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
					$map .= "var lonLatImg = new ol.proj.fromLonLat([" . $image->lon . "," . $image->lat . "],olview.getProjection());\n";
					$map .= "photoFeat = new ol.Feature( {geometry: new ol.geom.Point(lonLatImg), name: '".$imagehttp."'} );\n";
					$map .= "layer_geotaggedImgs.getSource().addFeature(photoFeat);\n";
            }
			}
		}

		if ( ! $imageList )
		{
			return false;
		}

		$map .= "// <!-- parseOLGeotaggedImgs END -->\n";

		return $map;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $track   param_description
	 * @param   unknown_type  $params  param_description
	 *
	 * @return return_description
	 */
	public function writeTrackOL($track, $params, $imageList, $makepreview = false)
	{
		$mainframe = JFactory::getApplication();
		$jtg_microtime = microtime(true);
		$zeiten = "<br />\n";
		$cfg = JtgHelper::getConfig();
		$iconpath = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";
		$httpiconpath = JPATH_SITE . "/components/com_jtg/assets/template/" . $cfg->template . "/images/";
		jimport('joomla.filesystem.file');
		$zeiten .= (int) round(( microtime(true) - $jtg_microtime), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " getTracks<br />\n";

		$map = "\n<!-- writeTrackCOM_JTG BEGIN -->\n";
		$map .= $this->parseScriptOLHead();
		$zeiten .= (int) round((microtime(true) - $jtg_microtime), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseScriptOLHead<br />\n";
		$map .= $this->parseOLMap();
		$zeiten .= (int) round((microtime(true) - $jtg_microtime), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseOLMap<br />\n";

		$map .= $this->parseOLMapControl($params, false);
		$zeiten .= (int) round((microtime(true) - $jtg_microtime), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseOLMapControl<br />\n";
		$map .= $this->parseOLLayer($track, $params);

		$zeiten .= (int) round((microtime(true) - $jtg_microtime), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseOLLayer<br />\n";
		$coords = $this->parseXMLlinesOL($params);
		$zeiten .= (int) round((microtime(true) - $jtg_microtime), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseXMLlinesOL<br />\n";

		if ( $this->allCoords !== null )
		{
			$map .= $coords['center'];
		}
		else
		{
			$map .= $this->wpCenter;
		}

		if ( $coords !== null )
		{
			$map .= $coords['coords'];
		}

		$zeiten .= (int) round((microtime(true) - $jtg_microtime ), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseOLMarker<br />\n";
		$map .= $this->parseOLGeotaggedImgs($track->id, $cfg->max_geoim_height, $iconpath, $httpiconpath, $imageList);
		$zeiten .= (int) round((microtime(true) - $jtg_microtime ), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseOLGeotaggedImgs<br />\n";

		if ($this->wps !== false)
		{
			$map .= $this->parseWPs();
		}

		$zeiten .= (int) round((microtime(true) - $jtg_microtime ), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseOLMapCenterSingleTrack<br />\n";

		if ($makepreview) {
			$prevwidth = 1080;
			$prevheight = 640;
			$map .= "olmap.once('rendercomplete', function () { 
			   var origSize = olmap.getSize();
				var origResolution = olmap.getView().getResolution();
				//makePreview($prevwidth, $prevheight, origSize, origResolution);
				makePreview(origSize[0], origSize[1], origSize, origResolution);
				// Set preview size
				var printSize = [$prevwidth, $prevheight];
				//olmap.setSize(printSize); // Changes centering, without changing aspect ratio
				var scaling = Math.min(printSize[0] / origSize[0], printSize[1] / origSize[1]);
				//olmap.getView().setResolution(origResolution / scaling); 
			});";
		}
		$map .= $this->parseScriptOLFooter();
		$zeiten .= (int) round((microtime(true) - $jtg_microtime ), 0) . " " . JText::_('COM_JTG_DEBUG_TIMES') . " parseScriptOLFooter<br />\n";
		$map .= "\n<!-- writeTrackCOM_JTG END -->\n";

		return $map;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $file    param_description
	 * @param   unknown_type  $params  param_description
	 *
	 * @return return_description
	 */
	public function writeSingleTrackOL($file,$params=false)
	{
		// TODO NO LONGER USED !!
		// For little Map in Administration
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');

		$map = "\n<!-- writeSingleTrackCOM_JTG BEGIN -->\n";
		$map .= $this->parseScriptOLHead();

		$map .= $this->parseOLMap();
		$map .= $this->parseOLMapControl($params, true);
		$map .= $this->parseOLLayer();
		$coords = $this->parseXMLlinesOL($params);
		$map .= $coords['coords'];

		if ($this->wps)
		{
			$map .= $this->parseWPs();
		}

		if ( $coords !== null )
		{
			$map .= $coords['center'];
		}
		else
		{
			$map .= $wp['center'];
		}

		$map .= $this->parseScriptOLFooter();
		$map .= "<!-- writeSingleTrackCOM_JTG END -->\n";

		return $map;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	private function parseScriptOLHead()
	{
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.folder');
		$template = $mainframe->getTemplate();
		$imgpath = 'templates/' . $template . '/css/ol_images';

		if ( JFolder::exists(JPATH_SITE . '/' . $imgpath))
		{
			$imgpath = JUri::root() . $imgpath;
		}
		else
		{
			$imgpath = JUri::root() . 'components/com_jtg/assets/template/default/ol_images/';
		}

		$map = "\n<!-- parseScriptOLHead BEGIN -->\n";
		$map .= "<script type=\"text/javascript\">\n";
		$map .= "	function slippymap_init() {\n";
		$map .= "			// Control images folder : remember the trailing slash\n";

		// TODO ACCOUNT FOR TEMPLATES
		//$map .= "			OpenLayers.ImgPath = \"$imgpath\" \n";
		//$map .= "			olmap = new OpenLayers.Map ( {theme: null, div: \"jtg_map\",\n";
                // MvL TODO: set image path?
                $map .= "olview = new ol.View( {\n";
                $map .= "                               center: [0, 0],\n";
                //$map .= "                               zoom: 1,\n";
                //$map .= "                               numZoomLevels: 19,\n";
                $map .= "                               units: \"m\",\n";
                $map .= "                       } );\n\n";
                $map .= "                       olmap = new ol.Map ( { target: \"jtg_map\",\n";
		$map .= "// <!-- parseScriptOLHead END -->\n";

		return $map;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	private function parseScriptOLFooter()
	{
		$map = "// <!-- parseScriptOLFooter BEGIN -->\n";

		$map .= " addPopup();\n";
		// Close slippymap_s_init script
		$map .= "}\n";
		$map .= "</script>\n";
		$map .= "<!-- parseScriptOLFooter END -->\n";

		return $map;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $params     param_description
	 * @param   unknown_type  $adminonly  param_description
	 *
	 * @return string
	 */
	private function parseOLMapControl($params, $adminonly = false)
	{
		if ( strtolower($this->unit) == "kilometer" )
		{
			$topOutUnits = "km";
			$topInUnits = "m";
			$bottomOutUnits = "mi";
			$bottomInUnits = "ft";
		}
		else
		{
			$topOutUnits = "mi";
			$topInUnits = "ft";
			$bottomOutUnits = "km";
			$bottomInUnits = "m";
		}

		$control = "// <!-- parseOLMapControl BEGIN -->\n";
		$control .= "		controls:[\n";
		$control .= "// 	Don't forget to remove comma in last line.\n// 	Otherwise it doesn't work with IE.\n";

		if ( ( $params === false ) OR ( $params->get('jtg_param_show_mouselocation') != "0" ) )
		{
			$control .= "				new ol.control.MousePosition( {coordinateFormat: ol.coordinate.createStringXY(4), projection: 'EPSG:4326' }),\n"; // Show mouse coordinate
		}

		if ( ( $params === false ) OR ( $params->get('jtg_param_show_layerswitcher') != "0" ) )
		{
                        // MvL: Layerswitcher is not default in OL6
			//$control .= "				new OpenLayers.Control.LayerSwitcher(),		// Menue zum ein/aus-Schalten der Layer\n";
		}

		if ( $adminonly === false)
		{
			if ( ( $params === false ) OR ( $params->get('jtg_param_show_panzoombar') != "0" ) )
			{
				$control .= "				new ol.control.ZoomSlider(),\n";
			}

			if ( ( $params === false ) OR ( $params->get('jtg_param_show_attribution') != "0" ) )
			{
				$control .= "				new ol.control.Attribution(),		// CC-By-SA ... \n";
			}

			if ( ( $params === false ) OR ( $params->get('jtg_param_show_scale') != "0" ) )
			{
				$control .= "				new ol.control.ScaleLine({\n					topOutUnits: '" .
					$topOutUnits . "',\n					topInUnits: '" .
					$topInUnits . "',\n					bottomOutUnits: '" .
					$bottomOutUnits . "',\n					bottomInUnits: '" .
					$bottomInUnits . "'\n				}),
							// Scalebar \n";
			}
		}

		$control .= "			],\n";
                $control .= "view: olview } );\n";

		// Don't use fullscreen option on admin site
		if ( strpos(JUri::base(), '/administrator/') == false ) // There is probably a better way to do this
		{
			$control .= "		var fullscreenToolbar = new ol.control.FullScreen();\n";
			$control .= "		olmap.addControl(fullscreenToolbar);\n";
		}

                // TODO: this can be implemented more elegantly by setting interactions when the map is created.
		if ( $params->get('jtg_param_allow_keymove') == "0" ) 
		{
			$control .= "		olmap.getInteractions().forEach(function(interaction) { ".
				"  if (interaction instanceof ol.interaction.KeyboardPan) { interaction.setActive(false); } }, this);\n";
			$control .= "		olmap.getInteractions().forEach(function(interaction) { ".
				"  if (interaction instanceof ol.interaction.KeyboardZoom) { interaction.setActive(false); } }, this);\n";
		}

		if ( $params->get('jtg_param_allow_mousemove') == "0" ) 
		{
			$control .= "		olmap.getInteractions().forEach(function(interaction) { ".
				"  if (interaction instanceof ol.interaction.MouseWheelZoom) { interaction.setActive(false); } }, this);\n";
			$control .= "		olmap.getInteractions().forEach(function(interaction) { ".
				"  if (interaction instanceof ol.interaction.Pointer) { interaction.setActive(false); } }, this);\n";
		}
		$control .= "// <!-- parseOLMapControl END -->\n";

		return $control;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	private function markerFunctionOL()
	{
		$map = "// <!-- markerFunctionCOM_JTG BEGIN -->\n";
		/*
		 $map .= "function createMarker(point,html) {\n";
		$map .= "var marker = new GMarker(point);\n";
		$map .= "GEvent.addListener(marker, 'click', function() {\n";
				$map .= "marker.openInfoWindowHtml(html);\n";
				$map .= "});\n";
		$map .= "return marker;\n";
		$map .= "}\n";
		*/
		$map .= "// <!-- markerFunctionCOM_JTG END -->\n";

		return $map;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	private function parseOLMap()
	{
		$string = "// <!-- parseOLMap BEGIN -->\n";

		$string .= "// <!-- parseOLMap END -->\n";

		return $string;
	}

	/**
	 * function_description
	 *
	 * @return array
	 */
	private function parseXMLlinesOL($params)
	{
		// 	global $jtg_microtime;

		$cfg = JtgHelper::getConfig();
		$iconpath = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";

		$link = JUri::current();

		$string_se = "";
		$center = "";

		$center .= "\n// <!-- parseOLTrack BEGIN -->\n";
		$center .= "var gpsTrack = new ol.layer.Vector({ \n";
$center .= "           source: new ol.source.Vector(),
                // {\n"; // MvL: need to complete this!  // NB: GPX converter does not work for routes
                //$center .= "             url: '".JUri::Root()."images/jtrackgallery/uploaded_tracks/$this->trackfilename',\n";
                //$center .= "             format: new ol.format.GPX() }),\n";
		$center .= "             style: new ol.style.Style({\n";
		$center .= "             stroke: new ol.style.Stroke({\n";
		$center .= "             color: '#ff00ff', width: 5 }) })\n";
		$center .= "           });\n";
		$center .= "    olmap.addLayer(gpsTrack);\n";
		// GPX parsing here is used for routes, for the animated cursor, and for the graphs
		// TODO: set opacity to 0.7; this is now part of the color
		$center .= "longitudeData = $this->longitudeData;\n";
		$center .= "latitudeData = $this->latitudeData;\n";
		$center .= "var points = [];\n";
		$center .= "for (var i = 0; i < longitudeData.length; i++) {\n";
                $center .= "    points.push(ol.proj.fromLonLat([longitudeData[i], latitudeData[i]], olview.getProjection()));\n";
                $center .= "}\n";
                $center .= "    gpsTrack.getSource().addFeature(new ol.Feature({geometry: new ol.geom.LineString(points)}));\n";
		$track_name = htmlentities($this->trackname, ENT_QUOTES, 'UTF-8');
                if (($params === false) OR ($params->get('jtg_param_disable_map_animated_cursor') == "0") ) {
			$center .= "\n// <!-- parseOLMapAnimatedCursorLayer BEGIN -->\n";
			$center .= "urlbase = '".JUri::base()."';\n"; // needed in animatedCurser.js
			$center .= "animatedCursorLayer = new ol.layer.Vector({ ".
				"    title: \"$track_name\",\n".
				"    source: new ol.source.Vector(),\n".
				"    style: animated_cursor_style,\n".
				"    visible: false\n".
				"});\n";
			$center .= "animatedCursorLineFeature = new ol.Feature({ \n";
			$center .= "     geometry: new ol.geom.LineString(points)});\n";
			$center .= "animatedCursorLineFeature.setId('cursorTrack');\n";
			$center .= "animatedCursorLayer.getSource().addFeature(animatedCursorLineFeature);\n";
			$center .= "animatedCursorLayer.gpxPoints = points;\n";
			$center .= "olmap.addLayer(animatedCursorLayer);\n";

			/* Add AnimatedCursor
		 	* MUST be added after olmap.zoomToExtent
		 	*/
			$document = JFactory::getDocument();
			$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/animatedCursor.js');

			$center .= "\n// <!-- parseOLMapAnimatedCursorIcon BEGIN -->\n";
                	$center .= "animatedCursorIcon = new ol.geom.Point( ol.proj.fromLonLat([".$this->track[1]->start."], olview.getProjection()));\n";
                	// setting the style directly for a feature does not seem to work...
                	$center .= "animatedCursorLayer.getSource().addFeature( new ol.Feature( { geometry: animatedCursorIcon } ) );\n";
			$center .= "// <!-- parseOLMapAnimatedCursorIcon END -->\n";
		}
		else
		{
			$center .= "<!-- AnimatedCursorLayer not activated -->\n";
		}

		$center .= "\n// <!-- parseOLTrack END -->\n";

		$string = "// <!-- parseXMLlines BEGIN -->\n";

		if ($this->trackCount == 0)
		{
			return;
		}

		$tracksColors = $this->calculateAllColors($this->trackCount);

                $string .= "var startMarkers = new ol.layer.Vector( {\n".
                           "   source: new ol.source.Vector(),\n".
                           "   style: new ol.style.Style({\n".
                           "      image: new ol.style.Icon({ src: '$iconpath/trackStart.png',\n".
                           "           anchorOrigin: 'bottom-left', anchor: [0,0] })\n})\n});\n";
                $string .= "var endMarkers = new ol.layer.Vector( {\n".
                           "   source: new ol.source.Vector(),\n".
                           "   style: new ol.style.Style({\n".
                           "      image: new ol.style.Icon({ src: '$iconpath/trackDest.png',\n".
                           "           anchorOrigin: 'bottom-right', anchor: [0,0] })\n})\n});\n";

		for ($i = 1; $i <= $this->trackCount; $i++)
		{
			$m = microtime(true);
			$coords = $this->track[$i]->coords;
			$subid = $link . "&amp;subid=" . $i;
			$color = "#" . $tracksColors[$i - 1];

                        $string_se .= " startMarkers.getSource().addFeature( new ol.Feature({ \n".
                                      "      geometry: new ol.geom.Point(ol.proj.transform( [".$this->track[$i]->start."],\n".
                                      "           'EPSG:4326',   'EPSG:3857')),\n".
                                      "      name: 'Start $i'\n".
                                      " }) );\n";
                        $string_se .= " endMarkers.getSource().addFeature( new ol.Feature({ \n".
                                      "      geometry: new ol.geom.Point(ol.proj.transform( [".$this->track[$i]->stop."],\n".
                                      "           'EPSG:4326',   'EPSG:3857')),\n".
                                      "      name: 'End $i'\n".
                                      " }) );\n";

		}
                $string_se .= "olmap.addLayer(startMarkers);\n";
                $string_se .= "olmap.addLayer(endMarkers);\n";
		$string .= $string_se;
		$string .= "// <!-- parseXMLlines END -->\n";

		$center .= "// <!-- parseOLMapCenterSingleTrack BEGIN -->\n";
                $center .= "olview.fit( ol.proj.transformExtent( [ ".
                           "$this->bbox_lon_min, ".
                           "$this->bbox_lat_min, $this->bbox_lon_max, ".
                           "$this->bbox_lat_max ], 'EPSG:4326', olview.getProjection()), {padding: [50, 50, 50, 75]} );\n";
                // Add button to 'zoom to fit':
                $center .= "olmap.addControl( new ol.control.ZoomToExtent( {extent: olview.calculateExtent()} ) );\n";
		$center .= "// <!-- parseOLMapCenterSingleTrack END -->\n";

		return array( "coords" => $string, "center" => $center );
	}

	/**
	 * function_description
	 *
	 * @param   string  $file  param_description
	 *
	 * @return array
	 */
	public function giveClickLinks($file)
	{
		$i = 0;
		$links = array();
		$this->gpsFile = $file;

		while (true)
		{
			$coords = $this->getCoords($file, $i);

			if ( $coords == false )
			{
				break;
			}

			$xml = $this->loadFile();
			$xml = $xml->trk[$i];
			$link = JFactory::getURI();
			$link = $link->_uri;
			$link = $link . "&amp;subid=" . $i;
			$name = (string) $xml->name;
			$links[$i]['link'] = $link;
			$links[$i]['name'] = $name;
			$i++;
		}

		return $links;
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
