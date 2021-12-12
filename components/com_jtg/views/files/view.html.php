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

jimport('joomla.application.component.view');

/*
 * Pagination previously made with J!2.5 pagination
 * jimport('joomla.html.pagination');
 * Now include a modified JPagination class working under J!2.5 and J3.x
 */
//include_once JPATH_BASE . '/components/com_jtg/views/files/pagination.php';


/**
 * JtgViewFiles class @ see JViewLegacy
 * HTML View class for the jtg component
 *
 * Returns the specified model
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgViewFiles extends JViewLegacy
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
		$uid = JFactory::getUser()->id;

		if (JFactory::getUser()->get('isRoot'))
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
	 *  Reset the filter form and corresponding state
	 *  Used between layout switches
	 *
	 */
	protected function resetFilter($catid = null)
	{
		$this->get("State"); // need to get the state before we can change it
		$filterform = $this->get('FilterForm');
		$filterform->setValue("search","filter",null);
		$this->getModel()->setState("filter.search",null);
		$filterform->setValue("trackcat","filter",$catid);
		$this->getModel()->setState("filter.trackcat",$catid);
		$filterform->setValue("tracklevel","filter",null);
		$this->getModel()->setState("filter.tracklevel",null);
		$filterform->setValue("mindist","filter",null);
		$this->getModel()->setState("filter.mindist",null);
		$filterform->setValue("maxdist","filter",null);
		$this->getModel()->setState("filter.maxdist",null);
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

		$app = JFactory::getApplication();
		if ($app->getUserState("jtg.files.layout") && $this->getLayout() != $app->getUserState("jtg.files.layout")) {
			// Reset filters when layout changes (list->files and vice versa)
			$this->resetFilter($app->input->get('cat'));
		}
		$app->setUserState("jtg.files.layout",$this->getLayout());
		$this->canDo = JHelperContent::getActions('com_jtg');

		if ($this->getLayout() == 'list')
		{
			$this->state = $this->get('State');
			$this->items = $this->get('Items');
			$this->pagination = $this->get('Pagination');
			$this->filterForm = $this->get('FilterForm');
			$this->activeFilters = $this->get('ActiveFilters');

			$this->_displayList($tpl);

			return;
		}

		if ($this->getLayout() == 'user')
		{
			$this->state = $this->get('State');
			$this->items = $this->get('Items');
			$this->pagination = $this->get('Pagination');
			$this->_displayUserTracks($tpl);

		return;
		}

		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	function _displayList($tpl)
	{
		$mainframe = JFactory::getApplication();
		$option = JFactory::getApplication()->input->get('option');

		$model = $this->getModel();
		$cache = JFactory::getCache('com_jtg');
		$sortedcats = JtgModeljtg::getCatsData(true);
		$sortedter = JtgModeljtg::getTerrainData(true);
		$user = JFactory::getUser();
		$uid = $user->get('id');
		$gid = $user->get('gid');
		$deletegid = $user->get('deletegid');
		$lh = LayoutHelper::navigation();
		$footer = LayoutHelper::footer();
		$cfg = JtgHelper::getConfig();
		$pathway = $mainframe->getPathway();
		$pathway->addItem(JText::_('COM_JTG_GPS_FILES'), '');
		$sitename = $mainframe->getCfg('sitename');
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JTG_GPS_FILES') . " - " . $sitename);
		$params = $mainframe->getParams();

		//Following variables used more than once
		$this->sortColumn 	= $this->state->get('list.ordering');
		$this->sortDirection	= $this->state->get('list.direction');

		$filter_order = $mainframe->getUserStateFromRequest("$option.filter_order", 'filter_order', '', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest("$option.filter_order_Dir", 'filter_order_Dir', '', 'cmd');

		$action = JRoute::_('index.php?option=com_jtg&view=files&layout=list', false);

		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;

		$this->sortedcats = $sortedcats;
		$this->sortedter = $sortedter;
		$this->lists = $lists;
		$this->uid = $uid;
		$this->gid = $gid;
		$this->deletegid = $deletegid;
		$this->lh = $lh;
		$this->footer = $footer;
		$this->action = $action;
		$this->cfg = $cfg;
		$this->params = $params;

		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	function _displayUserTracks($tpl)
	{
		$mainframe = JFactory::getApplication();
		$option = JFactory::getApplication()->input->get('option');
		$cache = JFactory::getCache('com_jtg');
		$lh = LayoutHelper::navigation();
		$footer = LayoutHelper::footer();
		$model = $this->getModel();
		$cfg = JtgHelper::getConfig();
		$pathway = $mainframe->getPathway();
		$pathway->addItem(JText::_('COM_JTG_MY_FILES'), '');
		$sitename = $mainframe->getCfg('sitename');
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_JTG_MY_FILES') . " - " . $sitename);

		$order = JFactory::getApplication()->input->getWord('order', 'order');

		$filter_order = $mainframe->getUserStateFromRequest("$option.filter_order", 'filter_order', '', 'word');
		$filter_order_Dir = $mainframe->getUserStateFromRequest("$option.filter_order_Dir", 'filter_order_Dir', '', 'word');
		$action = JRoute::_('index.php?option=com_jtg&view=files&layout=user', false);

		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;

		$cats = JtgModeljtg::getCatsData(true);
		$sortedter = JtgModeljtg::getTerrainData(true);
		$params = $mainframe->getParams();
		$this->params = $params;
		$this->sortedter = $sortedter;
		$this->lh = $lh;
		$this->cats = $cats;
		$this->footer = $footer;
		$this->action = $action;
		$this->cfg = $cfg;
		$this->lists = $lists;

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
		$lang = JFactory::getLanguage();
		$user = JFactory::getUser();

		/*
		 if ($user->id == 0) // User is public
		{
		$config = JFactory::getConfig();
		$lang = $config->getValue('language');
		}
		else
		{
		$user = JFactory::getUser();
		$lang = $user->getParam('language', 'the default');
		}
		*/

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
