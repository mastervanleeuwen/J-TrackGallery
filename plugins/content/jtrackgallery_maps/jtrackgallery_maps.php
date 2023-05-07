<?php
/**
 * @version		0.9
 * @package		J!TrackGallery plugin jtrackgallery_maps
 * @author    	Christophe Seguinot - http://jtrackgallery.net
 * @copyright
 * @license		GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 * This plugin in inspired from Simple Image Gallery (plugin)
 * developped by JoomlaWorks - http://www.joomlaworks.net
 */

// no direct access
defined ( '_JEXEC' ) or die ( 'Restricted access' );

jimport ( 'joomla.plugin.plugin' );
if (version_compare ( JVERSION, '1.6.0', 'ge' )) {
	jimport ( 'joomla.html.parameter' );
}
use Joomla\String\StringHelper;
class plgContentJtrackgallery_maps extends JPlugin {

	protected $map_count = 0;
	function onContentPrepare($context, &$row, &$params, $page = 0) {
		$this->renderJtrackGalleryMapsPlugin ( $row, $params, $page = 0 );
	}

	// The main function
	private function renderJtrackGalleryMapsPlugin(&$row, &$params, $page = 0) {
		// API
		jimport ( 'joomla.filesystem.file' );
		$mainframe = JFactory::getApplication ();
		$document = JFactory::getDocument ();

		// Assign paths
		$plg_name = "jtrackgallery_maps";
		$plg_tag = "JTRACKGALLERYMAP";
		$plg_copyrights_start = "\n\n<!-- J!TrackGallery \"jtrackgallery_maps\" Plugin (v0.9) starts here -->\n";
		$plg_copyrights_end = "\n<!-- J!TrackGallery \"jtrackgallery_maps\" Plugin (v0.9) ends here -->\n\n";

		$sitePath = JPATH_SITE;
		$siteUrl = JURI::root ( true );
		$pluginLivePath = $siteUrl . '/plugins/content/' . $plg_name;

		// Check if plugin is enabled
		if (JPluginHelper::isEnabled ( 'content', $plg_name ) == false)
			return;

		// Check
		if (!JComponentHelper::isEnabled('com_jtg', false)) {
			return JFactory::getApplication()->enqueueMessage(JText::_('PLG_JTG_MAPS_COM_JTG_NOT_INSTALLED'),'warning');
		}

		// Bail out if the page format is not what we want
		$allowedFormats = array (
				'',
				'html'
		);
		if (! in_array ( JFactory::getApplication()->input->getCmd ( 'format' ), $allowedFormats ))
			return;

			// Simple performance check to determine whether plugin should process further
		if (StringHelper::strpos ( $row->text, $plg_tag ) === false)
			return;

			// expression to search for
		$regex = "#{" . $plg_tag . "}(.*?){/" . $plg_tag . "}#is";

		// Find all instances of the plugin and put them in $matches
		preg_match_all ( $regex, $row->text, $matches );

		// Number of plugins
		$count = count ( $matches [0] );

		// Plugin only processes if there are any instances of the plugin in the text
		if (! $count)
			return;

			// Load the plugin language file
		JFactory::getLanguage()->load('plg_content_jtrackgallery_maps', JPATH_SITE . '/plugins/content/jtrackgallery_maps',	null, true);
		JFactory::getLanguage()->load('com_jtg', JPATH_SITE, null, true);
		JFactory::getLanguage()->load('com_jtg_common', JPATH_SITE, null, true);

		// Check for basic requirements
		$db = JFactory::getDBO ();

		// ----------------------------------- Get plugin parameters -----------------------------------

		// Get plugin info
		$plugin = JPluginHelper::getPlugin ( 'content', $plg_name );
		// Load params into and empty object
		$plgParams = new JRegistry();

		if ($plugin && isset($plugin->params)) {
			$plgParams->loadString($plugin->params);
		}
		// ----------------------------------- Prepare the output -----------------------------------

		// Process plugin tags
		if (preg_match_all ( $regex, $row->text, $matches, PREG_PATTERN_ORDER ) > 0) {

			// Start the replace loop
			foreach ( $matches [0] as $key => $match ) {

				$plg_call_params = array (
						"id" => 0,
						"gpxfilename" => ''
				);
				$tagcontent = preg_replace ( "/{.+?}/", "", $match );
				$tagparams = explode ( ',', strip_tags ( $tagcontent ) );

				foreach ( $tagparams as $tagparam ) {
					$temp = explode ( '=', $tagparam );
					$plg_call_params [trim ( $temp [0] )] = trim ( $temp [1] );
				}
				$plg_call_params ['id'] = ( int ) $plg_call_params ['id'];
				$warningtext = ' id=' . ($plg_call_params ['id'] ? $plg_call_params ['id'] : 'null') . ' gpxfilename=' . ($plg_call_params ['gpxfilename'] ? $plg_call_params ['gpxfilename'] : '') ;

				if ((! $plg_call_params ['id'] > 0) and (! $plg_call_params ['gpxfilename'])) {
					 JFactory::getApplication()->enqueueMessage(JText::_ ( 'PLG_JTG_MAPS_TRACK_NOT_SPECIFIED' ) . "()" );
				}
				// Test if given id or filename correspond to one track in database
				if ($plg_call_params ['gpxfilename']) {
					// Determine the id of the filename
					$query = "SELECT id FROM `#__jtg_files` WHERE file='" . $plg_call_params ['gpxfilename'] . "'";
					if ($plg_call_params ['id'] > 0) {
						$query .= " or id=" . $plg_call_params ['id'];
					}
					$db->setQuery ( $query );
					$db->execute ();
					$ids = $db->loadObjectList ();

					if (count ( $ids ) > 0 and ( int ) $ids [0]->id > 0)
					{
						$plg_call_params ['id'] = ( int ) $ids [0]->id;
					}
					else
					{
						JFactory::getApplication()->enqueueMessage( JText::_ ( 'PLG_JTG_MAPS_TRACK_NOT_FOUND' ) . " ($warningtext)" );
						$plg_call_params ['id'] = 0;
					}
				}

				$plg_html = $plg_copyrights_start;

				if ($plg_call_params ['id'] > 0)
				{
					// Generate the html code for the map
					$this->map_count += 1;
					if ($this->map_count == 1)
					{
						$plg_html .= '<div id="popup" class="ol-popup">'.
							' <a href="#" id="popup-closer" class="ol-popup-closer"></a>'.
							' <div id="popup-content"></div> </div>'."\n";
					}
					if ($this->map_count < 10)
					{
						$plg_html .= $this->rendermap($plgParams, $plg_call_params);
						$linktarget = '';
						if (isset($plg_call_params['show_link'])) {
							if ($plg_call_params['show_link']) $showlink = true; else $showlink = false;
							if ($plg_call_params['show_link'] == 2) $linktarget = ' target="_blank"';
						}
						else {
							$showlink = $plgParams['show_link'];
							if ($plgParams['link_newtab']) $linktarget = ' target="_blank"';
						}
						if ($showlink) {
						   $plg_html .= '<div class="jtg-gpx-link"><a href="'.JRoute::_('index.php?option=com_jtg&view=track&id='.$plg_call_params['id']).'"'.$linktarget.'>'.JText::_($plgParams['link_text']).'</a></div>';
						}
					}
					else
					{
						JFactory::getApplication()->enqueueMessage(JText::_ ( 'PLG_JTG_MAPS_CANT_RENDER_TRACKS' ));
						$plg_html .= JText::_ ( 'PLG_JTG_MAPS_CANT_RENDER_TRACKS' );
					}
                    
				} else {
					$plg_html .= JText::_ ( 'PLG_JTG_MAPS_TRACK_NOT_FOUND' ) . " ($warningtext)" ;
				}
				$plg_html .= $plg_copyrights_end;
				// Do the replace
				//$row->text = str_replace ( $match, $plg_html, $row->text );
				// Do the replace (in case of multiple matches: first occurrence only)
				$pos = strpos($row->text, $match);
				if ($pos !== false) {
    				$row->text = substr_replace($row->text, $plg_html, $pos, strlen($match));
				}
			}
		}
	}

	private function rendermap($plgParams, $plg_call_params)
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JUri::root(true) . '/media/com_jtg/js/openlayers/ol.css');
		$document->addStyleSheet(JUri::root(true) . '/media/com_jtg/js/openlayers/ol.css.map');

		// Add jtg_map stylesheet
		require_once JPATH_SITE . '/components/com_jtg/helpers/helper.php';
		require_once JPATH_SITE . '/components/com_jtg/helpers/maphelper.php';
		$cfg = JtgHelper::getConfig();
		$tmpl = strlen($cfg->template) ? $cfg->template : 'default';
		$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_style.css');
		$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');
		$map = "";

		// Load english language file for 'com_jtg' component then override with current language file
		JFactory::getLanguage()->load('com_jtg_common', JPATH_SITE . '/components/com_jtg',	null, true);

		// Com_jtg_additional language files are in /images/jtrackgallery/language folder
		JFactory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery',	null, true);

		$params = JComponentHelper::getParams('com_jtg');

		require_once JPATH_SITE . '/components/com_jtg/models/track.php';
		$model = JModelLegacy::getInstance( 'Track', 'JtgModel' );
		$track = $model->getFile($plg_call_params['id']);
		$trackImages = $model->getImages($plg_call_params['id']);
		$document = JFactory::getDocument();
		require_once JPATH_SITE . '/components/com_jtg/helpers/gpsClass.php';
		require_once JPATH_SITE . '/components/com_jtg/models/jtg.php';
		$document->addScript( JUri::root(true) . '/media/com_jtg/js/openlayers/ol.js');
		$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/jtg.js');
		$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/animatedCursor.js');
		$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/geolocation.js');
		$file = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . $track->file;
		$gpsData = new GpsDataClass($cfg->unit);
		$gpsData->loadFileAndData($file, $track->file);

		$plgParams_map_width = $plgParams->get('map_width', false);
		$plgParams_map_height = $plgParams->get('map_height', false);
		$map_width = $plgParams_map_width? $plgParams_map_width: $cfg->map_width;
		$map_height = $plgParams_map_height? $plgParams_map_height: $cfg->map_height;

		$map_width = isset ($plg_call_params ['map_width'])? $plg_call_params ['map_width']: $map_width;
		$map_height = isset ($plg_call_params ['map_height'])? $plg_call_params ['map_height']: $map_height;

		$layerSwitcher = $params->get('jtg_param_show_layerswitcher');
		if (isset($plg_call_params['layer_switcher']))
		{
			if ($plg_call_params['layer_switcher'] != 0 || $plg_call_params['layer_switcher']) {
				$layerSwitcher = true;
			}
			else {
				$layerSwitcher = false;
			}
		}
		if (isset($plg_call_params ['mapid'])) 
		{
			$mapid = $plg_call_params ['mapid'];
		}
		else
		{
			$mapid = $track->default_map; // TODO: check whether category has a default map
		}

		$trackColors = array();
		if (isset($plg_call_params['colors'])) {
			$trackColors = explode(';', $plg_call_params['colors'] );
		}

		if ($gpsData->displayErrors())
		{
			$map = "";
		}
		else
		{
			$map.= '<style type="text/css">

.olButton::before{
	display: none;
}
#jtg_map_'.$this->map_count.' img{
	max-width: none; /* joomla3 max-width=100% breaks popups*/
}
#jtg_map_'.$this->map_count.'.olMap {
	height: ' . $map_height . ';
	width: ' . $map_width . ';
	z-index: 0;
}
#jtg_map_'.$this->map_count.'.fullscreen {
	height: 800px;
	width: 100%;
	z-index: 10000;
}
/* Fix Bootstrap-Openlayers issue */
.olMap img { max-width: none !important; }

img.olTileImage {
	max-width: none !important;
}
</style>';

		$map .= "\n<center>\n<div id=\"jtg_map_{$this->map_count}\"  align=\"center\" class=\"olMap\" >";
		$map .= "\n</div>\n</center>\n";
		$map .= JtgMapHelper::parseTrackMapJS( $gpsData, $plg_call_params['id'], $mapid, $trackImages, false, true, $layerSwitcher, $trackColors, 'jtg_map_'.$this->map_count);
		$show_graph = false;
		if (isset($plg_call_params['show_graph'])) {
			if ($plg_call_params['show_graph'] != '0') $show_graph = true;
		}
		else if ($plgParams['show_graph']) $show_graph = true;
		if ($show_graph) 
		{
			$usepace = false;
			$graphid = "elevation_".$this->map_count;
			$graphJS = JtgMapHelper::parseGraphJS($gpsData, $cfg, $params, $usepace, true, $graphid);
			if (!empty($graphJS))
			{
				$map .= '<div id="profile" style="width: '.$map_width.';" >'."\n".
					'<div class="profile-img" id="'.$graphid.'" style="width: 100%; height: '.$cfg->charts_height.'"></div>'."\n".
					"</div>\n";
				$map .= $graphJS;
			}
		}
		$show_info = false;
		if (isset($plg_call_params['show_info'])) {
			if ($plg_call_params['show_info'] != '0') $show_info = true;
		}
		else if ($plgParams['show_info']) $show_info = true;
		if ($show_info) {
			$fieldlist = $plgParams['info_fields'];
			$map .= JtgHelper::parseTrackInfo($track, $gpsData, $params, $cfg, $fieldlist, $map_width);
		}
		}

		return $map;
	}

} // END CLASS
