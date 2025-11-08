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

namespace Jtg\Component\Jtg\Site\View\Jtg;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;
use Jtg\Component\Jtg\Site\Helpers\LayoutHelper;
use Jtg\Component\Jtg\Site\View\JtgView;

/**
 * HTML View class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class HtmlView extends JtgView
{
	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	public function display($tpl = null)
	{
		// ToDo split in jtg and geoposition
		$user = Factory::getUser();
		$uid = $user->id;

		$app = Factory::getApplication();

		// Load Openlayers stylesheet first (for overriding)
		$document = Factory::getDocument();
		$cfg = JtgHelper::getConfig();
		$tmpl = strlen($cfg->template) ? $cfg->template : 'default';
		$document->addStyleSheet(Uri::root(true) . '/media/com_jtg/js/openlayers/ol.css');
		if (version_compare(JVERSION, '4.0', 'lt')) $document->addStyleSheet(Uri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/filter_box_j3.css');
		// Then load jtg_map stylesheet
		$document->addStyleSheet(Uri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');

		// Then override style with user templates
		$template = $app->getTemplate();
		$template_jtg_map_style = 'templates/' . $template . '/css/jtg_map_style.css';

		if ( File::exists($template_jtg_map_style))
		{
			$document->addStyleSheet(Uri::root(true) . '/templates/' . $template . '/css/jtg_map_style.css');
		}

		$sitename = $document->getTitle() . " - " . $app->getCfg('sitename');
		$mapsxml = JPATH_COMPONENT_ADMINISTRATOR . '/views/maps/maps.xml';
		$this->params_maps = new Registry('com_jtg', $mapsxml);
		$params = ComponentHelper::getParams('com_jtg');
		LayoutHelper::parseMap($document);

		// Show Tracks in Overview-Map?
		$showtracks = (bool) $params->get('jtg_param_tracks');

		$catid = (Factory::getApplication()->input->getInt('cat', null)); // get category ID
		$model = $this->getModel();
		$cats = $model->getCatsData(false, $catid);
		$sortedcats = $model->getCatsData(true, $catid);
		$where = LayoutHelper::filterTracks($cats);
		if (count($cats) == 0) {
			$app->enqueueMessage(Text::_('COM_JTG_CAT_NOT_FOUND'));
		}

		$access = JtgHelper::giveAccessLevel(); // User access level
		$otherfiles = $params->get('jtg_param_otherfiles');// Access level defined in backend
		$mayisee = JtgHelper::MayIsee($where, $access, $otherfiles);
		$boxlinktext = array(
				0 => Text::_('COM_JTG_LINK_VIEWABLE_FOR_PUBLIC'),
				1 => Text::_('COM_JTG_LINK_VIEWABLE_FOR_REGISTERED'),
				2 => Text::_('COM_JTG_LINK_VIEWABLE_FOR_SPECIAL'),
				9 => Text::_('COM_JTG_LINK_VIEWABLE_FOR_PRIVATE')
		);

		$lh = '';

		if ((bool) $params->get('jtg_param_lh'))
		{
			$lh .= LayoutHelper::navigation();
		}
		else
		{
			$lh = null;
		}
		if (Factory::getApplication()->input->getBool('introtext'))
		{
			$intro_text = $params->get('intro_text_overview');
			if (strlen($intro_text))
			{
				$lh .= '<div class="intro_text_overview">';
				$lh .= $intro_text;
				$lh .= '</div>';
			}
		}
		$footer = LayoutHelper::footer();
		$disclaimericons = LayoutHelper::disclaimericons();
		$rows = $model->getTracksData(null, null, $where);

		$geo = Route::_('index.php?option=com_jtg&view=jtg&layout=geo', false);
		$this->newest =	null;

		if ($params->get('jtg_param_newest') != 0)
		{
			$this->newest =	LayoutHelper::parseTopNewest($where, $mayisee, $model, $params->get('jtg_param_newest'));
		}

		$this->hits = null;

		if ($params->get('jtg_param_mostklicks') != 0)
		{
			$this->hits = LayoutHelper::parseTopHits($where, $mayisee, $model, $params->get('jtg_param_mostklicks'));
		}

		$this->best = null;

		if ($params->get('jtg_param_best') != 0)
		{
			$this->best = LayoutHelper::parseTopBest($where, $mayisee, $model, $params->get('jtg_param_best'), $params->get('jtg_param_vote_show_stars'));
		}

		$this->rand = null;

		if ($params->get('jtg_param_rand') != 0)
		{
			$this->rand = LayoutHelper::parseTopRand($where, $mayisee, $model, $params->get('jtg_param_rand'));
		}

		$toptracks = LayoutHelper::parseToptracks($params);
		$published = "\n ( (a.published = 1 AND a.hidden = 0) OR ( a.uid='$uid' ) ) ";
		switch ($mayisee)
		{
			case null:
				switch ($where)
				{
					case "":
						$where = " WHERE " . $published;
						break;
					default:
						$where = " WHERE " . $where . " AND " . $published;
						break;
				}
				break;
			default:
				$where = " WHERE " . $mayisee . " AND " . $published;
				break;
		}

		if ($app->getParams()->get('dpcallocs_overview'))
		{
			// Get locations from DPCalendar
			$dpcalmodel = $this->getModel("DPCalLocations");
			$this->dpcallocs = $dpcalmodel->getItems(); 
		}
		else $this->dpcallocs = array();

		$zoomlevel = $app->input->getInt('map_zoom',$app->getParams()->get('map_zoom'));
		if (empty($zoomlevel)) $zoomlevel = 6;
		$this->lh = $lh;
		$this->boxlinktext = $boxlinktext;
		$this->footer = $footer;
		$this->disclaimericons = $disclaimericons;
		$this->rows = $rows;
		$this->where = $where;
		$this->cats = $cats;
		$this->sortedcats = $sortedcats;
		$this->cfg = $cfg;
		$this->geo = $geo;
		$this->toptracks = $toptracks;
		$this->showtracks = $showtracks;
		$this->params = $params;
		$this->zoomlevel = $zoomlevel;

		parent::display($tpl);
	}
}
