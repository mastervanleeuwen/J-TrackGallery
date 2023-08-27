<?php

/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        https://github.com/mastervanleeuwen/J-TrackGallery
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

jimport('joomla.application.component.view');
require_once JPATH_COMPONENT . '/helpers/maphelper.php';

/**
 * JtgViewTrack class @ see JViewLegacy
 * HTML View class for the jtg component
 *
 * Returns the specified model
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgViewTrack extends JViewLegacy
{
	/**
	 * Returns true|false if user is allowed to see the file
	 *
	 * @param   object  $param  param_description
	 *
	 * @return <bool>
	 */
	function maySeeSingleFile($param)
	{
		if (!isset($param->track))
		{
			return JText::_('COM_JTG_NO_RESSOURCE');
		}

		if ($this->cfg->access == 0)
		{
			// Track access is not used
			return true;
		}

		$published = (bool) $param->track->published;
		$access = (int) $param->track->access;
		/* $access:
		0 = public
		1 = registered
		2 = special // Ie admin
		9 = private
		*/
		$uid = Factory::getUser()->id;

		if (Factory::getUser()->get('isRoot'))
		{
			$admin = true;
		}
		else
		{
			$admin = false;
		}

		if ($uid)
		{
			$registred = true;
		}
		else
		{
			$registred = false;
		}
		$owner = (int) $param->track->uid;

		if ( ( $access == 9 ) AND ( $uid != $owner ) )

		{
			// Private only
			return false;
		}

		if (($registred) AND ($uid == $owner))
		{
			$myfile = true;
		}
		else
		{
			$myfile = false;
		}

		if ($registred)
		{
			if ($myfile)
			{
				return true;
			}
			elseif (!$published)
			{
				return false;
			}
			elseif ($access != 2)
			{
				return true;
			}
			elseif (($admin) AND ($access == 2))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if (!$published)
			{
				return false;
			}
			elseif ($access == 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description$gps
	 */
	public function display($tpl = null)
	{
		$file = JPATH_SITE . "/components/com_jtg/models/jtg.php";
		require_once $file;

		$mainframe = Factory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// Code support for joomla version greater than 3.0
		if (JVERSION >= 3.0)
		{
			JHtml::_('jquery.framework');
			JHtml::script(Juri::base() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
		}
		else
		{
			JHtml::script('jquery.js', 'components/com_jtg/assets/js/', false);
			JHtml::script('jquery.MultiFile.js', 'components/com_jtg/assets/js/', false);
		}

		$cfg = JtgHelper::getConfig();
		$this->cfg = $cfg;

		$document = $mainframe->getDocument();

		// Then load jtg_map stylesheet
		$tmpl = strlen($cfg->template) ? $cfg->template : 'default';

		// Load Openlayers stylesheet first (for overriding)
		$document->addStyleSheet(JUri::root(true) . '/media/com_jtg/js/openlayers/ol.css');

		$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');

		$this->params = JComponentHelper::getParams('com_jtg');
		//$this->id = $mainframe->getInput()->getInt('id');
		$this->id = $mainframe->input->getInt('id');
		$uid = Factory::getUser()->id;

		$this->footer = LayoutHelper::footer();

		$model = $this->getModel();
		$gpsData = new GpsDataClass($cfg->unit);

		$this->coords = "";
		if (isset ($this->id))
		{
			// In the form view, $id would not be available for new files
			$document->addScript( JUri::root(true) . '/media/com_jtg/js/openlayers/ol.js');  // Load OpenLayers
			$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/jtg.js',array('version'=>'auto'));
			$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/geolocation.js',array('version'=>'auto'));
			if ($this->params->get('jtg_param_disable_map_animated_cursor') == 0) {
				$document->addScript(JUri::root(true) . '/components/com_jtg/assets/js/animatedCursor.js');
			}
			$this->track = $model->getFile( $this->id );
			$file = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . strtolower($this->track->file);
			$gpsData->loadFileAndData($file, $this->track->file);
			$this->imageList = $model->getImages($this->id);
			$this->mapJS = '';
			if (!$gpsData->displayErrors())
			{
				$makepreview = false;
				if ($this->getLayout() == 'form') $makepreview = true;
				$mapids = [$this->track->default_map];
				$colors = array();
				if (!is_null($this->params->get('jtg_param_track_color'))) $colors[] = $this->params->get('jtg_param_track_color');
				$this->mapJS = JtgMapHelper::parseTrackMapJS($gpsData,$this->track->id, $this->track->default_map, $this->imageList, $makepreview, true, JComponentHelper::getParams('com_jtg')->get('jtg_param_show_layerswitcher'),$colors,'jtg_map');

				$this->gpsTrack = $gpsData;
				$this->coords = $gpsData->allCoords;
				$this->longitudeData = $gpsData->longitudeData;
				$this->latitudeData = $gpsData->latitudeData;
				// Charts
				$this->beatdata = $gpsData->beatData;
				$this->heightdata = $gpsData->elevationData;
				$this->speeddata = $gpsData->speedData;
				$this->pacedata = $gpsData->paceData;
				$this->date = $this->track->date?JHtml::_('date', $this->track->date, JText::_('COM_JTG_DATE_FORMAT_LC4')):'';
				if ( count($this->imageList) > 0) {
		         $this->images = true;
				}
			}
			else {
				$mainframe->enqueueMessage($gpsData->displayErrors(),'Error');
			}
		}
		if (! isset($this->track) )
		{ // New track; set some defaults
			$track = array('access' => '0', 'catid' => null, 'terrain' => null, 'published' => '1', 'hidden' => '0', 'level' => '3', 'default_map' => null);
         $this->track = (object) $track;
		}

		if ( $this->params->get("jtg_param_lh") == 1 )
		{
			$this->menubar = LayoutHelper::navigation();
		}
		else
		{
			$this->menubar = null;
		}

		$this->canDo = JHelperContent::getActions('com_jtg');
		$this->model = $model;
		$this->speedDataExists = $gpsData->speedDataExists;
		$this->elevationDataExists = $gpsData->elevationDataExists;
		$this->beatDataExists = $gpsData->beatDataExists;

		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $service  param_description
	 *
	 * @return return_description
	 */
	function approach($service)
	{
		// 	$userparams = explode("\n", $this->user->params);
		$app = Factory::getApplication();
		$lang = $app->getLanguage()->getTag();
		$user = Factory::getUser();

		$lang = explode("-", $lang);
		$userlang = $lang[0];

		// Allowed language from ORS
		$availablelang = array ( 'de', 'en', 'it', 'fr', 'es' );

		if (in_array($userlang, $availablelang))
		{
			$lang = $userlang;
		}
		else
		{
			$lang = "en";
		}

		$imgdir = JUri::base() . "components/com_jtg/assets/images/approach/" . $this->cfg->routingiconset . "/";
		$routservices = array();
		$return = "";

		switch ($service)
		{
			case 'ors' :
				// OpenRouteService:
				$link = $this->model->approachors($this->track->start_n, $this->track->start_e, $lang);
				$routservices = array (
						array (
								"img" => $imgdir . "car.png",
								"name" => JText::_('COM_JTG_BY_CAR'),
								array (
										array (
												"Fastest",
												JText::_('COM_JTG_FASTEST')
										),
										array (
												"Shortest",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						),
						array (
								"img" => $imgdir . "bike.png",
								"name" => JText::_('COM_JTG_BY_BICYCLE'),
								array (
										array (
												"BicycleSafety",
												JText::_('COM_JTG_SAFEST')
										),
										array (
												"Bicycle",
												JText::_('COM_JTG_SHORTEST')
										),
										array (
												"BicycleMTB",
												JText::_('COM_JTG_BY_MTB')
										),
										array (
												"BicycleRacer",
												JText::_('COM_JTG_BY_RACERBIKE')
										)
								)
						),
						array (
								"img" => $imgdir . "foot.png",
								"name" => JText::_('COM_JTG_BY_FOOT'),
								array (
										array (
												"Pedestrian",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						)
				);
				break;

			case 'cm' :
				// CloudMade:
				$link = $this->model->approachcm($this->track->start_n, $this->track->start_e, $lang);
				$routservices = array (
						array (
								"img" => $imgdir . "car.png",
								"name" => JText::_('COM_JTG_CAR'),
								array (
										array (
												"car",
												JText::_('COM_JTG_FASTEST')
										),
										array (
												"car/shortest",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						),
						array (
								"img" => $imgdir . "bike.png",
								"name" => JText::_('COM_JTG_BY_BICYCLE'),
								array (
										array (
												"bicycle",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						),
						array (
								"img" => $imgdir . "foot.png",
								"name" => JText::_('COM_JTG_BY_FOOT'),
								array (
										array (
												"foot",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						)
				);
				break;

			case 'cmkey' :
				// CloudMade with API-Key:
				$link = $this->model->approachcmkey($this->track->start_n, $this->track->start_e, $lang);
				$routservices = array (
						array (
								"img" => $imgdir . "car.png",
								"name" => JText::_('COM_JTG_BY_CAR'),
								array (
										array (
												"car",
												JText::_('COM_JTG_FASTEST')
										),
										array (
												"car/shortest",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						),
						array (
								"img" => $imgdir . "bike.png",
								"name" => JText::_('COM_JTG_BY_BICYCLE'),
								array (
										array (
												"bicycle",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						),
						array (
								"img" => $imgdir . "foot.png",
								"name" => JText::_('COM_JTG_BY_FOOT'),
								array (
										array (
												"foot",
												JText::_('COM_JTG_SHORTEST')
										)
								)
						)
				);
				break;

			case 'easy' :
				$cfg = JtgHelper::getConfig();
				$link = $this->model->approacheasy($this->track->start_n, $this->track->start_e, $lang);
				break;
		}

		foreach ($routservices AS $shifting)
		{
			$return .= "			<td>
			<center>
			<img src=\"" . $shifting['img'] . "\" alt=\"" . $shifting['name'] . "\" title=\"" . $shifting['name'] . "\" />
			</center>
			<ul>\n";

			foreach ($shifting[0] AS $service)
			{
				$return .= "					<li>
				<a href=\"" . $link . $service[0] . "\" target=\"_blank\">" . $service[1] . "</a>
				</li>\n";
			}

			$return .= "				</ul>\n			</td>\n";
		}

		return $return;
	}


	/**
	 * function_description
	 *
	 *
	 * @param   unknown_type  $template  param_description
	 * @param   unknown_type  $content   param_description
	 * @param   unknown_type  $linkname  param_description
	 * @param   unknown_type  $only      param_description
	 *
	 * @return return_description
	 */
	public function buildImageFiletypes($track, $wp, $route, $cache, $roundtrip, $iconheight, $hide_icon_istrack, $hide_icon_is_wp, $hide_icon_is_route, $hide_icon_isgeocache, $hide_icon_isroundtrip)
	{
		$height = ($iconheight > 0? ' style="max-height:' . $iconheight . 'px;" ' : ' ');
		$imagelink = "";
		$iconpath = JUri::root()."/components/com_jtg/assets/images";
		if (!$hide_icon_istrack)
		{
			$foundtrackroute = 0;
			if ( ( isset($track) ) AND ( $track == "1" ) )
			{
				$imagelink .= "<img $height src =\"$iconpath/track1.png\" title=\"" . JText::_('COM_JTG_ISTRACK1') . "\"/>\n";
                                $foundtrackroute = 1;
			}

			if ( ( isset($route) ) AND ( $route == "1" ) )
			{
				$imagelink .= "<img $height src =\"$iconpath/route1.png\" title=\"" . JText::_('COM_JTG_ISROUTE1') . "\"/>\n";
				$foundtrackroute = 1;
			}

			if ( !$foundtrackroute )
				$imagelink .= "<img $height src =\"$iconpath/track0.png\" title=\"" . JText::_('COM_JTG_ISTRACK0') . "\"/>\n";
		}

		if (! $hide_icon_isroundtrip)
		{
			if ( ( isset($roundtrip) ) AND ( $roundtrip == "1" ) )
			{
				$m = (string) 1;
			}
			else
			{
				$m = (string) 0;
			}

			if ( isset($roundtrip) )
			{
				$imagelink .= "<img $height src =\"$iconpath/roundtrip$m.png\" title=\"" . JText::_('COM_JTG_ISROUNDTRIP' . $m) . "\"/>\n";
			}
			else
			{
				$imagelink .= "<img $height src =\"$iconpath/roundtrip$m.png\" title=\"" . JText::_('COM_JTG_DKROUNDTRIP') . "\"/>\n";
			}
		}

		if (!$hide_icon_is_wp)
		{
			if ( ( isset($wp) ) AND ( $wp == "1" ) )
			{
				$m = (string) 1;
			}
			else
			{
				$m = (string) 0;
			}

			if ( isset($wp) )
			{
				$imagelink .= "<img $height src =\"$iconpath/wp$m.png\" title=\"" . JText::_('COM_JTG_ISWP' . $m) . "\"/>\n";
			}
			else
			{
				$imagelink .= "<img $height src =\"$iconpath/wp$m.png\" title=\"" . JText::_('COM_JTG_DKWP') . "\"/>\n";
			}
		}

		if (!$hide_icon_isgeocache)
		{
			if ( ( isset($cache) ) AND ( $cache == "1" ) )
			{
				$m = (string) 1;
			}
			else
			{
				$m = (string) 0;
			}

			if ( isset($cache) )
			{
				$imagelink .= "<img $height src =\"$iconpath/cache$m.png\" title=\"" . JText::_('COM_JTG_ISCACHE' . $m) . "\"/>\n";
			}
			else
			{
				$imagelink .= "<img $height src =\"$iconpath/cache$m.png\" title=\"" . JText::_('COM_JTG_DKCACHE') . "\"/>\n";
			}
		}

		return $imagelink;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $template  param_description
	 * @param   unknown_type  $content   param_description
	 * @param   unknown_type  $linkname  param_description
	 * @param   unknown_type  $only      param_description
	 *
	 * @return return_description
	 */
	protected function parseTemplate($template, $content = null, $linkname = null, $only = null, $printbutton = false)
	{
		$tmpl = strlen($this->cfg->template) ? $this->cfg->template : 'default';
		$templatepath = JPATH_BASE . "/components/com_jtg/assets/template/" . $tmpl . '/';

		if ((!$content)AND($content != ""))
		{
			include_once $templatepath . $template . "_" . $only . ".php";

			return;
		}
		$TLopen = $template . "_open";
		$TLclose = $template . "_close";
		$function = "ParseTemplate_" . $TLopen;
		defined(strtoupper('_ParseTemplate_' . $template . '_open')) or include_once $templatepath . $TLopen . ".php";
		$return = $function ($linkname, $printbutton);
		$return .= $content;
		$function = "ParseTemplate_" . $TLclose;
		defined(strtoupper('ParseTemplate_' . $template . '_close')) or include_once $templatepath . $TLclose . ".php";
		$return .= $function ($linkname);
		return $return;
	}
}
