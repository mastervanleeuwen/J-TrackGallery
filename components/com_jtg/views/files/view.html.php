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

		if ($this->getLayout() == 'form')
		{
			$this->_displayForm($tpl);

			return;
		}

		if ($this->getLayout() == 'file')
		{
			$this->_displayFile($tpl);

			return;
		}

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
	protected function _displayForm($tpl = null)
	{
		$mainframe = JFactory::getApplication();
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

		JHtml::_('behavior.modal');
		JHtml::_('behavior.tooltip');
		$yesnolist = array(
				array('id' => 0, 'title' => JText::_('JNO')),
				array('id' => 1, 'title' => JText::_('JYES'))
		);

		$cfg = JtgHelper::getConfig();

		// Make sure user is logged in and have the necessary access rights

		if (! JtgHelper::userHasFrontendRights() )
		{
			JResponse::setHeader('HTTP/1.0 403', true);
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_ALERT_NOT_AUTHORISED'), 'Error');

			return;
		}

		$editor = JFactory::getEditor();
		$pathway = $mainframe->getPathway();
		$lh = LayoutHelper::navigation();
		$footer = LayoutHelper::footer();
		$model = $this->getModel();

		$uri = "index.php?option=com_jtg&view=files&layout=form";
		$uri = JRoute::_($uri, false);

		// Add pathway item
		$pathway->addItem(JText::_('New'), '');
		$sitename = $mainframe->getCfg('sitename');
		$row = $model->getCats();
		$terrain = $model->getTerrain(" WHERE published=1 ");
		$terms = JRoute::_('index.php?option=com_content&view=article&tmpl=component&id=' . $cfg->terms_id, false);
		$id = (JFactory::getApplication()->input->getInt('id', null));
		$catid = "";
		$document = JFactory::getDocument();

		$default_map = $model->getDefaultMaps();
		array_unshift($default_map, array('id' => 0, "name" => JText::_('JNONE')) );
		$default_overlays = $model->getDefaultOverlays();
		array_unshift($default_overlays, array('id' => 0, "name" => JText::_('JNONE')) );

		if (isset ($id))
		{
			// View/Update part

      	// Load Openlayers stylesheet first (for overriding)
	      $document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/default/ol.css');

   	   // Then load jtg_map stylesheet
      	$tmpl = ($cfg->template = "") ? $cfg->template : 'default';
      	$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');

      	// Then override style with user templates
      	$template = $mainframe->getTemplate();
      	$template_jtg_map_style = 'templates/' . $template . '/css/jtg_map_style.css';

      	if ( JFile::exists($template_jtg_map_style) )
      	{
        		$document->addStyleSheet(JUri::root(true).$template_jtg_map_style);
      	}

	      $document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/ol.js');  // Load OpenLayers
   	   $document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/jtg.js');

			$track = $this->getModel()->getFile( $id );
			$this->track = $track;
			$this->id = $id;
			$gpsData = new GpsDataClass($cfg->unit);
			$file = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . strtolower($track->file);
			$gpsData->loadFileAndData( $file, $track->file );

			if (! $gpsData->displayErrors())
      	{
				$params = JComponentHelper::getParams('com_jtg');
				$makepreview = false;
				if (!JFile::exists(JPATH_SITE . '/images/jtrackgallery/maps/track_'.$id.'.png')) $makepreview = true;
				$this->map = $gpsData->writeTrackOL( $track, $params, null, $makepreview);
      	}

			$catid = $track->catid;
			$catid = explode(",", $catid);
			$pathway->addItem(JText::_('COM_JTG_UPDATE_GPS_FILE'), '');
			$document = JFactory::getDocument();
			$document->setTitle(JText::_('COM_JTG_UPDATE_GPS_FILE') . " (" . $track->title . ") - " . $sitename);
			$selterrain = explode(',', $track->terrain);
			$value_published = $track->published;
			$value_hidden = $track->hidden;
			$sellevel = $track->level;
			$value_default_map = $track->default_map;
			$value_default_overlays= unserialize($track->default_overlays);
		}
		else
		{
			// New file
			$document->setTitle(JText::_('COM_JTG_NEW_TRACK') . " - " . $sitename);
			$track = array("access" => "0");
			$track = JArrayHelper::toObject($track);
			$catid = null;
			$selterrain = null;
			$value_published = 1;
			$value_hidden = 0;
			$sellevel = 3;
			$value_default_map = null;
			$value_default_overlays= null;
		}

		$level = $model->getLevelSelect($sellevel);
		$img_path = JUri::root() . 'images/jtrackgallery/uploaded_tracks_images/track_' . $id . "/";
		$images = null;
		$imgcount = 0;

      if (isset($id)) {
			$imageList = $model->getImages($id);
			$images = "<div class=\"jtg-photo-grid\">";
			foreach ($imageList as $image) {
				$thumb_name = 'thumb1_' . $image->filename;
				$images .= "<div class=\"jtg-photo-item\"><input type=\"checkbox\" name=\"deleteimage_" . $image->id . "\" value=\""
						. $image->filename . "\">" . JText::_('COM_JTG_DELETE_IMAGE') . " (" . $image->filename . ")<br />" .
						"<img src=\"" . $img_path . 'thumbs/' . $thumb_name . "\" alt=\"" . $image->filename . "\" title=\"" . $image->filename . " (thumbnail)\" /><br />".
						"<input type=\"text\" class=\"jtg-photo-title\" name=\"img_title_".$image->id . "\" value = \"".$image->title."\" placeholder=\"Title\" maxlength=\"256\"> <br /></div>\n";
				$imgcount++;
			}
         $images .= "</div>";
		}

		$size = min(count($row), 6);
		$lists['content'] = JHtml::_('select.genericlist', $row, 'catid[]', 'multiple="multiple" size="' . $size . '"', 'id', 'title', $catid);
		$size = min(count($terrain), 6);
		$lists['terrain'] = JHtml::_('select.genericlist', $terrain, 'terrain[]', 'multiple="multiple" size="' . $size . '"', 'id', 'title', $selterrain);
		$lists['access'] = JtgHelper::getAccessList($track->access);
		$lists['hidden']	= JHtml::_('select.genericlist', $yesnolist, 'hidden', 'class="inputbox" size="1"', 'id', 'title', $value_hidden);
		$lists['published']	= JHtml::_('select.genericlist', $yesnolist, 'published', 'class="inputbox" size="1"', 'id', 'title', $value_published);
		$lists['default_map'] 	= JHtml::_('select.genericlist', $default_map, 'default_map', 'size="1"', 'id', 'name', $value_default_map);
		$size=min(4,count($default_overlays));
		$lists['default_overlays'] 	= JHtml::_('select.genericlist', $default_overlays, 'default_overlays[]', 'class="inputbox" multiple="true" size="' . $size . '"', 'id', 'name', $value_default_overlays);

		$this->imgcount = $imgcount;
		$this->images = $images;

		$this->lh = $lh;
		$this->footer = $footer;
		$this->track = $track;
		$this->editor = $editor;
		$this->lists = $lists;
		$this->action = $uri;
		$this->cfg = $cfg;
		$this->terms = $terms;
		$this->level = $level;
		/*
		 * $this->comments = comments;
		 */

		parent::display($tpl);
	}

	/**
	 * Display function
	 *
	 * @param   array  $tpl  template
	 *
	 * @return return_description
	 */
	function _displayFile($tpl)
	{
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$mapsxml = JPATH_COMPONENT_ADMINISTRATOR . '/views/maps/maps.xml';
		$params_maps = new JRegistry('com_jtg', $mapsxml);
		$this->params = $params_maps;
		$params = JComponentHelper::getParams('com_jtg');
		$sitename = $mainframe->getCfg('sitename');
		$document = JFactory::getDocument();
		$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/ol.js');  // Load OpenLayers
		// Code support for joomla version greater than 3.0
		if (JVERSION >= 3.0)
		{
			// Load JQuery before combobox
			JHtml::_('jquery.framework');
		}
		else
		{
			// Nothing
		}

		JHtml::_('behavior.combobox');
		$cache = JFactory::getCache('com_jtg');

		// TODO when cache is used, Update a track, then browse it: jtg_osmGettile.js is not loaded!! Why?
		// $cache->setCaching( 1 ); // activate caching

		if ( $params->get("jtg_param_lh") == 1 )
		{
			$lh = LayoutHelper::navigation();
		}
		else
		{
			$lh = null;
		}

		$footer = LayoutHelper::footer();
		$cfg = JtgHelper::getConfig();

		$model = $this->getModel();
		$pathway = $mainframe->getPathway();
		$id = JFactory::getApplication()->input->getInt('id');
		/**
		 // $track = $model->getTrack($id);
		 // $track = $cache->get(array($model, 'getTrack'), array($id));
		 // If (!$id) die ("Schau mal in datei view.html.php Zeile 152 :-P");
		 // If (!$id) $id = 1;
		 // 	$file = JPATH_SITE . "/components/com_jtg/models/jtg.php";
		 // 	require_once $file;
		 */
		$sortedcats = JtgModeljtg::getCatsData(true);
		$track = $this->getModel()->getFile( $id );

		if ( ( !$track ) OR ( $track->id === null ) )
		{
			// $this->model = $model;
			$this->lh = $lh;
			$this->footer = $footer;
			parent::display($tpl);

			return false;
		}

		/**
		 // $vote = $model->getVotes($id);
		 // 		$vote = $cache->get(array (
		 // 		$model,
		 // 		'getVotes'
		 // 		), array (
		 // 		$id
		 // 		));
		 */
		$vote = $model->getVotes($id);
		$pathway->addItem($track->title, '');
		$document->setTitle($track->title . " - " . $sitename);
		$date = JHtml::_('date', $track->date, JText::_('COM_JTG_DATE_FORMAT_LC4'));
		$profile = JtgHelper::getProfileLink($track->uid, $track->user);
		$comments = $model->getComments($id, $cfg->ordering);

		/**
		 // 	$comments = $cache->get(array (
		 // 		$model,
		 // 		'getComments'
		 // 	), array (
		 // 		$id,
		 // 		$cfg->ordering
		 // 	));
		 */
		$user = JFactory::getUser();
		$document = JFactory::getDocument();

		// Load Openlayers stylesheet first (for overriding)
		$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/default/ol.css');

		// Then load jtg_map stylesheet
		$tmpl = ($cfg->template = "") ? $cfg->template : 'default';
		$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');

		// Then override style with user templates
		$template = $mainframe->getTemplate();
		$template_jtg_map_style = 'templates/' . $template . '/css/jtg_map_style.css';

		if ( JFile::exists($template_jtg_map_style) )
		{
        	$document->addStyleSheet(JUri::root(true).$template_jtg_map_style);
		}

		// Kartenauswahl BEGIN
		$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/jtg.js');

		$action = JUri::root()."/index.php?option=com_jtg&amp;controller=download&amp;task=download";
		$file = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . strtolower($track->file);
		$gpsData = new GpsDataClass($cfg->unit);

		// Cache: $gpsData structure is cached, after LoadFileAndData
		$gpsData = $cache->call(array ( $gpsData, 'loadFileAndData' ), $file, $track->file );

		if ($gpsData->displayErrors())
		{
			$map = "";
			$coords = "";
			$distance_float = 0;
			$distance = 0;
		}
		else
		{
		   $imageList = $model->getImages($id);
			// Kartenauswahl BEGIN
			$map = $cache->call(array ( $gpsData, 'writeTrackOL' ), $track, $params, $imageList );

			// Kartenauswahl END
			$distance_float = (float) $track->distance;
			$distance = JtgHelper::getLocatedFloat($distance_float, 0, $cfg->unit);

			// Charts
			$coords = $gpsData->allCoords;
		}

		/**
		 // Klicklinks for every track in one file (at the moment not active)
		 // function giveClickLinks is not performant!
		 // 		$clicklist = $cache->get(array (
		 // 		$gps,
		 // 		'giveClickLinks'
		 // 		), array (
		 // 		$file
		 // 		));
		 // 		if ((count($clicklist)) < 2) {
		 // 			$clicklist = false;
		 // 		}
		 */
		// Load images if exists
		$img_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id;

		$jscript = "<script type=\"text/javascript\">
		Joomla.submitbutton = function(pressbutton)  {
		var form = document.adminForm;
		submitform( pressbutton);}
	</script>\n";

		$imageBlock = null;

		$imageList = $model->getImages($id);

		if ($imageList !== null AND count($imageList) > 0) {
         $this->images = true;
			switch ($cfg->gallery)
			{
				case 'jd2' :
					$galscript = "<script type=\"text/javascript\">
					startGallery = function()  {
					var myGallery = new gallery($('myGallery'), {
					timed: true,
					showArrows: true,
					embedLinks: false,
					showCarousel: false
			});
			}
			window.addEventListener('domready',startGallery);
			</script>\n";
			JHTML::_('behavior.framework', true); // Load mootools
			$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/jd.gallery.js');
					$imageBlock .= "<div id=\"myGallery\">";

					foreach ($imageList as $image)
					{
						$imageBlock .= "	<div class=\"imageElement\"> <h3>" . $track->title . " <small>(" . $image->filename . ")</small></h3>
						<p></p>
						<img src=\"" . JUri::base() . "images/jtrackgallery/uploaded_tracks_images/track_" . $id . "/" . $image->filename . "\" class=\"full\" height=\"0px\" />
						</div>\n";
					}

					$imageBlock .= "</div>\n";
					break;

				case 'highslide' :

					$galscript = "<script type=\"text/javascript\">
					hs.graphicsDir = '" . JUri::base() . "components/com_jtg/assets/highslide/graphics/';
					hs.align = 'center';
					hs.transitions = ['expand', 'crossfade'];
					hs.fadeInOut = true;
					hs.outlineType = 'rounded-white';
					hs.headingEval = 'this.a.title';
					hs.numberPosition = 'heading';
					hs.useBox = true;
					hs.width = 600;
					hs.height = 400;
					hs.showCredits = false;
					hs.dimmingOpacity = 0.8;

					// Add the slideshow providing the controlbar and the thumbstrip
					hs.addSlideshow({
					//slideshowGroup: 'group1',
					interval: 5000,
					repeat: false,
					useControls: true,
					fixedControls: 'fit',
					overlayOptions: {
					position: 'top right',
					offsetX: 200,
					offsetY: -65
			},
			thumbstrip: {
			position: 'rightpanel',
			mode: 'float',
			relativeTo: 'expander',
			width: '210px'
			}
			});
			// Make all images animate to the one visible thumbnail
			var miniGalleryOptions1 = {
			thumbnailId: 'thumb1'
			}
			</script>\n";
					$document->addScript( JUri::root(true) . '/components/com_jtg/assets/highslide/highslide-with-gallery.packed.js');
					$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/highslide/highslide.css');

					// TODO This style sheet is not overrided.
					$imageBlock .= "\n<div class=\"highslide-gallery\" style=\"width: auto; margin: auto\">\n";
					$imageBlock .= "\n<div class=\"jtg-photo-grid\">\n";
					$imgcount = count($imageList);

					foreach ($imageList as $image)
					{
						if ($imgcount < 5)
						{
							$thumb = 'thumbs/thumb1_' . $image->filename;
						}
						else
						{
							$thumb = 'thumbs/thumb2_' . $image->filename;
						}

						if ( ! JFile::exists(JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id . '/' . $thumb) )
						{
							$thumb = $image->filename;
						}
						$title = $image->title;
						if (strlen($title)==0) $title = $image->filename;
						$imageBlock .= "	<div class=\"jtg-photo-item\"> <a class=\"highslide\" href='" . JUri::base() . "images/jtrackgallery/uploaded_tracks_images/track_" . $id . "/" . $image->filename . "' title=\"" . $title . "\" onclick=\"return hs.expand(this)\">
							<img src=\"" . JUri::base() . "images/jtrackgallery/uploaded_tracks_images/track_" . $id . '/' . $thumb . "\" alt=\"$image->filename\"  /></a>
                     <div class=\"jtg-caption\">$image->title</div> <br>
                     </div>\n";
					}

					$imageBlock .= "</div></div>\n";
					break;

				case 'straight' :
					$galscript = "";

               $imageBlock .= "<div class=\"jtg-photo-grid\">\n";
					foreach ($imageList as $image)
					{
						$imageBlock .= "<div class=\"jtg-photo-item\"> <img src=\"" . JUri::base() . "images/jtrackgallery/uploaded_tracks_images/track_" . $id . "/"
							. $image->filename . "\" alt=\"" . $image->filename . " (" . $image->filename . ")" . "\" title=\"" . $track->title
							. "\" />\n <br>$image->title</div>";
					}
               $imageBlock .= "</div>\n";
					break;

				case 'ext_plugin':
					$gallery_folder = "jtrackgallery/uploaded_tracks_images/track_" . $id;
					$external_gallery = str_replace('%folder%', $gallery_folder, $cfg->gallery_code);
					$imageBlock = JHTML::_('content.prepare', $external_gallery);
					$galscript = "";
					break;

				default:
					$galscript = "";
			}
		}
		else
		{
			$this->images = null; //false;
			$galscript = "";
		}

		$stars = array(
				1 => "one",
				2 => "two",
				3 => "three",
				4 => "four",
				5 => "five",
				6 => "six",
				7 => "seven",
				8 => "eight",
				9 => "nine",
				10 => "ten"
				);
		$stars = JArrayHelper::toObject($stars);

		$level = $model->getLevel($track->level);
		$this->lh = $lh;
		$this->params = $params;
		$this->sortedcats = $sortedcats;
		$this->stars = $stars;
		$this->jscript = $jscript;
		$this->galscript = $galscript;
		$this->footer = $footer;
		$this->map = $map;
		$this->cfg = $cfg;
		$this->track = $track;
		$this->vote = $vote;
		$this->distance = $distance;
		$this->distance_float = $distance_float;
		$this->action = $action;
		$this->date = $date;
		$this->profile = $profile;
		$this->beatdata = $gpsData->beatData;
		$this->heighdata = $gpsData->elevationData;
		$this->speeddata = $gpsData->speedData;
		$this->pacedata = $gpsData->paceData;
		$this->speedDataExists = $gpsData->speedDataExists;
		$this->elevationDataExists = $gpsData->elevationDataExists;
		$this->beatDataExists = $gpsData->beatDataExists;
		$this->longitudeData = $gpsData->longitudeData;
		$this->latitudeData = $gpsData->latitudeData;
		$this->comments = $comments;
		$this->user = $user;
		$this->model = $model;
		$this->level = $level;
		$this->imageBlock = $imageBlock;
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
		$tmpl = ($this->cfg->template = "") ? $this->cfg->template : 'default';
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
