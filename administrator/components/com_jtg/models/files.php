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

// Import Joomla! libraries
jimport('joomla.application.component.model');
/**
 * Model Class Files
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgModelFiles extends JModelLegacy
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
		$gpsData = new GpsDataClass($cfg->unit);

		// Do not use cache here
		$gpsData ->loadFileAndData($file, $file);

		if ($gpsData->displayErrors())
		{
			return false;
		}

		$isTrack = $gpsData->isTrack;
		$isWaypoint = $gpsData->isWaypoint;
		$isRoute = (int) 0;

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

		if ( $gpsData->start === false )
		{
			return false;
		}

		$db = JFactory::getDBO();

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
		. "\n istrack='" . $gpsData->isTrack . "',"
		. "\n iswp='" . $gpsData->isWaypoint . "',"
		. "\n isroute='" . $gpsData->isRoute . "',"
		. "\n iscache='" . $gpsData->isCache . "',"
		. "\n start_n='" . $gpsData->start[1] . "',"
		. "\n start_e='" . $gpsData->start[0] . "',"
		. "\n distance='" . $gpsData->distance . "',"
		. "\n ele_asc='" . $gpsData->totalAscent . "',"
		. "\n ele_desc='" . $gpsData->totalDescent . "',"
		. "\n vote='" . $vote . "'"
		. "\n WHERE id='" . $id . "'";

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$db->execute();

		if ($db->getErrorNum())
		{
			echo $db->stderr();

			return 'database not saved';
		}
		else
		{
			return true;
		}
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

		if (count($files['name']) > 0)
		{
			foreach ($files['name'] as $key => $value)
			{
				if ($value != "")
				{
					$filename = JFile::makesafe($value);
					$ext = JFile::getExt($files['name'][$key]);

					if ( ( $types === true ) OR (in_array(strtolower($ext), $types)))
					{
						if ( JtgHelper::uploadfile(
								array(
								"tmp_name" => $files['tmp_name'][$key],
								"name" => $files['name'][$key],
								"type" => $files['type'][$key],
								"error" => $files['error'][$key],
								"size" => $files['size'][$key],
								), $dest
								) === false)
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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__gps_tracks";

		if ($db->setQuery($query))
		{
			return false;
		}

		$rows = $db->loadAssocList();

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

		//        $db = JFactory::getDBO();

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

		$search = JFactory::getApplication()->input->get('search');
		$where = array();
		$db = JFactory::getDBO();

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
	 * function_description
	 *
	 * @param   string  $id  param_description
	 *
	 * @return object
	 */
	function getFile($id)
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = "SELECT * FROM #__jtg_files"
		. "\n WHERE id='" . $id . "'";
		$db->setQuery($query);
		$result = $db->loadObject();

		// If (!$result) return false;
		if ($db->getErrorNum())
		{
			echo $db->stderr();

			return false;
		}

		if (!$result)
		{
			return JTable::getInstance('jtg_files', 'table');
		}

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
	function publish($cid = array(), $publish = 1)
	{
		$user 	= JFactory::getUser();

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
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
			JArrayHelper::toInteger($cid);
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
			JArrayHelper::toInteger($cid);
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
	function delete($cid = array())
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$result = false;

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
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
				// File (gpx?) delete
				$filename = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . $row->file;

				if (JFile::exists($filename))
				{
					JFile::delete($filename);
				}
			}
			// Delete from DB
			$query = 'DELETE FROM #__jtg_files WHERE id IN ( ' . $cids . ' )';
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

		$size = count($levels);

		if ( $size > 6)
		{
			$size = 6;
		}

		return JHtml::_('select.genericlist', $levels, 'level', 'size="' . $size . '"', 'id', 'title', $selected);
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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

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
		$nullcat = JArrayHelper::toObject($nullcat);
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
		$db = JFactory::getDBO();
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
		$db = JFactory::getDBO();
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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = "SELECT access FROM #__jtg_files WHERE id='" . $id . "'";
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
		$mainframe = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		require_once '../components/com_jtg/helpers/gpsClass.php';
		$fileokay = true;
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$targetdir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';
		$found = JRequest::getInt('found');

		for ($i = 0;$i < $found;$i++)
		{
			$existingfiles = JFolder::files($targetdir);
			$import = JFactory::getApplication()->input->get('import_' . $i);

			if ( $import !== null )
			{
				$catid = JFactory::getApplication()->input->get('catid_' . $i, null, 'array');
				$catid = $catid ? implode(',', $catid) : '';
				$level = JFactory::getApplication()->input->get('level_' . $i, 0, 'integer');
				$title = $db->quote(JFactory::getApplication()->input->get('title_' . $i, '', 'string'));
				$terrain = JFactory::getApplication()->input->get('terrain_' . $i, null, 'array');

				if ($terrain)
				{
					$terrain = $terrain ? implode(',', $terrain) : '';
				}
				else
				{
					$terrain = "";
				}

				$desc = $db->escape(implode(' ', JFactory::getApplication()->input->get('desc_' . $i, '', 'array')));
				$file = JFactory::getApplication()->input->get('file_' . $i, '', 'raw');
				$file_replace = JFactory::getApplication()->input->get('file_replace_' . $i);
				$hidden = JFactory::getApplication()->input->get('hidden_' . $i);
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

				$target = JFile::makeSafe($target);

				if ( (!$file_replace ) and (in_array($target, $existingfiles)) )
				{
					$fncount = 1;

					while (true)
					{
						$target = $file_tmp . '_' . $fncount . "." . $extension;

						if (!in_array($target, $existingfiles))
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

				$uid = JFactory::getApplication()->input->get('uid_' . $i);
				$date = JFactory::getApplication()->input->get('date_' . $i);
				/*
				 * $images = JFactory::getApplication()->input->files->get('images_'.$i,);
				*/
				$access = JRequest::getInt('access_' . $i);
				$cache = JFactory::getCache();

				// TODO use $target below!!
				$gpsData = new GpsDataClass("Kilometer");
				$gpsData = $cache->get(array ( $gpsData, 'loadFileAndData' ), array ($file, $filename ), "Kilometer");
				$errors = $gpsData->displayErrors();

				if ($errors)
				{
					// file is NOT OK
					$fileokay = false;
					$map = "";
					$coords = "";
					$distance_float = 0;
					$distance = 0;
					$alert_text = json_encode(JText::_('COM_JTG_NO_SUPPORT') . "\n" . $errors);
					echo "<script type='text/javascript'>alert($alert_text);window.history.back(-1);</script>";

					// Remove file before exiting
					if (!JFile::delete($file))
					{
						// TODO JTEXT + warning
						echo JText::sprintf('COM_JTG_FILE_DELETE_FAILED', $file);

						// TODO check if exit is correct here ???
						exit;
					}
				}
				else
				{
					// file is OK
					$fileokay = true;
					$start_n = $gpsData->start[1];
					$start_e = $gpsData->start[0];
					$coords = $gpsData->allCoords;
					$isTrack = $gpsData->isTrack;
					$isWaypoint = $gpsData->isWaypoint;
					$isRoute = 0;
					$isCache = 0;

					$distance = $gpsData->distance;
					$totalAscent = $gpsData->totalAscent;
					$totalDescent = $gpsData->totalDescent;
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
						// TODO JTEXT + warning
						echo "Upload failed (file: \"" . $file . "\") !\n";
					}
					else
					{
						chmod($targetdir . $target, 0664);
					}

					if (!JFile::delete($file))
					{
						// TODO JTEXT + warning
						echo "Erasing failed (file: \"" . $file . "\") !\n";
					}

					// Images upload part
					$query = "INSERT INTO #__jtg_files SET"
					. "\n uid='" . $uid . "',"
					. "\n catid='" . $catid . "',"
					. "\n title=" . $title . ","
					. "\n file='" . $target . "',"
					. "\n terrain='" . $terrain . "',"
					. "\n description='" . $desc . "',"
					. "\n date='" . $date . "',"
					. "\n start_n='" . $start_n . "',"
					. "\n start_e='" . $start_e . "',"
					. "\n distance='" . $distance . "',"
					. "\n ele_asc='" . $totalAscent . "',"
					. "\n ele_desc='" . $totalDescent . "',"
					. "\n level='" . $level . "',"
					. "\n access='" . $access . "',"
					. "\n istrack='" . $isTrack . "',"
					. "\n iswp='" . $isWaypoint . "',"
					. "\n isroute='" . $isRoute . "',"
					. "\n hidden='" . $hidden . "'";

					$db->setQuery($query);
					$db->execute();

					if ($db->getErrorNum())
					{
						echo $db->stderr();

						return false;
					}
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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

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
	 * get a list for default overlays
	 *
	 * @param   unknown_type  $exclusion  param_description
	 *
	 * @return unknown
	 */
	function getDefaultOverlays()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

		$query = "SELECT id,name FROM #__jtg_maps WHERE published=1
				AND (param LIKE \"%isBaseLayer: false%\" OR param LIKE \"%isBaseLayer:false%\")";
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
		$db = JFactory::getDBO();
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
			// 		. "\n file='" . $track['file'] . "',"
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
			. "\n istrack='" . $isTrack . "',"
			. "\n iswp='" . $isWaypoint . "',"
			. "\n isroute='" . $isRoute . "'";
			$db->setQuery($query);
			$db->execute();

			if ($db->getErrorNum())
			{
				echo $db->stderr();

				return false;
			}
			else
			{
				// Start picture import
				$query = "SELECT id FROM #__jtg_files WHERE file='" . $target . "'";
				$db->setQuery($query);
				$result = $db->loadObject();

				if ($db->getErrorNum())
				{
					echo $db->stderr();

					return false;
				}

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

		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		// Get the post data
		$catid = JFactory::getApplication()->input->get('catid', null, 'array');
		$catid = $catid ? implode(',', $catid) : '';
		$level = JFactory::getApplication()->input->get('level', 0, 'integer');
		$title = $db->quote(JFactory::getApplication()->input->get('title', '', 'string'));
		$terrain = JFactory::getApplication()->input->get('terrain', null, 'array');

		if ($terrain)
		{
			$terrain = $terrain ? implode(',', $terrain) : '';
		}
		else
		{
			$terrain = "";
		}

		$default_map = JFactory::getApplication()->input->get('default_map');
		$default_overlays = JFactory::getApplication()->input->get('default_overlays',null,'array');
		if ($default_overlays[0]==0)
		{
			// None has been selected: deselect all other selection (multiple selection)
			$default_overlays=array(0);
		}
		$default_overlays = serialize($default_overlays);

		$desc = $db->escape(implode(' ', JFactory::getApplication()->input->get('description', '', 'array')));
		$file = JFactory::getApplication()->input->files->get('file');
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
		$uid = JFactory::getApplication()->input->get('uid');
		$date = date("Y-m-d");
		$images = JFactory::getApplication()->input->files->get('images');
		$access = JRequest::getInt('access');
		$hidden = JFactory::getApplication()->input->get('hidden');

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

				// Man weiß ja nie ;)
				if ( $fncount > 100 )
				{
					// This would never happen !!
					die("<html>Booah! No free Filename available!<br />\"<i>" . JFile::makeSafe($file['name']) . "</i>\"</html>");
				}

				$fncount++;
			}
		}

		// Get the start coordinates
		// Default unit
		$gpsData = new GpsDataClass("Kilometer");
		$file = $upload_dir . $target;
		$cache = JFactory::getCache();
		$gpsData = $cache->get(array ( $gpsData, 'loadFileAndData' ), array ($file, $target), "Kilometer");
		$errors = $gpsData->displayErrors();

		if ($errors)
		{
			// File is NOT OK
			$map = "";
			$coords = "";
			$distance_float = 0;
			$distance = 0;
			$alert_text = json_encode(JText::_('COM_JTG_NO_SUPPORT') . "\n" . $errors);
			echo "<script type='text/javascript'>alert($alert_text);window.history.back(-1);</script>";

			// TODO before exit, remove downloaded file!!
			exit;
		}
		else
		{
			// File is OK
			$fileokay = true;

			$start_n = $gpsData->start[1];
			$start_e = $gpsData->start[0];
			$coords = $gpsData->allCoords;
			$isTrack = $gpsData->isTrack;
			$isWaypoint = $gpsData->isWaypoint;
			$isRoute = 0;
			$isCache = 0;
			$totalAscent = $gpsData->totalAscent;
			$totalDescent = $gpsData->totalDescent;
			$distance = $gpsData->distance;

			$query = "INSERT INTO #__jtg_files SET"
			. "\n uid='" . $uid . "',"
			. "\n catid='" . $catid . "',"
			. "\n title=" . $title . ","
			. "\n file='" . strtolower($filename) . "',"
			. "\n terrain='" . $terrain . "',"
			. "\n description='" . $desc . "',"
			. "\n date='" . $date . "',"
			. "\n start_n='" . $start_n . "',"
			. "\n start_e='" . $start_e . "',"
			. "\n distance='" . $distance . "',"
			. "\n ele_asc='" . $totalAscent . "',"
			. "\n ele_desc='" . $totalDescent . "',"
			. "\n level='" . $level . "',"
			. "\n access='" . $access . "',"
			. "\n istrack='" . $isTrack . "',"
			. "\n iswp='" . $isWaypoint . "',"
			. "\n isroute='" . $isRoute . "',"
			. "\n iscache='" . $isCache . "',"
			. "\n default_map='" . $default_map . "',"
			. "\n default_overlays='" . $default_overlays . "',"
			. "\n hidden='" . $hidden . "'";

			$db->setQuery($query);
			$db->execute();

			if ($db->getErrorNum())
			{
				JFile::delete($file);

				return false;
			}

			$query = "SELECT * FROM #__jtg_files"
			. "\n WHERE file='" . strtolower($filename) . "'";
			$db->setQuery($query);
			$result = $db->loadObject();

			if ($db->getErrorNum())
			{
				echo $db->stderr();

				return false;
			}

			$id = $result->id;

			// Images upload part
			$imgpath = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id . '/';
			$jInput = JFactory::getApplication()->input;
			$jFileInput = new jInput($_FILES);
			$images = $jFileInput->get('images', array(), 'array');

			if (count($images['name']) > 1)
			{
				$cfg = JtgHelper::getConfig();
				$types = explode(',', $cfg->type);
				JFolder::create($imgpath, 0777);

				foreach ($images['name'] as $key => $value)
				{
					if ($value != "")
					{
						$imgfilename = JFile::makesafe($value);
						$ext = JFile::getExt($images['name'][$key]);

						if (in_array(strtolower($ext), $types))
						{
							JtgHelper::createimageandthumbs($images['tmp_name'][$key], $ext, $imgpath, $imgfilename);
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
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$targetdir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';

		// $found = JRequest::getInt('found');
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
			. "\n istrack='" . $isTrack . "',"
			. "\n iswp='" . $isWaypoint . "',"
			. "\n isroute='" . $isRoute . "'";

			$db->setQuery($query);
			$db->execute();

			if ($db->getErrorNum())
			{
				echo $db->stderr();

				return false;
			}
			else
			{
				// Fehlt noch ...
				$id = 0;
				$images = 0;

				$sourcePath = JPATH_SITE . '/images/joomgpstracks/' . md5($title);
				$destPath = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id;

				if (count($images) > 0 )
				{
					JFolder::create($destPath, 0777);
					/*
					$img_dir = JPATH_SITE . '/images/jtrackgallery/' . md5($title);
					*/

					foreach ($images['name'] as $key => $value)
					{
						$ext = JFile::getExt($images['name'][$key]);

						if (in_array($ext, $types))
						{
							JtgHelper::createimageandthumbs($images['tmp_name'][$key], $ext, $destPath,  $images['name'][$key]);
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $id  param_description
	 *
	 * @return return_description
	 */
	function getImages($id)
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

		$db = JFactory::getDBO();
		$user = JFactory::getUser();

		// Get the post data
		$id = JRequest::getInt('id');
		$catid = JFactory::getApplication()->input->get('catid', null, 'array');
		$catid = $catid ? implode(',', $catid) : '';
		$level = JFactory::getApplication()->input->get('level', 0, 'integer');
		$title = $db->quote(JFactory::getApplication()->input->get('title', '', 'string'));
		$hidden = JFactory::getApplication()->input->get('hidden');
		$published = JFactory::getApplication()->input->get('published');

		$default_map = JFactory::getApplication()->input->get('default_map');
		$default_overlays = JFactory::getApplication()->input->get('default_overlays',null,'array');
		if ($default_overlays[0]==0)
		{
			// None has been selected: deselect all other selection (multiple selection)
			$default_overlays=array(0);
		}
		$default_overlays = serialize($default_overlays);

		$allimages = $this->getImages($id);
		$imgpath = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id . '/';

		if ($allimages)
		{
			foreach ($allimages AS $key => $image)
			{
				$image = JFactory::getApplication()->input->get('deleteimage_' . str_replace('.', null, $image));

				if ($image !== null)
				{
					JFile::delete($imgpath . $image);
					JFile::delete($imgpath . 'thumbs/' . 'thumb0_' . $image);
					JFile::delete($imgpath . 'thumbs/' . 'thumb1_' . $image);
					JFile::delete($imgpath . 'thumbs/' . 'thumb2_' . $image);
				}
			}
		}

		$date = JFactory::getApplication()->input->get('date');
		$terrain = JFactory::getApplication()->input->get('terrain', null, 'array');

		// ToDo: empty Terrain = bad
		if ($terrain != "")
		{
			$terrain = $terrain ? implode(',', $terrain) : '';
		}

		$desc = $db->escape(implode(' ', JFactory::getApplication()->input->get('description', '', 'array')));
		$uid = JFactory::getApplication()->input->get('uid');

		if ( $date == "" )
		{
			$date = date("Y-m-d");
		}

		$access = JRequest::getInt('access');

		// 	images upload part
		$jInput = JFactory::getApplication()->input;
		$jFileInput = new jInput($_FILES);
		$images = $jFileInput->get('images', array(), 'array');

		if (count($images['name']) > 0)
		{
			$cfg = JtgHelper::getConfig();
			$types = explode(',', $cfg->type);
			JFolder::create($imgpath, 0777);

			foreach ($images['name'] as $key => $value)
			{
				$filename = JFile::makesafe($value);
				$ext = JFile::getExt($images['name'][$key]);

				if (in_array(strtolower($ext), $types))
				{
					JtgHelper::createimageandthumbs($images['tmp_name'][$key], $ext, $imgpath, $filename);
				}
			}
		}

		$query = "UPDATE #__jtg_files SET"
		. "\n uid='" . $uid . "',"
		. "\n catid='" . $catid . "',"
		. "\n title=" . $title . ","
		. "\n terrain='" . $terrain . "',"
		. "\n date='" . $date . "',"
		. "\n description='" . $desc . "',"
		. "\n level='" . $level . "',"
		. "\n access='" . $access . "',"
		. "\n published='" . $published . "',"
		. "\n default_map='" . $default_map . "',"
		. "\n default_overlays='" . $default_overlays . "',"
		. "\n hidden='" . $hidden . "'"
		. "\n WHERE id='" . $id . "'";
		$db->setQuery($query);
		$db->execute();

		if ($db->getErrorNum())
		{
			echo $db->stderr();

			return false;
		}
		else
		{
			return true;
		}
	}
}
