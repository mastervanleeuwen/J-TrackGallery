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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;

/**
 * JtgModelMaps class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgModelMaps extends ListModel
{
	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * function_description
	 *
	 * @param   string  $direction  param_description
	 *
	 * @return boolean
	 */
	function move($direction)
	{
		$row = $this->getTable('jtg_maps');

		if (!$row->load($this->_id))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		if (!$row->move($direction))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
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
		$app = Factory::getApplication();

		// Get the pagination request variables
		$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart	= $app->getUserStateFromRequest($this->option . '.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		// $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$limitstart = Factory::getApplication()->input->get('limitstart', 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$array = Factory::getApplication()->input->get('cid', array(0), 'array');
		$edit	= Factory::getApplication()->input->get('edit', true);

		if ($edit)
		{
			$this->setId((int) $array[0]);
		}
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	protected function _buildQuery()
	{
		$orderby = $this->_buildContentOrderBy();
		$query = "SELECT * FROM #__jtg_maps"
		. $where
		. $orderby;

		return $query;
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
		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			$query = 'DELETE FROM #__jtg_maps WHERE id IN ( ' . $cids . ' )';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   string  $id  param_description
	 *
	 * @return object
	 */
	function getMap($id)
	{
		$db = $this->getDbo();
		$query = "SELECT * FROM #__jtg_maps"
		. "\n WHERE id=" . $id;
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @global string $option
	 * @return string
	 */
	protected function _buildContentOrderBy()
	{
		$app = Factory::getApplication();

		$filter_order		= $app->getUserStateFromRequest(
				$this->option . 'filter_order', 'filter_order', 'ordering', 'cmd');
		$filter_order_Dir	= $app->getUserStateFromRequest(
				$this->option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

		if ($filter_order == 'ordering')
		{
			$orderby 	= ' ORDER BY ordering ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , id ';
		}

		return $orderby;
	}

	/**
	 * function_description
	 *
	 * @return string
	 */
	protected function _buildContentWhere()
	{
		$search = Factory::getApplication()->input->get('search');
		$where = array();
		$db = $this->getDbo();

		if ($search)
		{
			$where[] = 'LOWER(a.name) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
			$where[] = 'LOWER(b.name) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
		}

		$where = (count($where) ? ' WHERE ' . implode(' OR ', $where) : '');

		return $where;
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
			$maps = $this->getMaps();
			$this->_total = count($maps);
		}

		return $this->_total;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $order  param_description
	 *
	 * @return Object
	 */
	function getMaps($order=false)
	{
		$db = $this->getDbo();
		$sql = 'Select * from #__jtg_maps ';

		if ($order)
		{
			$sql .= 'ORDER BY ' . $order;
		}
		else
		{
			$sql .= 'ORDER BY ordering asc';
		}

		$db->setQuery($sql);
		$maps = $db->loadObjectlist();

		return $maps;
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
	 * publish or unpublish some track(s)
	 *
	 * @param   array   $cid      array of track IDs
	 * @param   string  $publish  1 to publish, O to unpublish
	 *
	 * @return bool true on success
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user = Factory::getUser();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			$query = 'UPDATE #__jtg_maps'
			. ' SET published = ' . (int) $publish
			. ' WHERE id IN ( ' . $cids . ' )';
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
	 * @return return_description
	 */
	function saveMap()
	{
		$db = $this->getDbo();

		// Get the post data
		$input = Factory::getApplication()->input;
		$publish = $input->getInt('publish');
		$order = $input->getInt('order');
		$name = $input->get('name', '', 'string');
		$name = htmlentities($name);

		$type = $input->getInt('type');
		// Raw extraction remove link (attribution need links)
		$param = $input->get('param', '', 'array');
		$param = str_replace("'", '&#39;', $param[0]);
		$apikey = $input->get('apikey', '', 'string');

		$checked_out = $input->get('checked_out');

		if ( ( $name == "" ) )
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_MAP_NOT_SAVED'), 'Warning');

			return false;
		}

		$query = "INSERT INTO #__jtg_maps SET"
		. "\n name=" . $db->quote($name) . ","
		. "\n ordering='" . $order . "',"
		. "\n published='" . $publish . "',"
		. "\n type=" . $type . ","
		. "\n param=" . $db->quote($param) . ","
		. "\n apikey=" . $db->quote($apikey) . ","
		. "\n checked_out='" . $checked_out . "'";

		if ($script)
		{
			$query .= ",\n script='" . $script . "'";
		}

		$db->setQuery($query);

		if (!$db->execute())
		{
			die($db->_errorMsg);
		}

		return true;
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
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function updateMap()
	{
		$db = $this->getDbo();

		// Get the post data
		$input = Factory::getApplication()->input;
		$publish = $input->getInt('publish');
		$order = $input->getInt('order');
		$id = $input->getInt('id');
		$name = $input->get('name', '', 'string');
		$name = htmlentities($name);
		$type = $input->getInt('type','0');
		$param = $input->get('param', '', 'array');
		$param = str_replace("'", '&#39;', $param[0]);

		$apikey = $input->get('apikey', '', 'string');

		$checked_out = $input->get('checked_out');

		if ( ( $name == "" ) )
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_MAP_NOT_SAVED'), 'Warning');
			return false;
		}

		$query = "UPDATE #__jtg_maps SET"
		. "\n name=" . $db->quote($name) . ","
		. "\n ordering='" . $order . "',"
		. "\n published='" . $publish . "',"
		. "\n type='" . $type . "',"
		. "\n param=" . $db->quote($param) . ","
		. "\n apikey=" . $db->quote($apikey) . ","
		. "\n checked_out='" . $checked_out . "'"
		. "\n WHERE id=".$id;

		$db->setQuery($query);

		$db->execute();

		return true;
	}
}
