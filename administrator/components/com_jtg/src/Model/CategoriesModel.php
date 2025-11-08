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
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams/model
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        http://jtrackgallery.net/
 *
 *
 */

namespace Jtg\Component\Jtg\Administrator\Model;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Session\Session;
use Joomla\Utilities\ArrayHelper;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;

/**
 * Model Class Categories
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class CategoriesModel extends ListModel
{
	/**
	 * Category Images array
	 *
	 * @var array
	 */
	var $_pics = null;

	/**
	 * Category Data array
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
	 */
	public function __construct()
	{
		parent::__construct();
		$app = Factory::getApplication();

		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($this->option . '.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = $app->input->get('limitstart', 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * function_description
	 *
	 * @return object
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
	 * @return object
	 */
	function getPics()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pics))
		{
			$folder = JPATH_SITE . '/images/jtrackgallery/cats/';
			jimport('joomla.filesystem.folder');
			$files = Folder::files($folder);
			$this->_pics = $files;
		}

		return $this->_pics;
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
			$this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * function_description
	 *
	 * @return int
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
	protected function _buildQuery()
	{
		$query = "SELECT * FROM #__jtg_cats ORDER BY ordering";

		return $query;
	}

	/**
	 * function comment
	 *
	 * @param   integer  $id  category id
	 *
	 * @return object
	 */
	function getCat($id)
	{
		$db = $this->getDbo();

		$query = "SELECT * FROM #__jtg_cats"
		. "\n WHERE id='" . $id . "'";

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * get a cat parent
	 *
	 * @param   unknown_type  $exclusion  param_description
	 *
	 * @return unknown
	 */
	function getParent($exclusion=null)
	{
		$db = $this->getDbo();

		$query = "SELECT id,title FROM #__jtg_cats WHERE published=1";

		if ( $exclusion !== null )
		{
			$query .= " AND id != " . $exclusion;
		}

		$query .= "\n ORDER BY title ASC";

		$db->setQuery($query);
		$result = $db->loadObjectList();
		$newresult = array();

		foreach ($result as $k => $v)
		{
			$newresult[$k] = $v;
			$newresult[$k]->name = Text::_($newresult[$k]->title);
		}

		return $newresult;
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
			$newresult[$k]->name = Text::_($newresult[$k]->name);
		}

		return $newresult;
	}
	
	/**
	 * function_description
	 *
	 * @param   int  $cid  category id
	 * @param   int  $direction  direction (+1 = up, -1 = down)
	 * 
	 * @return boolean
	 */
	function move($cid, $direction)
	{
		$row = $this->getTable('Categories');
		$db = $this->getDbo();

		if (!$row->load($cid))
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		if (!$row->move($direction))
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		return true;
	}
	
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function saveCatImage()
	{
		Session::checkToken() or die( 'JINVALID_TOKEN' );
		$files = Factory::getApplication()->input->files->get('files');

		return $this->uploadCatImage($files);
	}

	/**
	 * function_description
	 *
	 * @param   string  $order  param_description
	 * @param   array   $cid    param_description
	 *
	 * @return boolean
	 */
	function saveorder($order, $cid = array())
	{
		$row = $this->getTable();
		$db = $this->getDbo();
		$groupings = array();

		// Update ordering values
		for ( $i = 0; $i < count($cid); $i++ )
		{
			$row->load((int) $cid[$i]);

			// Track categories
			$groupings[] = $row->catid;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];

				if (!$row->store())
				{
					$this->setError($db->getErrorMsg());

					return false;
				}
			}
		}

		// Execute updateOrder for each parent group
		$groupings = array_unique($groupings);

		foreach ($groupings as $group)
		{
			$row->reorder('id = ' . (int) $group);
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   array    $cid      param_description
	 * @param   integer  $publish  param_description
	 *
	 * @return boolean
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	= Factory::getUser();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__jtg_cats'
			. ' SET published = ' . (int) $publish
			. ' WHERE id IN ( ' . $cids . ' )'
			. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )';
			$db = $this->getDbo();
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $files  param_description
	 *
	 * @return return_description
	 */
	function deleteCatImage($files)
	{
		jimport('joomla.filesystem.file');
		$path = JPATH_SITE . "/images/jtrackgallery/cats/";

		$return = true;

		foreach ($files as $file)
		{
			if (!File::delete($path . $file))
			{
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * function_description
	 *
	 * @param   array  $cid  param_description
	 *
	 * @return boolean
	 */
	function delete($cid = array())
	{
		jimport('joomla.filesystem.file');
		$result = false;

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			// Delete the images
			$query = "SELECT * FROM #__jtg_cats"
			. "\n WHERE id IN ( ' . $cids . ' )";

			$db = $this->getDbo();
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}

			foreach ($rows as $row)
			{
				File::delete(JPATH_SITE . '/images/jtrackgallery/cats/' . $row->image);
			}

			// Delete from DB
			$query = 'DELETE FROM #__jtg_cats'
			. ' WHERE id IN ( ' . $cids . ' )';
			$db->setQuery($query);

			if (!$db->execute())
			{
				$this->setError($db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function saveCat()
	{
		// Check the token
		Session::checkToken() or die( 'JINVALID_TOKEN' );

		$db = $this->getDbo();
		$title = Factory::getApplication()->input->get('title', '', 'string');

		if ( $title == "" )
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_NO_TITLE'), 'Warning');

			return false;
		}

		$input = Factory::getApplication()->input;
		$published = $input->getInt('publish');
		$desc = $input->get('desc', '', 'string');

		if ( (substr($desc, 0, 3) == '<p>') AND (substr($desc, -4, 4) == '</p>') )
		{
			// Remove enclosing <p> tags,try translating text, add <p> tags
			$desc = substr($desc, 3, -4);
		}

		$parent = $input->getInt('parent');
		$image = $input->get('catpic');
		$usepace = $input->get('usepace');
		$default_map = $input->get('default_map');

		$db->setQuery("SELECT MAX(ordering) FROM #__jtg_cats");
		$maxordering = $db->loadResult();
		$maxordering++;

		$query = "INSERT INTO #__jtg_cats SET"
		. "\n parent_id='" . $parent . "',"
		. "\n title=" . $db->quote($title) . ","
		. "\n image='" . $image . "',"
		. "\n usepace='" . $usepace . "',"
		. "\n default_map=" . $default_map . ","
		. "\n ordering=" . $maxordering . ","
		. "\n checked_out=0,"
		. "\n description=" . $db->quote(htmlentities($desc)) . ","
		. "\n published='" . $published . "'";

		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $file  param_description
	 *
	 * @return bool true on success
	 */
	function uploadCatImage($file)
	{
		jimport('joomla.filesystem.file');

		if ($file['name'] != "")
		{
			$file['ext'] = File::getext($file['name']);
			$config = JtgHelper::getConfig();
			$allowedimages = $config->type;
			$allowedimages = explode(',', $allowedimages);

			if ( !in_array($file['ext'], $allowedimages) )
			{
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_JTG_NOTALLOWED_FILETYPE', $file['ext']), 'Warning');

				return false;
			}

			$upload_dir = JPATH_SITE . "/images/jtrackgallery/cats/";
			$filename = File::makeSafe(strtolower($file['name']));

			if (File::exists($upload_dir . $filename))
			{
				Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_CATPIC_ALLREADYEXIST'), 'Warning');

				return false;
			}
			else
			{
				$upload = File::upload($file['tmp_name'], $upload_dir . $filename);

				if (!$upload)
				{
					return false;
				}
				else
				{
					return true;
				}
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function updateCat()
	{
		// Check the token
		Session::checkToken() or die( 'JINVALID_TOKEN' );
		
		$db = $this->getDbo();

		$input = Factory::getApplication()->input;
		$id = $input->getInt('id');
		$file = $input->files->get('image');
		$title = $input->get('title', '', 'string');
		$image = $input->get('catpic');
		$usepace = $input->get('usepace');
		$default_map = $input->get('default_map');

		if ( $title == "" )
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_NO_TITLE'), 'Warning');

			return false;
		}

		$published = $input->getInt('publish');
		$desc = $input->get('desc', '', 'string');

		if ( (substr($desc, 0, 3) == '<p>') AND (substr($desc, -4, 4) == '</p>') )
		{
			// Remove enclosing <p> tags,try translating text, add <p> tags
			$desc = substr($desc, 3, -4);
		}

		$parent = $input->getInt('parent');
		$query = "UPDATE #__jtg_cats SET"
		. "\n parent_id='" . $parent . "',"
		. "\n title=" . $db->quote($title) . ","
		. "\n image='" . $image . "',"
		. "\n usepace='" . $usepace . "',"
		. "\n default_map=" . $default_map . ","
		. "\n description=" . $db->quote(htmlentities($desc)) . ","
		. "\n published='" . $published . "'"
		. "\n WHERE id='" . $id . "'";

		$db->setQuery($query);
		$db->execute();

		return true;
	}
}
