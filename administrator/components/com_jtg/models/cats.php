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

// No direct access
defined('_JEXEC') or die('Restricted access');

// Import Joomla! libraries
jimport('joomla.application.component.model');
/**
 * Model Class Categories
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgModelCats extends JModelLegacy
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
		$mainframe = JFactory::getApplication();

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart	= $mainframe->getUserStateFromRequest($this->option . '.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		// 	$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
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
	 * @return object
	 */
	function getPics()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pics))
		{
			$folder = JPATH_SITE . '/images/jtrackgallery/cats/';
			jimport('joomla.filesystem.folder');
			$files = JFolder::files($folder);
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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

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
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

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
			$newresult[$k]->name = JText::_($newresult[$k]->title);
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

}
