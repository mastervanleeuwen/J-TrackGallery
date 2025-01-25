<?php
/**
 * component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 * @package     Comjtg
 * @subpackage  Backend
 *
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @author      Christian Knorr, InJooOSM  <christianknorr@users.sourceforge.net>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        http://jtrackgallery.net/
 *
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;

// Import Joomla! libraries
jimport('joomla.application.component.model');
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Model Class Files
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgModelFiles extends AdminModel
{
	/**
	 * Category data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * function_description
	 *
	 * @return string The message to be displayed to the use
	 *
	 * @since   0.0.1
	 * */
	function updateGeneratedValues()
	{
		// Get the post data
		$id = JFactory::getApplication()->input->get('id');
		$file = JFactory::getApplication()->input->get('file');
		$cfg = JtgHelper::getConfig();
		jimport('joomla.filesystem.file');
		require_once '../components/com_jtg/helpers/gpsClass.php';
		$file = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . $file;
		$gpsData = new GpsDataClass($file, $file);

		if ($gpsData->displayErrors())
		{
			return false;
		}

		$isTrack = $gpsData->isTrack;
		$isWaypoint = $gpsData->isWaypoint;
		$isRoute = $gpsData->isRoute;

		if ( $isWaypoint == 1 )
		{
			$isCache = $gpsData->isCache;
		}
		else
		{
			$isCache = 0;
		}

		if ( $gpsData->isTrack == 1 )
		{
			$distance = $gpsData->distance;
			$ele[0] = $gpsData->totalAscent;
			$ele[1] = $gpsData->totalDescent;
		}
		else
		{
			$distance = 0;
			$ele = array(null,null);
		}

		$params = JComponentHelper::getParams('com_jtg');
		$iconCoords = $gpsData->getIconCoords($params['jtg_param_icon_loc']);
		if ($iconCoords === false) return false;
		if ($gpsData->start === false) return false;

		$db = $this->getDbo();

		// Count votings
		$query = "SELECT COUNT(*) FROM #__jtg_votes"
		. "\n WHERE trackid='" . $id . "'";

		$db->setQuery($query);
		$count = (int) $db->loadResult();

		// Sum rating
		$query = "SELECT SUM(rating) FROM #__jtg_votes"
		. "\n WHERE trackid='" . $id . "'";
		$db->setQuery($query);
		$givenvotes = (int) $db->loadResult();

		if ( $count == 0 )
		{
			$vote = 0;
		}
		else
		{
			$vote = (float) (round(($givenvotes / $count), 3));
		}

		$query = "UPDATE #__jtg_files SET"
		. "\n istrack='" . (int) $gpsData->isTrack . "',"
		. "\n iswp='" . (int) $gpsData->isWaypoint . "',"
		. "\n isroute='" . (int) $gpsData->isRoute . "',"
		. "\n iscache='" . (int) $gpsData->isCache . "',"
		. "\n start_n='" . $gpsData->start[1] . "',"
		. "\n start_e='" . $gpsData->start[0] . "',"
		. "\n icon_n='" . $iconCoords[1] . "',"
		. "\n icon_e='" . $iconCoords[0] . "',"
		. "\n distance='" . $gpsData->distance . "',"
		. "\n ele_asc='" . $gpsData->totalAscent . "',"
		. "\n ele_desc='" . $gpsData->totalDescent . "',"
		. "\n vote='" . $vote . "'"
		. "\n WHERE id=" . $id;

		$db = $this->getDbo();
		$db->setQuery($query);
		if (! $db->execute())
		{
			echo $db->stderr();
			return 'database not saved';
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $files  param_description
	 * @param   unknown_type  $dest   param_description
	 * @param   unknown_type  $types  param_description
	 *
	 * @return string
	 */
	function uploadfiles($files, $dest, $types = true)
	{
		jimport('joomla.filesystem.file');

		/* TODO test upload individually, and load all valid files
		 * remove the return statement inside for loop!
		*/

		if (count($files) > 0)
		{
			foreach ($files as $file)
			{
				if ($file['name'] != "")
				{
					$filename = JFile::makeSafe($file['name']);
					$ext = JFile::getExt($filename);

					if ( ( $types === true ) OR (in_array(strtolower($ext), $types)))
					{
						if ( JtgHelper::uploadfile($file, $dest) === false)
						{
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * function_description
	 *
	 * @global string $option
	 */
	public function __construct()
	{
		parent::__construct();
		$mainframe = JFactory::getApplication();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart	= $mainframe->getUserStateFromRequest($this->option . '.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		// 	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$limitstart = JFactory::getApplication()->input->get('limitstart', 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$array = JFactory::getApplication()->input->get('cid', array(0), 'array');
		$edit	= JFactory::getApplication()->input->get('edit', true);

		if ($edit)
		{
			$this->setId((int) $array[0]);
		}
	}

	/**
	 * function_description
	 *
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_data;
	}

	/**
	 * function_description
	 *
	 * @return array $pagination
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	function _fetchJPTfiles()
	{
		$db = $this->getDbo();
		$query = "SELECT * FROM #__gps_tracks";

		try {
			if ($db->setQuery($query))
			{
				return false;
			}

			$rows = $db->loadAssocList();
		}
		catch (Exception $e) {
			$rows = false;
		}
		return $rows;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	protected function _buildQuery()
	{
		$mainframe = JFactory::getApplication();

		$orderby = $this->_buildContentOrderBy();
		$where = $this->_buildContentWhere();

		$query = "SELECT a.*, b.title AS cat FROM"
		. "\n #__jtg_files AS a"
		. "\n LEFT JOIN #__jtg_cats AS b"
		. "\n ON a.catid=b.id"
		. $where
		. $orderby;

		return $query;
	}

	/**
	 * function_description
	 *
	 * @global string $option
	 * @return string
	 */
	protected function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();

		$filter_order = $mainframe->getUserStateFromRequest(
				$this->option . 'filter_order', 'filter_order', 'ordering', 'cmd');
		$filter_order_Dir = $mainframe->getUserStateFromRequest(
				$this->option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

		if ($filter_order == 'ordering')
		{
			$orderby = ' ORDER BY ordering ' . $filter_order_Dir;
		}
		else
		{
			$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , id ';
		}

		return $orderby;
	}

	/**
	 * function_description
	 *
	 * @global string $option
	 * @return string
	 */
	protected function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();

		$search = $mainframe->input->get('search');
		$where = array();
		$db = $this->getDbo();

		if ($search)
		{
			$where[] = 'LOWER(a.title) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
			$where[] = 'LOWER(b.title) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
			$where[] = 'LOWER(a.date) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
		}

		$where = ( count($where) ? ' WHERE ' . implode(' OR ', $where) : '');

		return $where;
	}

	/**
	 * get information about a single track from database
	 *
	 * @param integer  $id Track id
	 *
	 * @return object
	 */
	function getFile($id)
	{
		$db = $this->getDbo();
		$query = "SELECT * FROM #__jtg_files"
		. "\n WHERE id=" . $id;
		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result)
		{
			return JTable::getInstance('jtg_files', 'table');
		}

		return $result;
	}

	/**
	 * get list of images for a track from database
	 *
	 * @param integer  $id Track id
	 *
	 * @return object
	 */
	function getImages($id)
	{
		$db = $this->getDbo();
		$query = "SELECT * FROM #__jtg_photos"
                . "\n WHERE trackID=" . $id . "";
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @param   string  $id  param_description
	 *
	 * @return return_description
	 */
	function setId($id)
	{
		// Set weblink id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * publish or unpublish some tracks
	 *
	 * @param   array    $cid      array of track IDs
	 * @param   boolean  $publish  1 to publish, 0 to unpublish
	 *
	 * @return boolean true on success
	 */
	function publish(&$cid, $publish = 1)
	{
		$user 	= JFactory::getUser();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__jtg_files'
			. ' SET published = ' . (int) $publish
			. ' WHERE id IN ( ' . $cids . ' )'
			. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   array   $cid   param_description
	 * @param   string  $hide  param_description
	 *
	 * @return bool
	 */
	function showhide($cid = array(), $hide = 0)
	{
		$user 	= JFactory::getUser();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__jtg_files'
			. ' SET hidden = ' . (int) $hide
			. ' WHERE id IN ( ' . $cids . ' )'
			. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * set some track(s) access level
	 *
	 * @param   array   $cid     array of track IDs
	 * @param   string  $access  track access level
	 *
	 * @return bool true on success
	 */
	function access($cid = array(), $access = 1)
	{
		$user 	= JFactory::getUser();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__jtg_files'
			. ' SET access = ' . (int) $access
			. ' WHERE id IN ( ' . $cids . ' )'
			. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * delete track(s) whose ID(s) belong to $cid
	 *
	 * @param   array  $cid  param_description
	 *
	 * @return boolean true on success
	 */
	function delete(&$cid)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$result = false;

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			$query = 'SELECT * FROM #__jtg_files WHERE id IN ( ' . $cids . ' )';
			$this->_db->setQuery($query);
			$rows = $this->_db->loadObjectList();

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}

			foreach ($rows as $row)
			{
				// Folder and Pictures within delete
				$folder = JPATH_SITE . "/images/jtrackgallery/" . $row->id;

				if (JFolder::exists($folder))
				{
					JFolder::delete($folder);
				}
				$img_path = JPATH_SITE . 'images/jtrackgallery/uploaded_tracks_images/track_' . $row->id;
				if (JFolder::exists($img_path))
				{
					JFolder::delete($img_path);
				}
				// File (gpx?) delete
				$filename = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . $row->file;

				if (JFile::exists($filename))
				{
					JFile::delete($filename);
				}
				// Delete from DB
				$this->getTable()->delete($row->id);
			}

			$query = 'DELETE FROM #__jtg_photos WHERE trackID IN ( ' . $cids . ' )';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   array  $found  param_description
	 *
	 * @return boolean
	 */
	function deleteFromImport($found)
	{
		$cid = JFactory::getApplication()->input->get('import_0');
		jimport('joomla.filesystem.file');
		$result = false;

		for ($i = 0; $i <= $found; $i++)
		{
			$file = JFactory::getApplication()->input->get('import_' . $i);

			if ( $file !== null )
			{
				if (JFile::exists($file))
				{
					if (!JFile::delete($file))
					{
						JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_ERROR_FILE_NOT_ERASEABLE') . "(" . $file . ")", 'Error');

						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $selected  param_description
	 *
	 * @return array
	 */
	function getLevelList($selected = 0)
	{
		$return = "\n";
		$cfg = JtgHelper::getConfig();
		$levels = explode("\n", $cfg->level);
		array_unshift($levels, 'dummy');
		$i = 0;

		foreach ($levels AS $level)
		{
			if ( trim($level) != "" )
			{
				if ( $i == 0 )
				{
					$levels[0] = JText::_('COM_JTG_SELECT');
				}
				else
				{
					$levels[$i] = $i . " - " . JText::_(trim($level));
				}

				$i++;
			}
		}

		return JHtml::_('select.genericlist', $levels, 'level', 'class="form-select"', 'id', 'title', $selected);
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $nosubcats  param_description
	 * @param   unknown_type  $stdtext    param_description
	 * @param   unknown_type  $stdid      param_description
	 * @param   unknown_type  $type       param_description
	 *
	 * @return array
	 */
	function getCats($nosubcats = false, $stdtext = 'COM_JTG_SELECT', $stdid = 0, $type = 1)
	{
		$db = $this->getDbo();

		$query = "SELECT * FROM #__jtg_cats WHERE published=1 ORDER BY ordering,id ASC";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$children = array();

		foreach ($rows as $v )
		{
			if ( ( ($nosubcats) AND ($v->parent_id == 0) ) OR (!$nosubcats) )
			{
				$v->title = JText::_($v->title);

				// TODO  unnecessary ?
				$v->name = $v->title;
				$pt	= $v->parent_id;
				$list	= @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$levellimit = 50;
		$rows = JHtml::_('menu.treerecurse', 0, '', array(), $children, max(0, $levellimit - 1), 0, $type);
		$nullcat = array(
				"id" => $stdid,
				"parent" => "0",
				"title" => JText::_($stdtext),
				"description" => "",
				"image" => "",
				"ordering" => "0",
				"published" => "0",
				"checked_out" => "0",
				"name" => JText::_($stdtext),
				"treename" => JText::_($stdtext),
				"children" => ""
		);
		$nullcat = (object) $nullcat;
		array_unshift($rows, $nullcat);

		return $rows;
	}

	/**
	 * Buiold select list for users
	 *
	 * Used to generate generic list of users
	 * Joomla 2.5 JHtml::_('list.users'..); returns duplicate users
	 *
	 * @param   boolean  $nullter  if true, add a 'select' text before first user in array list
	 * @param   string   $where    input sql where statement
	 *
	 * @return array list of users
	 */
	function getUsers($nullter = false, $where = "WHERE block = 0" )
	{
		$db = $this->getDbo();
		$rows = null;
		$query = "SELECT id, name as title FROM #__users " . $where . " ORDER BY name";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$users = array();

		if ($rows)
		{
			foreach ($rows as $v)
			{
				$users[] = $v;
			}
		}

		if ($nullter !== false)
		{
			$nullter = new stdClass;
			$nullter->title = JText::_('COM_JTG_SELECT');
			$nullter->id = null;
			array_unshift($users, $nullter);
		}

		return $users;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $select   param_description
	 * @param   boolean       $nullter  if true, add a 'select' text before first terrain in array list
	 * @param   string        $where    input sql where statement
	 *
	 * @return array list of terrains
	 */
	function getTerrain($select = "*", $nullter = false, $where = null )
	{
		$db = $this->getDbo();
		$rows = null;

		if ($where !== "WHERE id = ")
		{
			$query = "SELECT " . $select . " FROM #__jtg_terrains " . $where . " ORDER BY ordering,title ASC";

			$db->setQuery($query);
			$rows = $db->loadObjectList();
		}

		$terrain = array();

		if ($rows)
		{
			foreach ($rows as $v)
			{
				$v->title = JText::_($v->title);
				$terrain[] = $v;
			}
		}

		if ($nullter !== false)
		{
			$nullter = new stdClass;
			$nullter->title = JText::_('COM_JTG_SELECT');
			$nullter->id = null;
			array_unshift($terrain, $nullter);
		}

		return $terrain;
	}

	/**
	 * retrieve a track access level
	 *
	 * @param   integer  $id  track id
	 *
	 * @return integer access level
	 */
	function getAccess($id)
	{
		$db = $this->getDbo();
		$query = "SELECT access FROM #__jtg_files WHERE id=" . $id ;
		$db->setQuery($query);
		$row = $db->loadResult();

		return $row;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function saveFiles()
	{
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		require_once '../components/com_jtg/helpers/gpsClass.php';
		$fileokay = true;
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$targetdir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';
		$input = JFactory::getApplication()->input;
		$found = $input->getInt('found');
		$params = JComponentHelper::getParams('com_jtg');

		for ($i = 0;$i < $found;$i++)
		{
			$existingfiles = JFolder::files($targetdir);
			$import = $input->get('import_' . $i);

			if ( $import !== null )
			{
				$catid = $input->get('catid_' . $i, null, 'array');
				if ($catid) {
					$data['catid'] = $catid ? implode(',', $catid) : '';
				}
				else {
					$data['catid'] = $params->get('jtg_param_default_cat');
				}
				$data['level'] = $input->get('level_' . $i, 0, 'integer');
				$data['title'] = $input->get('title_' . $i, '', 'string');
				$data['alias'] = $input->get('alias_' . $i, '', 'string');
				$terrain = $input->get('terrain_' . $i, null, 'array');

				if ($terrain)
				{
					$data['terrain'] = $terrain ? implode(',', $terrain) : '';
				}
				else
				{
					$data['terrain'] = "";
				}

				$data['description'] = $input->get('desc_' . $i, '', 'raw');
				$file = $input->get('file_' . $i, '', 'raw');
				$file_replace = $input->get('file_replace_' . $i);
				$data['hidden'] = $input->get('hidden_' . $i);
				$data['published'] = !$data['hidden'];
				$file_tmp = explode('/', $file);
				$filename = strtolower($file_tmp[(count($file_tmp) - 1)]);
				$target = File::MakeSafe($filename);
				$extension = JFile::getExt($filename);

				// Truncate filename to 127 characters
				if (strlen($target) > 127)
				{
					$target = substr($target, 0, 123);
				   $target .= "." . $extension;
				}

				if ( (!$file_replace ) and (in_array($target, $existingfiles)) )
				{
					$fncount = 1;

					while ($fncount < 1000)
					{
						$basename = JFile::stripExt($target);
						if (strlen($target) > 124) $basename = substr($basename,0,119);
						$target = $basename. '_' . $fncount . "." . $extension;

						if (!in_array($target, $existingfiles))
						{
							break;
						}

						$fncount++;
					}
					if ( $fncount == 1000 )
					{
						$app->enqueueMessage("Booah! No free Filename available! <i>" . $file . "</i>", 'error');
						return false;
					}
				}
				$data['file'] = $target;

				$data['uid'] = $input->get('uid_' . $i);
				$data['date'] = $input->get('date_' . $i);
				/*
				 * $images = JFactory::getApplication()->input->files->get('images_'.$i,);
				*/
				$data['access'] = $input->getInt('access_' . $i);

				// TODO use $target below!!
				$gpsData = new GpsDataClass($file, $filename);
				$errors = $gpsData->displayErrors();

				if ($errors)
				{
					// file is NOT OK
					$fileokay = false;
					$map = "";
					$coords = "";
					$distance_float = 0;
					$data['distance'] = 0;
					$app->enqueueMessage(JText::_('COM_JTG_NO_SUPPORT') . "<br>" . $errors);

					// Remove file before exiting
					if (!JFile::delete($file))
					{
						// TODO JTEXT + warning

						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JTG_FILE_DELETE_FAILED', $file), 'Warning');

						// TODO check if exit is correct here ???
						exit;
					}
				}
				else
				{
					// file is OK
					$fileokay = true;
					$iconCoords = $gpsData->getIconCoords($params['jtg_param_icon_loc']);
					$data['icon_n'] = $iconCoords[1];
					$data['icon_e'] = $iconCoords[0];
					$data['start_n'] = $gpsData->start[1];
					$data['start_e'] = $gpsData->start[0];
					$coords = $gpsData->allCoords;
					$data['isTrack'] = $gpsData->isTrack;
					$data['isWaypoint'] = $gpsData->isWaypoint;
					$data['isRoute'] = $gpsData->isRoute;
					$data['isCache'] = $gpsData->isCache;

					$data['distance'] = $gpsData->distance;
					$data['ele_asc'] = $gpsData->totalAscent;
					$data['ele_desc'] = $gpsData->totalDescent;
				}

				if ($fileokay == true)
				{
					/*
					 * Upload the file
					 * $upload_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';
					 * $filename = explode('/',$file);
					 * $filename = $filename[(count($filename)-1)];
					 * $filename = JFile::makeSafe($filename);
					 */

					if (!JFile::copy($file, $targetdir . $target))
					{
						// TODO translation string 
						$app->enqueueMessage("Upload failed (file: \"" . $file . "\") !",'error');
					}
					else
					{
						chmod($targetdir . $target, 0664);
					}

					if (!JFile::delete($file))
					{
						// TODO translation string 
						$app->enqueueMessage("Erasing failed (file: \"" . $file . "\")",'error');
					}

					$trackTable = $this->getTable();
					$trackTable->bind($data);
					$trackTable->newTags = $input->get('tags_'.$i);
					$trackTable->check();
					$trackTable->store();
				}
			}
		}

		return true;
	}

	/**
	 * get a list for default maps
	 *
	 * @param   unknown_type  $exclusion  param_description
	 *
	 * @return unknown
	 */
	function getDefaultMaps()
	{
		$db = $this->getDbo();

		$query = "SELECT id,name FROM #__jtg_maps WHERE published=1
				AND NOT (param LIKE \"%isBaseLayer: false%\" OR param LIKE \"%isBaseLayer:false%\")";
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$newresult = array();


		foreach ($result as $k => $v)
		{
			$newresult[$k] = $v;
			$newresult[$k]->name = JText::_($newresult[$k]->name);
		}

		return $newresult;
	}

	/**
	 * function_description
	 *
	 * @param   object  $track  track object
	 *
	 * @return return_description
	 */
	function importFromJPT($track)
	{
		// TODO Deprecated, can be replacd by import from injooosm
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		require_once '../components/com_jtg/helpers/gpsClass.php';
		$db = $this->getDbo();
		$fileokay = false;
		$targetdir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';
		$sourcedir = JPATH_SITE . '/components/com_joomgpstracks/uploaded_tracks/';
		$existingfiles = JFolder::files($targetdir);
		$file = $sourcedir . $track['file'];
		$file_tmp = explode('/', $file);
		$file_tmp = str_replace(' ', '_', strtolower($file_tmp[(count($file_tmp) - 1)]));
		$file_tmp = explode('.', $file_tmp);
		$extension = $file_tmp[(count($file_tmp) - 1)];
		unset($file_tmp[(count($file_tmp) - 1)]);
		$file_tmp = trim(implode('.', $file_tmp));
		$file_tmp = str_replace('#', '', $file_tmp);
		$file_tmp = str_replace('\&amp;', '', $file_tmp);
		$file_tmp = str_replace('\&', '', $file_tmp);

		// Truncate filename to 50 characters
		if (strlen($file_tmp) > 50)
		{
			$file_tmp = substr($file_tmp, 0, 50);
		}

		$target = $file_tmp . "." . $extension;
		$target = JFile::makeSafe($target);

		if ( in_array($target, $existingfiles) )
		{
			$randnumber = (50 - strlen($target));
			$fncount = 0;

			while (true)
			{
				$target = $file_tmp . '_' . $fncount . "." . $extension;

				if (!in_array($target, $existingfiles) )
				{
					break;
				}
				// Man weiß ja nie ;)

				if ( $fncount > 10000 )
				{
					die("<html>Booah! No free Filename available!<br />\"<i>" . $file . "</i>\"</html>");
				}

				$fncount++;
			}
		}

		// 	get the start coordinates $target
		// TODO GPSCLASS deprecated, (was in use in importFromJPT)
		$gps_old = new gpsClass;
		$gps_old->gpsFile = $file;
		$isTrack = $gps_old->isTrack();
		$isWaypoint = $gps_old->isWaypoint();
		$isRoute = "0";

		if ($start = $gps_old->getStartCoordinates())
		{
			$fileokay = true;
		}
		else
		{
			// TODO print an error message
			$alert_text = json_encode(JText::_('COM_JTG_NO_SUPPORT') . "(2): " . $target);
			echo "<script type='text/javascript'>alert($alert_text);window.history.back(-1);</script>";
		}

		if ($fileokay == true)
		{
			if (!JFile::copy($file, $targetdir . $target))
			{
				echo "Upload failed (file: \"" . $file . "\") !\n";
			}
			else
			{
				chmod($targetdir . $target, 0664);
			}

			$cfg = JtgHelper::getConfig();
			$types = explode(',', $cfg->type);
			$query = "INSERT INTO #__jtg_files SET"
			. "\n uid='" . $track['uid'] . "',"
			. "\n catid='0',"
			. "\n title='" . $track['title'] . "',"
			. "\n file='" . $target . "',"
			. "\n description='" . $track['description'] . "',"
			. "\n date='" . $track['date'] . "',"
			. "\n start_n='" . $track['start_n'] . "',"
			. "\n start_e='" . $track['start_e'] . "',"
			. "\n distance='" . $track['distance'] . "',"
			. "\n ele_asc='" . $track['ele_asc'] . "',"
			. "\n ele_desc='" . $track['ele_desc'] . "',"
			. "\n level='" . $track['level'] . "',"
			. "\n access='" . $track['access'] . "',"
			. "\n istrack='" . (int) $isTrack . "',"
			. "\n iswp='" . (int) $isWaypoint . "',"
			. "\n isroute='" . (int) $isRoute . "'";
			$db->setQuery($query);
			if (! $db->execute() )
			{
				echo $db->stderr();
				return false;
			}

			// Start picture import
			$query = "SELECT id FROM #__jtg_files WHERE file='" . $target . "'";
			$db->setQuery($query);
			$result = $db->loadObject();

			$imagedirsource = JPATH_SITE . "/images/joomgpstracks/" . md5($track['title']) . '/';
			$imagedirsourcedir = JFolder::files($imagedirsource);
			$imagedirdestination = JPATH_SITE . "/images/jtrackgallery/" . $result->id . '/';

			if ((!JFolder::exists($imagedirdestination)) AND (count($imagedirsourcedir) > 0) )
			{
				JFolder::create($imagedirdestination, 0777);
			}

			foreach ( $imagedirsourcedir AS $imagetocopy )
			{
				if (!JFile::copy($imagedirsource . $imagetocopy, $imagedirdestination . $imagetocopy))
				{
					echo "Upload failed:<pre>\"" . $imagedirsource . $imagetocopy . "\"</pre> to <pre>\"" . $imagedirdestination . $imagetocopy . "\"</pre>\n";

					return false;
				}
			}

			// End picture import
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function saveFile()
	{
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		require_once '../components/com_jtg/helpers/gpsClass.php';

		$db = $this->getDbo();
		$user = JFactory::getUser();

		// Get the post data
		$input = JFactory::getApplication()->input;
		$catid = $input->get('catid', null, 'array');
		$data['catid'] = $catid ? implode(',', $catid) : '';
		$data['level'] = $input->get('level', 0, 'integer');
		$data['title'] = trim($input->get('title', '', 'string'));
		$terrain = $input->get('terrain', null, 'array');

		if ($terrain)
		{
			$data['terrain'] = $terrain ? implode(',', $terrain) : '';
		}
		else
		{
			$data['terrain'] = "";
		}

		$data['default_map'] = (int) $input->get('default_map');

		$data['description'] = $input->get('description', '', 'raw');
		$file = $input->files->get('file');
		$file_tmp = explode('/', $file);
		$filename = strtolower($file_tmp[(count($file_tmp) - 1)]);
		$file_tmp = str_replace(' ', '_', $filename);
		$file_tmp = explode('.', $file_tmp);
		$extension = $file_tmp[(count($file_tmp) - 1)];
		unset($file_tmp[(count($file_tmp) - 1)]);
		$file_tmp = trim(implode('.', $file_tmp));
		$file_tmp = str_replace('#', '', $file_tmp);
		$file_tmp = str_replace('\&amp;', '', $file_tmp);
		$file_tmp = str_replace('\&', '', $file_tmp);

		// Truncate filename to 50 characters
		if (strlen($file_tmp) > 50)
		{
			$file_tmp = substr($file_tmp, 0, 50);
		}

		$target = $file_tmp . "." . $extension;
		$data['uid'] = $input->get('uid');
		$data['date'] = date("Y-m-d");
		$images = $input->files->get('images');
		$data['access'] = $input->getInt('access');
		$data['hidden'] = $input->get('hidden');
		$data['published'] = !$data['hidden'];

		// Upload the file
		$upload_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';
		$fncount = 1;

		while (true)
		{
			if (!JFile::exists($upload_dir . $target))
			{
				if (!JFile::upload($file['tmp_name'], $upload_dir . $target))
				{
					echo JText::_('COM_JTG_UPLOAD_FAILED');
				}
				else
				{
					chmod($upload_dir . $target, 0664);
				}

				break;
			}
			else
			{
				$target = $file_tmp . '_' . $fncount . "." . $extension;

				if ( $fncount > 100 )
				{
					// This would never happen !!
					die("<html>Booah! No free Filename available!<br />\"<i>" . JFile::makeSafe($file['name']) . "</i>\"</html>");
				}

				$fncount++;
			}
		}

		// Get the start coordinates
		$file = $upload_dir . $target;
		$gpsData = new GpsDataClass($file, $target);
		$errors = $gpsData->displayErrors();

		if ($errors)
		{
			// File is NOT OK
			$map = "";
			$coords = "";
			$distance_float = 0;
			$distance = 0;
			$app->enqueueMessage(JText::_('COM_JTG_NO_SUPPORT') . "<br>" . $errors, 'error');

			JFile::delete($upload_dir . $target);

			return false;
		}
		else
		{
			// File is OK
			$fileokay = true;

			$data['file'] = $target;
			$data['start_n'] = $gpsData->start[1];
			$data['start_e'] = $gpsData->start[0];
			$iconCoords = $gpsData->getIconCoords($params['jtg_param_icon_loc']);
			$data['icon_n'] = $iconCoords[1];
			$data['icon_e'] = $iconCoords[0];
			$coords = $gpsData->allCoords;
			$data['isTrack'] = $gpsData->isTrack;
			$data['isWaypoint'] = $gpsData->isWaypoint;
			$data['isRoute'] = $gpsData->isRoute;
			$data['isCache'] = $gpsData->isCache;
			$data['ele_asc'] = $gpsData->totalAscent;
			$data['ele_desc'] = $gpsData->totalDescent;
			$data['distance'] = $gpsData->distance;

			$trackTable = $this->getTable();
			$data['alias'] = trim($input->get('alias', '', 'string'));

			$trackTable->bind($data);
			$trackTable->newTags = $input->get('tags');
			$trackTable->check();
			if (! $trackTable->save()) {
				JFile::delete($file);
				return false;
			}

			$query = "SELECT * FROM #__jtg_files"
			. "\n WHERE file='" . strtolower($filename) . "'";
			$db->setQuery($query);
			$result = $db->loadObject();

			$id = $result->id;

			// Images upload part
			$finput = JFactory::getApplication()->input->files();
			$images = $finput->get('images');

			if (count($images) > 0)
			{
				$cfg = JtgHelper::getConfig();
				$types = explode(',', $cfg->type);

				foreach ($images as $image)
				{
					if ($image['name'] != "")
					{
						$imgfilename = JFile::makesafe($image['name']);
						$ext = JFile::getExt($imgfilename);

						if (in_array(strtolower($ext), $types))
						{
							JtgHelper::createimageandthumbs($id,$image['tmp_name'], $ext, $imgfilename);
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * description: Import tracks from JoomGPSTracks
	 *
	 * @return void
	 */
	function importJPTtracks()
	{
		/* under construction */
		// TODO DEPRECATED
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$importfiles = $this->_fetchJPTfiles;
		$mainframe = JFactory::getApplication();
		require_once '../components/com_jtg/helpers/gpsClass.php';
		$fileokay = true;
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$targetdir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';

		for ($i = 0;$i < count($importfiles);$i++)
		{
			$importfile = $importfiles[$i];
			$existingfiles = JFolder::files($targetdir);

			// 	$import = JFactory::getApplication()->input->get('import_'.$i);
			// 	if ( $import == "on" ) {
			$catid = $importfile['catid'];
			$level = $importfile['level'];
			$title = $importfile['title'];
			$terrain = $importfile['terrain'];
			$desc = $importfile['desc'];
			$file = $importfile['file'];
			$source = $file;
			$file_tmp = explode('/', $file);

			// 			$file_tmp = str_replace(' ','_',strtolower($file_tmp[(count($file_tmp)-1)]));
			$file_tmp = explode('.', $file_tmp);
			$extension = $file_tmp[(count($file_tmp) - 1)];
			unset($file_tmp[(count($file_tmp) - 1)]);
			$file_tmp = trim(implode('.', $file_tmp));
			$file_tmp = str_replace('#', '', $file_tmp);
			$file_tmp = str_replace('\&amp;', '', $file_tmp);
			$file_tmp = str_replace('\&', '', $file_tmp);

			// Truncate filename to 50 characters
			if (strlen($file_tmp) > 50)
			{
				$file_tmp = substr($file_tmp, 0, 50);
			}

			$target = $file_tmp . "." . $extension;
			$target = JFile::makeSafe($target);

			if ( in_array($target, $existingfiles) )
			{
				$randnumber = (50 - strlen($target));
				$fncount = 0;

				while (true)
				{
					$target = $file_tmp . '_' . $fncount . "." . $extension;

					if (!in_array($target, $existingfiles) )
					{
						break;
					}

					if ( $fncount > 10000 )
					{
						die("<html>Booah! No free Filename available!<br />\"<i>" . $file . "</i>\"</html>");
					}

					$fncount++;
				}
			}

			$uid = $importfile['uid'];
			$date = $importfile['date'];
			$access = $importfile['access'];

			// Get the start coordinates $target
			// TODO gpsclass deprecated was in use in importFromJPT
			$gps_old = new gpsClass;
			$gps_old->gpsFile = $file;
			$isTrack = $gps_old->isTrack();
			$isWaypoint = $gps_old->isWaypoint();
			$isRoute = "0";
			$start_n = $importfile['start_n'];
			$start_e = $importfile['start_e'];

			if (!JFile::copy($file, $targetdir . $target))
			{
				// TODO Jtext
				echo "Upload failed (file: \"" . $file . "\") !\n";
			}
			else
			{
				chmod($targetdir . $target, 0664);
			}
			/*
						if (!JFile::delete($file))
							echo "Erasing failed (file: \"" . $file . "\") !\n";

						$start_n = $start[1];
						$start_e = $start[0];
						$coords = $gps_old->getCoords($targetdir.$target);
						$distance = $gps_old->getDistance($coords);			 *
			 */

			$distance = $importfile['distance'];

			$ele[0] = $importfile['ele_asc'];
			$ele[1] = $importfile['ele_desc'];

			// Images upload part
			$cfg = JtgHelper::getConfig();
			$types = explode(',', $cfg->type);

			$query = "INSERT INTO #__jtg_files SET"
			. "\n uid='" . $uid . "',"
			. "\n catid='" . $catid . "',"
			. "\n title='" . $title . "',"
			. "\n file='" . $target . "',"
			. "\n terrain='" . $terrain . "',"
			. "\n description='" . $desc . "',"
			. "\n date='" . $date . "',"
			. "\n start_n='" . $start_n . "',"
			. "\n start_e='" . $start_e . "',"
			. "\n distance='" . $distance . "',"
			. "\n ele_asc='" . $ele[0] . "',"
			. "\n ele_desc='" . $ele[1] . "',"
			. "\n level='" . $level . "',"
			. "\n access='" . $access . "',"
			. "\n istrack='" . (int) $isTrack . "',"
			. "\n iswp='" . (int) $isWaypoint . "',"
			. "\n isroute='" . (int) $isRoute . "'";

			$db->setQuery($query);

			if (! $db->execute())
			{
				echo $db->stderr();

				return false;
			}
			$query = "SELECT id FROM #__jtg_files WHERE file='" . strtolower($filename) . "'";

			$db->setQuery($query);
			$rows = $db->loadObject();

			// Images upload part
			$newimages = JFactory::getApplication()->input->files->get('images');
			$cfg = JtgHelper::getConfig();
			$types = explode(',', $cfg->type);

			if (count($newimages) > 0 )
			{
				foreach ($newimages['name'] as $newimage)
				{
					$filename = JFile::makeSafe($newimage['name']);
					$ext = JFile::getExt($filename);

					if (in_array($ext, $types))
					{
						JtgHelper::createimageandthumbs($row->id, $image['tmp_name'], $ext, $filename);
					}
				}
			}
		}

		return false;
	}

	/**
	 * Read image list from files system
         *   deprecated; used to be called getImages()
	 *
	 * @param   unknown_type  $id  param_description
	 *
	 * @return return_description
	 */
	function getImageFiles($id)
	{
		jimport('joomla.filesystem.folder');
		$img_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id;

		if (!JFolder::exists($img_dir))
		{
			return null;
		}

		$images = JFolder::files($img_dir);

		return $images;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function updateFile()
	{
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		require_once '../components/com_jtg/helpers/gpsClass.php';

		$db = $this->getDbo();
		$user = JFactory::getUser();

		// Get the post data
		$input = JFactory::getApplication()->input;
		$id = $input->getInt('id');
		$data['id'] = $id;
		$catid = $input->get('catid', null, 'array');
		$data['catid'] = $catid ? implode(',', $catid) : '';
		$data['level'] = $input->get('level', 0, 'integer');
		$data['title'] = $input->get('title', '', 'string');
		$data['alias'] = trim($input->get('alias', '', 'string'));
		$data['hidden'] = $input->get('hidden');
		$data['published'] = $input->get('published');

		$data['default_map'] = (int) $input->get('default_map');

		$imagelist = $this->getImages($data['id']);
		$imgpath = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id . '/';

		foreach ($imagelist as $image)
		{
			$delimage = $input->get('deleteimage_' . $image->id);
			if ($delimage !== null)
			{
				JFile::delete($imgpath . $delimage);
				JFile::delete($imgpath . 'thumbs/' . 'thumb0_' . $delimage);
				JFile::delete($imgpath . 'thumbs/' . 'thumb1_' . $delimage);
				JFile::delete($imgpath . 'thumbs/' . 'thumb2_' . $delimage);
				$query = "DELETE FROM #__jtg_photos\n WHERE id='".$image->id."'";
				$db->setQuery($query);
				if (! $db->execute())
				{
					echo $db->stderr();
				}
			}
			// Set image title
			$img_title = $input->get('img_title_' . $image->id, '', 'string');
			if ($img_title !== null and $img_title != $image->title) {
				$query = "UPDATE #__jtg_photos SET title=".$db->quote($img_title)." WHERE id='".$image->id."'";
				$db->setQuery($query);

				if (! $db->execute())
				{
					echo $db->stderr();
				}
			}
		}

		$data['date'] = $input->get('date',date("Y-m-d"));
		$terrain = $input->get('terrain', null, 'array');

		// ToDo: empty Terrain = bad
		if ($terrain != "")
		{
			$data['terrain'] = $terrain ? implode(',', $terrain) : '';
		}

		$data['description'] = $input->get('description', '', 'raw');
		$data['uid'] = $input->get('uid');

		/*
		if ( $data['date'] == "" )
		{
			$data['date'] = date("Y-m-d");
		}
		*/

		$data['access'] = $input->getInt('access');

		// 	images upload part
		$newimages = $input->files->get('images', array(), 'array');

		if (count($newimages) > 0)
		{
			$cfg = JtgHelper::getConfig();
			$types = explode(',', $cfg->type);
			JFolder::create($imgpath, 0777);

			foreach ($newimages as $newimage)
			{
				$filename = JFile::makeSafe($newimage['name']);
				$ext = JFile::getExt($filename);

				if (in_array(strtolower($ext), $types))
				{
					JtgHelper::createimageandthumbs($id,$newimage['tmp_name'], $ext, $filename);
				}
			}
		}

		$trackTable = $this->getTable();
		$trackTable->bind($data);
		$trackTable->newTags = $input->get('tags');
		$trackTable->check();
		if (!$trackTable->store())
		{
			$app->enqueueMessag('Error storing track: '.$db->stderr(),'error');;

			return false;
		}

		return true;
	}

	public function getTable($name = 'jtg_files', $prefix = 'Table', $options = [])
	{
		return parent::getTable($name, $prefix, $options);
	}

	public function getForm($data = array(), $loadData = true)
   {
      return $this->loadForm('com_jtg.track','track',array('load_data' => $loadData));
   }
}
