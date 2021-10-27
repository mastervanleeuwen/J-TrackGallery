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
jimport('joomla.application.component.model');
use Joomla\Utilities\ArrayHelper;
/**
 * JtgModelComments class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgModelComments extends JModelLegacy
{
	/**
	 * function_description
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$mainframe = JFactory::getApplication();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart	= $mainframe->getUserStateFromRequest($this->option . '.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = JFactory::getApplication()->input->get('limitstart', 0);

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
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$query = "SELECT a.*, b.title AS track FROM #__jtg_comments AS a"
		. "\n LEFT JOIN #__jtg_files AS b ON b.id=a.tid"
		. "\n ORDER BY date DESC";

		return $query;
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
		$user 	= JFactory::getUser();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__jtg_comments'
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
			$query = 'DELETE FROM #__jtg_comments'
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
	 * @param   array  $cid  param_description
	 *
	 * @return object
	 */
	function getComment($cid)
	{
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();
		$cids = implode(',', $cid);

		$query = "SELECT * FROM #__jtg_comments WHERE id IN( " . $cids . " )";
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function saveComment()
	{
		$mainframe = JFactory::getApplication();

		$input = JFactory::getApplication()->input;
		$id     = $input->getInt('id');
		$title  = $input->get('title', '', 'string');
		$text   = $input->get('text', '', 'raw');

		$db = JFactory::getDBO();

		$query = "UPDATE #__jtg_comments SET"
		. "\n title='" . $title . "',"
		. "\n text='" . $text . "'"
		. "\n WHERE id='" . $id . "'";
		$db->setQuery($query);
		$db->execute();

		return true;
	}
}
