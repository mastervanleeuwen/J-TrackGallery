<?php

/**
 * @component  J!Track Gallery (jtg) for Joomla! 3.x and 4.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @copyright   2023 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 *
 */

namespace Jtg\Component\Jtg\Site\View\Tag;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;
use Jtg\Component\Jtg\Site\Helpers\LayoutHelper;
use Jtg\Component\Jtg\Site\Model\JtgModel;
use Jtg\Component\Jtg\Site\View\JtgView;

/**
 * JtgViewTag class 
 * HTML View class for the jtg component
 *
 * Returns the specified model
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.9.35
 */
class HtmlView extends JtgView
{
	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description$gps
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$tagid = $app->input->get('id');
		$this->canDo = ContentHelper::getActions('com_jtg');

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$model = $this->getModel();

		$option = $app->input->get('option');
		$cfg = JtgHelper::getConfig();
		$document = $app->getDocument();
		$sitename = $app->getCfg('sitename');

		if (is_null($tagid))
		{
			$app->enqueueMessage('No tag selected','Error');
		}
		if (version_compare(JVERSION,'4.0','ge')) {
			$this->title = htmlspecialchars($model->getTable()->getTagsHelper()->getTagNames(array($tagid))[0], ENT_QUOTES, 'UTF-8');
		}
		else {
			$tagsHelper = new JHelperTags;
			$this->title = htmlspecialchars($tagsHelper->getTagNames(array($tagid))[0], ENT_QUOTES, 'UTF-8');
		}
		$document->setTitle($this->title . " - " . $sitename);
		$pathway = $app->getPathway();
		$pathway->addItem($this->title, '');

		$params = $app->getParams();
		$this->showmap = $params->get('jtg_param_cat_show_map',1);
		$this->showlist = $params->get('jtg_param_cat_show_list',1);

		if ($this->showmap) {
			LayoutHelper::parseMap($document); // Loads ol.js
			$tmpl = strlen($cfg->template) ? $cfg->template : 'default';
			$document->addStyleSheet(Uri::root(true) . '/media/com_jtg/js/openlayers/ol.css');
			$document->addStyleSheet(Uri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');

			$this->showtracks = (bool) $params->get('jtg_param_tracks');
			$this->zoomlevel = 6;
		}
		$model = $this->getModel();

		$sortedter = JtgModel::getTerrainData(true);
		$user = Factory::getUser();
		$uid = $user->get('id');
		$gid = $user->get('gid');
		$deletegid = $user->get('deletegid');
		$lh = LayoutHelper::navigation();
		$footer = LayoutHelper::footer();
		$cfg = JtgHelper::getConfig();
		$pathway = $app->getPathway();
		$pathway->addItem(Text::_('COM_JTG_GPS_FILES'), '');
		$sitename = $app->getCfg('sitename');
		$document = $app->getDocument();
		$document->setTitle(Text::_('COM_JTG_GPS_FILES') . " - " . $sitename);

		//Following variables used more than once
		$this->sortColumn 	= $this->state->get('list.ordering');
		$this->sortDirection	= $this->state->get('list.direction');

		$filter_order = $app->getUserStateFromRequest("$option.filter_order", 'filter_order', '', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest("$option.filter_order_Dir", 'filter_order_Dir', '', 'cmd');

		$action = Route::_('index.php?option=com_jtg&view=tagt&layout=default&id='.$tagid, false);

		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;

		$this->tagid = $tagid;
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
		$iconpath = Uri::root()."/components/com_jtg/assets/images";
		if (!$hide_icon_istrack)
		{
			$foundtrackroute = 0;
			if ( ( isset($track) ) AND ( $track == "1" ) )
			{
				$imagelink .= "<img $height src =\"$iconpath/track1.png\" title=\"" . Text::_('COM_JTG_ISTRACK1') . "\"/>\n";
                                $foundtrackroute = 1;
			}

			if ( ( isset($route) ) AND ( $route == "1" ) )
			{
				$imagelink .= "<img $height src =\"$iconpath/route1.png\" title=\"" . Text::_('COM_JTG_ISROUTE1') . "\"/>\n";
				$foundtrackroute = 1;
			}

			if ( !$foundtrackroute )
				$imagelink .= "<img $height src =\"$iconpath/track0.png\" title=\"" . Text::_('COM_JTG_ISTRACK0') . "\"/>\n";
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
				$imagelink .= "<img $height src =\"$iconpath/roundtrip$m.png\" title=\"" . Text::_('COM_JTG_ISROUNDTRIP' . $m) . "\"/>\n";
			}
			else
			{
				$imagelink .= "<img $height src =\"$iconpath/roundtrip$m.png\" title=\"" . Text::_('COM_JTG_DKROUNDTRIP') . "\"/>\n";
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
				$imagelink .= "<img $height src =\"$iconpath/wp$m.png\" title=\"" . Text::_('COM_JTG_ISWP' . $m) . "\"/>\n";
			}
			else
			{
				$imagelink .= "<img $height src =\"$iconpath/wp$m.png\" title=\"" . Text::_('COM_JTG_DKWP') . "\"/>\n";
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
				$imagelink .= "<img $height src =\"$iconpath/cache$m.png\" title=\"" . Text::_('COM_JTG_ISCACHE' . $m) . "\"/>\n";
			}
			else
			{
				$imagelink .= "<img $height src =\"$iconpath/cache$m.png\" title=\"" . Text::_('COM_JTG_DKCACHE') . "\"/>\n";
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
