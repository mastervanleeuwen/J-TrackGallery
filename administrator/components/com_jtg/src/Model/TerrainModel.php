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

namespace Jtg\Component\Jtg\Administrator\Model;
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Utilities\ArrayHelper;
/**
 * Model Class Terrain
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class TerrainModel extends ListModel
{
	var $_data = null;

	var $_total = null;

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
	 * @param   interger  $tid  terrain id
	 *
	 * @return object
	 */
	function getData($tid=null)
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery($tid);
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
	 * @param   integer  $terrain  terrain id
	 *
	 * @return string
	 */

	protected function _buildQuery($terrain=null)
	{
		$app = Factory::getApplication();
		$orderby	= $this->_buildContentOrderBy();
		$db = $this->getDbo();
		$query = "SELECT * FROM #__jtg_terrains"
		. $orderby;

		if ( $terrain !== null )
		{
			$query .= " WHERE id=" . $terrain;
		}

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
		return;
		$app = Factory::getApplication();

		$filter_order = $app->getUserStateFromRequest($this->option . 'filter_order', 'filter_order', 'title', 'cmd');
		$filter_order_Dir = $app->getUserStateFromRequest($this->option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');

		$orderby = ' ORDER BY ' . $filter_order . ' ' . $filter_order_Dir . ' , title ';

		// Problems if sorted in "Files"-Menu and switched to "Terrain"

		/*
		Return $orderby;
		*/

		// TODO: Why is that return commented!!
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
	 * function_description
	 *
	 * @return return_description
	 */
	function save()
	{
		// Get post data
		$row = Factory::getApplication()->input->getArray();
		$table = $this->getTable('jtg_terrain');
		$table->bind($row);

		if (!$table->store())
		{
			Factory::getApplication()->enqueueMessage($table->getError(), 'Warning');

			return false;
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

			$query = 'UPDATE #__jtg_terrains'
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
	 * @param   array  $cid  param_description
	 *
	 * @return boolean
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			// Delete from DB
			$query = 'DELETE FROM #__jtg_terrains'
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
}
