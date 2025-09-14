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
 * @link        https://mastervanleeuwen.github.io/J-TrackGallery/ 
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

jimport('joomla.application.component.view');

/**
 * JtgViewUser class @ see JViewLegacy
 * HTML View class for the jtg component
 *
 * Returns the specified model
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgViewUser extends JViewLegacy
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
			return Text::_('COM_JTG_NO_RESSOURCE');
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

		$app = Factory::getApplication();
		$app->setUserState("jtg.files.layout",$this->getLayout());
		$this->canDo = JHelperContent::getActions('com_jtg');

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		$this->params = $app->getParams();
		if ( $this->params->get("jtg_param_lh") == 1 )
		{
			$this->menubar = LayoutHelper::navigation();
		}
		else
		{
			$this->menubar = null;
		}

		$footer = LayoutHelper::footer();
		$model = $this->getModel();
		$cfg = JtgHelper::getConfig();
		$pathway = $app->getPathway();
		$pathway->addItem(Text::_('COM_JTG_MY_FILES'), '');
		$sitename = $app->getCfg('sitename');
		$document = $app->getDocument();
		$document->setTitle(Text::_('COM_JTG_MY_FILES') . " - " . $sitename);

		$order = $app->input->getWord('order', 'order');

		$filter_order = $app->getUserStateFromRequest("com_jtg.filter_order", 'filter_order', '', 'word');
		$filter_order_Dir = $app->getUserStateFromRequest("com_jtg.filter_order_Dir", 'filter_order_Dir', '', 'word');
		$action = JRoute::_('index.php?option=com_jtg&view=user', false);

		$lists['order'] = $filter_order;
		$lists['order_Dir'] = $filter_order_Dir;

		$cats = JtgModeljtg::getCatsData(true);
		$sortedter = JtgModeljtg::getTerrainData(true);
		$this->sortedter = $sortedter;
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
