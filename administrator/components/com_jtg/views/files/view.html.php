<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Backend
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

// Import Joomla! libraries
jimport('joomla.application.component.view');

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

/**
 * HTML View tracks class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgViewFiles extends JViewLegacy
{
	/**
	 * function_description
	 *
	 * @param   integer  $rowaccess  access level
	 * @param   bool     $needcolor  need color
	 *
	 * @return Status (string)
	 */
	public function buildRowGroupname($rowaccess, $needcolor = false)
	{
		switch ($rowaccess)
		{
			case 0:
				$access = Text::_('COM_JTG_PUBLIC');
				$color = "green";
				break;
			case 1:
				$access = Text::_('COM_JTG_REGISTERED');
				$color = "red";
				break;

			case 2:
				$access = Text::_('COM_JTG_ADMINISTRATORS');
				$color = "black";
				break;

			case 9:
				$access = Text::_('COM_JTG_PRIVATE');
				$color = "orange";
				break;
		}

		if ($needcolor === false)
		{
			return $access;
		}
		else
		{
			return "<font color='" . $color . "'>" . $access . "</font>";
		}
	}

	/**
	 * Gibt den Klicklink zurück mit dem man Dateien für das Menü auswählen kann
	 *
	 * @param   unknown_type  $id     param_description
	 * @param   unknown_type  $title  param_description
	 *
	 * @return string
	 */
	public function buildChooseKlicks($id, $title)
	{
		$onclick = "window.parent.jSelectArticle('" . $id . "', '" . $title . "', 'id');";

		return "<a style=\"cursor: pointer;\" href=\"javascript:void(0);\" onclick=\"" . $onclick . "\">" . $title . "</a>";
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $file   param_description
	 * @param   unknown_type  $count  param_description
	 *
	 * @return string
	 */
	public function buildEditKlicks($file, $count, $id)
	{
		if (JVERSION < 4.0)
		{
			return "<a href=\"javascript:void(0);\" onclick=\"javascript:return Joomla.listItemTask('cb" . $count
			. "','editfile')\">" . $file . "</a>";
		}
		else
		{
			return "<a href=\"".Route::_("index.php?option=com_jtg&task=editfile&controller=files&id=$id")."\">". $file."</a>";
		}
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $iconpath  param_description
	 * @param   unknown_type  $hidden    param_description
	 * @param   unknown_type  $count     param_description
	 *
	 * @return return_description
	 */
	public function buildHiddenImage($iconpath, $hidden, $count)
	{
		switch ($hidden)
		{
			case "0":
				// Visible
				$link = "tohide";
				$icon = $iconpath . "icon_visible.png";
				$tt = Text::_('COM_JTG_TOHIDE');
				$item = "<img alt=\"" . $tt . "\" title=\"" . $tt . "\" src=\"" . $icon . "\" />";
				break;

			case "1":
				// Hidden
				$link = "toshow";
				$icon = $iconpath . "icon_hidden.png";
				$tt = Text::_('COM_JTG_TOSHOW');
				$item = "<img alt=\"" . $tt . "\" title=\"" . $tt . "\" src=\"" . $icon . "\" />";
				break;

			default:
				// Not saved
				$tt = Text::_('COM_JTG_NOT_SAVED');
				$item = "<span title=\"" . $tt . "\">-- ? --</span>";

				return $item;
				break;
		}

		return "<a href=\"javascript:void(0);\" onclick=\"javascript:return Joomla.listItemTask('cb" . $count .
		"','" . $link . "')\">" . $item . "</a>";
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $track  param_description
	 * @param   unknown_type  $wp     param_description
	 * @param   unknown_type  $route  param_description
	 * @param   unknown_type  $cache  param_description
	 *
	 * @return string html image section
	 */
	public function buildImageFiletypes($track, $wp, $route, $cache)
	{
		$imagelink = "<table class=\"fileis\"><tr>";

		$foundtrackroute = 0;
		if ( ( isset($track) ) AND ( $track == "1" ) )
		{
			$imagelink .= "<td class=\"icon\"><span class=\"track1\" title=\"" . Text::_('COM_JTG_ISTRACK1') . "\"></span></td>";
       			$foundtrackroute = 1;
		}
		if ( ( isset($route) ) AND ( $route == "1" ) )
		{
			$imagelink .= "<td class=\"icon\"><span class=\"route1\" title=\"" . Text::_('COM_JTG_ISROUTE1') . "\"></span></td>";
			$foundtrackroute = 1;
		}
		if (!$foundtrackroute)
			$imagelink .= "<td class=\"icon\"><span class=\"track0\" title=\"" . Text::_('COM_JTG_ISTRACK0') . "\"></span></td>";

		if ( ( isset($wp) ) AND ( $wp == "1" ) )
		{
			$m = (string) 1;
		}
		else
		{
			$m = (string) 0;
		}

		$imagelink .= "<td class=\"icon\">";

		if ( isset($wp) )
		{
			$imagelink .= "<span class=\"wp" . $m . "\" title=\"" . Text::_('COM_JTG_ISWP' . $m) . "\"></span>";
		}
		else
		{
			$imagelink .= "<span class=\"wp" . $m . "\" title=\"" . Text::_('COM_JTG_DKWP') .
			"\" style=\"text-align:center\"><font size=\"+2\">?</font>";
		}

		$imagelink .= "</td>";

		if ( ( isset($cache) ) AND ( $cache == "1" ) )
		{
			$m = (string) 1;
		}
		else
		{
			$m = (string) 0;
		}

		$imagelink .= "<td class=\"icon\">";

		if ( isset($cache) )
		{
			$imagelink .= "<span class=\"cache" . $m . "\" title=\"" . Text::_('COM_JTG_ISCACHE' . $m) . "\">";
		}
		else
		{
			$imagelink .= "<span class=\"cache" . $m . "\" title=\"" . Text::_('COM_JTG_DKCACHE') . "\" style=\"text-align:center\"><font size=\"+2\">?</font>";
		}

		$imagelink .= "</span>";
		$imagelink .= "</td>";

		$imagelink .= "</tr></table>";

		return $imagelink;
	}

	/**
	 * function_description
	 *
	 * @param   string   $file   file URI
	 * @param   boolean  $exist  true if file exists
	 *
	 * @return true or Errorlevel (1 to 5)
	 */
	public function checkFilename($file, $exist=false)
	{
		if ($exist !== false )
		{
			return 1;
		}

		$filename = explode('/', $file);
		$filename = $filename[(count($filename) - 1)];

		if ( !is_writable($file) )
		{
			// Kein Schreibrecht
			return 2;
		}

		if ( strlen($filename) > 127 )
		{
			// Dateinamenslänge überschritten
			return 3;
		}

		if ( preg_match('/\&/', $filename) )
		{
			// When "&" in file name
			return 4;
		}

		if ( preg_match('/\#/', $filename) )
		{
			// When "#" in file name
			return 5;
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $file  param_description
	 *
	 * @return date
	 */
	public function giveDate($file)
	{
		if ((!is_file($file)) OR (!is_readable($file)))
		{
			return false;
		}

		$file = simplexml_load_file($file);
		$date = explode('T', $file->time);

		if (count($file->time) == 0)
		{
			return false;
		}

		if ( count($date) != 2 )
		{
			$date = explode('T', $file->trk->trkseg->trkpt->time);
		}

		if ( count($date) != 2 )
		{
			$date = explode('T', $file->metadata->time);
		}

		if ( strlen($date[0]) == 10 )
		{
			return $date[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $file  param_description
	 *
	 * @return date
	 */
	public function giveTitle($file)
	{
		// TODO remove this function in future version
		if ((!is_file($file)) OR (!is_readable($file)))
		{
			return "";
		}

		$file = simplexml_load_file($file);
		$desc = $file->metadata->desc;

		if ( ( $desc === null ) OR ( count($desc) == 0 ) )
		{
			$desc = $file->wpt->name;

			if ( $desc !== null )
			{
				return (string) $desc;
			}

			return $file->trk->name;
		}

		$desc = (string) $desc;

		return $desc;
	}

	/**
	 * function_description
	 *
	 * @param   integer  $catid  category ID
	 *
	 * @return return_description
	 */
	function giveParentCat($catid)
	{
		$catid = (int) $catid;

		if ($catid == 0)
		{
			return null;
		}

		$model = $this->getModel();
		$cats = $model->getCats();
		$cats = (object) $cats;
		$i = 0;

		foreach ($cats AS $cat)
		{
			if (isset($cat->id))
			{
				$id = (int) $cat->id;
			}

			if (isset($cat->title))
			{
				$title[$id] = $cat->title;
			}

			if ((isset($cat->id))AND( $catid == $id ))
			{
				$parentid = (int) $cat->parent_id;
				break;
			}

			$i++;
		}

		if ((isset($parentid) AND ($parentid != 0) AND isset($title[$parentid])))
		{
			return ($title[$parentid]);
		}

		return null;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $cats       param_description
	 * @param   integer       $catid      category ID
	 * @param   unknown_type  $separator  param_description
	 *
	 * @return return_description
	 */
	protected function parseCatTree($cats, $catid, $separator = "<br />")
	{
		$catid = (int) $catid;

		if ($catid == 0)
		{
			return null;
		}

		$newcat = array();
		$missingcat = array();

		foreach ($cats AS $cat)
		{
			$newcat[$cat->id] = $cat;

			if (isset($cat->title))
			{
				$newcat[$cat->id]->title = Text::_($cat->title);
			}
		}

		if ( !isset($newcat[$catid]) )
		{
			// Missing Category
			$missingcat[$catid] = $catid;
			$newcat[$catid] = new stdClass;
			$newcat[$catid]->id = 0;
			$newcat[$catid]->title = Text::sprintf('COM_JTG_ERROR_MISSING_CATID', $catid);
			$newcat[$catid]->parent_id = 0;
			$newcat[$catid]->treename = "<font class=\"errorEntry\">" . $newcat[$catid]->title . "</font>";
		}

		$return = array();
		$j = count($newcat);

		while (true)
		{
			$cat = $newcat[$catid];
			$catid = $cat->parent_id;
			array_unshift($return, Text::_($cat->treename));

			if ( ( $cat->parent_id == 0 ) OR ( $j <= 0 ) )
			{
				break;
			}

			$j--;
		}

		$return = implode($separator, $return);

		return array("tree" => $return, "missing" => $missingcat);

		// TODO unused code below!!
		if ((isset($parentid) AND ($parentid != 0) AND isset($title[$parentid])))
		{
			return (Text::_($title[$parentid]));
		}

		return null;
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$option = Factory::getApplication()->input->get('option');
		$this->canDo = ContentHelper::getActions('com_jtg');

		if ($this->getLayout() == 'form')
		{
			$this->_displayForm($tpl);

			return;
		}

		if ($this->getLayout() == 'upload')
		{
			$this->_displayUpload($tpl);

			return;
		}

		$model = $this->getModel();

		$order = Factory::getApplication()->input->get('order', 'order', 'string');

		$filter_order = $app->getUserStateFromRequest(
				$option . "filter_order",
				'filter_order',
				'ordering',
				'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest(
				$option . "filter_order_Dir",
				'filter_order_Dir',
				'',
				'word');
		$search = $app->getUserStateFromRequest(
				$option . "search",
				'search',
				'',
				'string');
		$search				= StringHelper::strtolower($search);

		$lists['order']		= $filter_order;
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['search']	= $search;
		$rows		= $this->get('Data');
		$total		= $this->get('Total');
		$pagination = $this->get('Pagination');
		$cfg = JtgHelper::getConfig();
		$cats = $model->getCats();

		$this->state = $this->get('State');
		$this->cats = $cats;
		$this->lists = $lists;
		$this->rows = $rows;
		$this->cfg = $cfg;
		$this->pagination = $pagination;

		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	function _displayUpload($tpl)
	{
		if (version_compare(JVERSION,'4.0','ge'))
		{
			HTMLHelper::_('jquery.framework');
			HTMLHelper::script(Juri::root() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
		}
		else if (version_compare(JVERSION,'3.0','ge'))
		{
			HTMLHelper::_('jquery.framework');
			HTMLHelper::script(Juri::root() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
			JHTML::_('behavior.framework');
		}
		else
		{
			HTMLHelper::script('jquery.js', 'components/com_jtg/assets/js/', false);
			HTMLHelper::script('jquery.MultiFile.js', 'components/com_jtg/assets/js/', false);
			HTMLHelper::script('mootools.js', '/media/system/js/', false);
			HTMLHelper::script('core-uncompressed.js', 'media/system/js/', false);
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
	protected function _displayForm($tpl)
	{
		if (JVERSION >= 4.0)
		{
			HTMLHelper::_('jquery.framework');
			HTMLHelper::script(Uri::root() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
		}
		else if (JVERSION >= 3.0)
		{
			HTMLHelper::_('jquery.framework');
			HTMLHelper::script(Uri::root() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
			JHTML::_('behavior.framework');
		}
		else
		{
			HTMLHelper::script('jquery.js', 'components/com_jtg/assets/js/', false);
			HTMLHelper::script('jquery.MultiFile.js', 'components/com_jtg/assets/js/', false);
			HTMLHelper::script('mootools.js', '/media/system/js/', false);
		}

		jimport('joomla.filesystem.folder');
		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		if (count($cid))
		{
			$id = $cid[0];
		}
		else
		{
			$id = Factory::getApplication()->input->getInt('id');
		}
		$cfg = JtgHelper::getConfig();
		$editor = Factory::getConfig()->get('editor');
		$editor = Editor::getInstance($editor);;
		$model = $this->getModel();
		$cats = $model->getCats(0, 'COM_JTG_SELECT', 0, 0);
		$terrain = $model->getTerrain("*", true, " WHERE published=1 ");
		$user 	= Factory::getUser();
		$uid = $user->get('id');

		if (!isset($id))
		{
			echo "deprecated";

			// New File
			$id = 0;
			$track = $model->getFile($id);
			$access = $model->getAccess($id);
			$size = min(count($cats), 6);
			$lists['cats']		= HTMLHelper::_('select.genericlist', $cats, 'catid[]', 'size="' . $size . '" multiple="multiple"', 'id', 'treename');
			$size = min(count($terrain), 6);
			$lists['terrain']	= HTMLHelper::_('select.genericlist', $terrain, 'terrain[]', 'multiple="multiple" size="' . $size . '"', 'id', 'title', 0);
			$row->access = $access;
			$lists['uid']		= HTMLHelper::_('list.users', 'uid', $uid, 1, null, 'name', 0);
			$lists['hidden']	= HTMLHelper::_('select.booleanlist', 'hidden', null, 0);
			$lists['published']	= HTMLHelper::_('select.booleanlist', 'published', null, 1);
			$lists['default_map'] 	= HTMLHelper::_('select.genericlist', $default_map, 'default_map', 'size="1"', 'id', 'name', null);

			$this->lists = $lists;
			$this->track = $track;
			$this->id = $id;
			$lists['level']	= $model->getLevelList(0);
		}
		else
		{
			// 		Edit File
			$track = $model->getFile($id);
			$lists['level']	= $model->getLevelList($track->level);
			$access = $model->getAccess($id);

			$default_map=$model->getDefaultMaps();
			array_unshift($default_map, array('id' => 'null', "name" => Text::_('JNONE')) );

			$error = false;
			$terrainlist = ($track->terrain? explode(',', $track->terrain): 0);
			/*
			 *
			 * What was this for ??
			* 		foreach ($terrainlist as $t) {
			* 			if ( !is_numeric($t) ) $error = true;
			* 		}
			*		if ( $error === true ) $error = "<font color=\"red\">" . Text::_('Error') . ": " . $track->terrain . "</font><br />";
			*/
			$size = min(count($cats), 6);
			$trackids = explode(",", $track->catid);
			$lists['cats']		= HTMLHelper::_('select.genericlist', $cats, 'catid[]', 'class="form-select" size="' . $size . '" multiple="multiple"', 'id', 'treename', $trackids, '', true);
			$size = min(count($terrain), 6);
			$lists['terrain']	= $error . HTMLHelper::_('select.genericlist', $terrain, 'terrain[]', 'class="form-select" multiple="multiple" size="' . $size . '"', 'id', 'title', $terrainlist);
			$lists['default_map'] 	= HTMLHelper::_('select.genericlist', $default_map, 'default_map', 'class="form-select" size="1"', 'id', 'name', $track->default_map);

			// 		$row->access = $access;
			$lists['access']	= JtgHelper::getAccessList($access);

			// 		$lists['access']	= HTMLHelper::_('list.accesslevel', $row );
			$lists['hidden'] = HTMLHelper::_('select.booleanlist', 'hidden', null, $track->hidden);
			$lists['uid'] = HTMLHelper::_('list.users', 'uid', $track->uid, 1, 'class="form-select"', 'name', 0);

			$imagelist = $model->getImages($id);

			$img_path = Uri::root() . 'images/jtrackgallery/uploaded_tracks_images/track_' . $id . '/';
			
			$images = "<div class=\"jtg-photo-grid\">";
			foreach ($imagelist as $image) 
			{
				$thumb_name = 'thumb1_' . $image->filename;
				$images .= "<div class=\"jtg-photo-item\"\><input type=\"checkbox\" name=\"deleteimage_" . $image->id. "\" value=\"" . $image->filename . "\">" . Text::_('COM_JTG_DELETE_IMAGE') . " (" . $image->filename . ")<br />\n".
				"<img src=\"" . $img_path . 'thumbs/' . $thumb_name . "\" alt=\"" . $image->filename . "\" title=\"" . $image->filename . " (thumbnail)\" /><br />\n".
				"<input type=\"text\" class=\"inputbox jtg-photo-input\" name=\"img_title_".$image->id. "\" value=\"".$image->title."\" placeholder=\"Title\"> <br /></div>\n";
			}
         $images .= "</div>";

			$lists['published'] = HTMLHelper::_('select.booleanlist', 'published', null, $track->published);
			$lists['values'] = JtgHelper::giveGeneratedValues('backend', $this->buildImageFiletypes($track->istrack, $track->iswp, $track->isroute, $track->iscache), $track);
			$lists['level']	= $model->getLevelList($track->level);
			if (version_compare(JVERSION,'4.0','ge')) {
				$this->tagids = $model->getTable('jtg_files')->getTagsHelper()->getTagIds($id, 'com_jtg.file');
			}
			else {
				$tagsHelper = new JHelperTags;
				$this->tagids = $tagsHelper->getTagIds($id, 'com_jtg.file');
			}
			$trackForm = $this->getModel()->getForm();
			$tagField = $trackForm->getField('tags');
			$tagField->setValue($this->tagids);
			$lists['tags'] = $tagField->renderField(array('hiddenLabel'=> true));
			$this->lists = $lists;
			$this->track = $track;
			$this->id = $id;
			$this->images = $images;
		}

		$this->editor = $editor;
		parent::display($tpl);
	}
}
