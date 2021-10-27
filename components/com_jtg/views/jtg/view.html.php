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

/**
 * HTML View class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgViewjtg extends JViewLegacy
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
		$mainframe = JFactory::getApplication();
		$cfg = JtgHelper::getConfig();
		$gpsData = new GpsDataClass($cfg->unit);
		$document = JFactory::getDocument();
		$user = JFactory::getUser();
		$uid = $user->id;
		jimport('joomla.filesystem.file');

		// Load Openlayers stylesheet first (for overriding)
		$tmpl = ($cfg->template = "") ? $cfg->template : 'default';
      $document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/'.$tmpl.'/ol.css');
		if (version_compare(JVERSION, '4.0', 'lt')) $document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/filter_box_j3.css');
		// Then load jtg_map stylesheet
		$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');

		// Then override style with user templates
		$template = $mainframe->getTemplate();
		$template_jtg_map_style = 'templates/' . $template . '/css/jtg_map_style.css';

		if ( JFile::exists($template_jtg_map_style))
		{
			$document->addStyleSheet(JUri::root(true) . '/templates/' . $template . '/css/jtg_map_style.css');
		}

		$sitename = $document->getTitle() . " - " . $mainframe->getCfg('sitename');
		$mapsxml = JPATH_COMPONENT_ADMINISTRATOR . '/views/maps/maps.xml';
		$this->params_maps = new JRegistry('com_jtg', $mapsxml);
		$params = JComponentHelper::getParams('com_jtg');
		LayoutHelper::parseMap($document);

		// Show Tracks in Overview-Map?
		$showtracks = (bool) $params->get('jtg_param_tracks');

		$catid = (JFactory::getApplication()->input->getInt('cat', null)); // get category ID
		$model = $this->getModel();
		$cats = $model->getCatsData(false, $catid);
		$sortedcats = $model->getCatsData(true, $catid);
		$where = LayoutHelper::filterTracks($cats);
		if (count($cats) == 0) {
			$mainframe->enqueueMessage(JText::_('COM_JTG_CAT_NOT_FOUND'));
		}

		$access = JtgHelper::giveAccessLevel(); // User access level
		$otherfiles = $params->get('jtg_param_otherfiles');// Access level defined in backend
		$mayisee = JtgHelper::MayIsee($where, $access, $otherfiles);
		$boxlinktext = array(
				0 => JText::_('COM_JTG_LINK_VIEWABLE_FOR_PUBLIC'),
				1 => JText::_('COM_JTG_LINK_VIEWABLE_FOR_REGISTERED'),
				2 => JText::_('COM_JTG_LINK_VIEWABLE_FOR_SPECIAL'),
				9 => JText::_('COM_JTG_LINK_VIEWABLE_FOR_PRIVATE')
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

		$intro_text = $params->get('intro_text_overview');
		if ($intro_text && $this->getLayout() != 'map')
		{
			$lh .= '<div class="intro_text_overview">';
			$lh .= $intro_text;
			$lh .= '</div>';
		}
		if ($this->getLayout() == 'map') {
			// Map layout is default layout without intro text
			$tpl = null;
		}

		$footer = LayoutHelper::footer();
		$disclaimericons = LayoutHelper::disclaimericons();
		$rows = $model->getTracksData(null, null, $where);

		$geo = JRoute::_('index.php?option=com_jtg&view=jtg&layout=geo', false);
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

		if ($mainframe->getParams()->get('dpcallocs_overview'))
		{
			// Get locations from DPCalendar
			$dpcalmodel = $this->getModel("DPCalLocations");
			$this->dpcallocs = $dpcalmodel->getItems(); 
		}
		else $this->dpcallocs = array();

		$this->lh = $lh;
		$this->boxlinktext = $boxlinktext;
		$this->footer = $footer;
		$this->disclaimericons = $disclaimericons;
		$this->gpsData = $gpsData;
		$this->rows = $rows;
		$this->where = $where;
		$this->cats = $cats;
		$this->sortedcats = $sortedcats;
		$this->cfg = $cfg;
		$this->geo = $geo;
		$this->toptracks = $toptracks;
		$this->showtracks = $showtracks;
		$this->params = $params;
		$this->default_zoom = $mainframe->getParams()->get('map_zoom');
		if (empty($this->default_zoom)) $this->default_zoom = 6; // for backward compatibility

		parent::display($tpl);
	}
}
