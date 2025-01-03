<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @copyright   2023 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 *
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * JtgModelTag class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.9.35
 */



class JtgModelTag extends ListModel
{
	/**
	 * files data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * files total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Constructor
	 */


	//Override construct to allow filtering and ordering on our fields
	public function __construct($config = array()) {
        if (empty($config['filter_fields']))
	    	{
		    	$config['filter_fields'] = array(
					'search',
					'cat',
					'mindist',
					'maxdist');
				$cfg = JtgHelper::getConfig();
				if ($cfg->uselevel) $config['filter_fields'][] = 'tracklevel';
	    	}
	 		parent::__construct($config);
	}

	public function getTable($name = 'jtg_files', $prefix = 'Table', $options = [])
	{
		return parent::getTable($name, $prefix, $options);
	}

	protected function getListQuery(){
		$db = $this->getDbo();
		$user = Factory::getUser();
		$uid = $user->id;
		$input = Factory::getApplication()->input;

		$tagid = $input->get('id',null);
		if (version_compare(JVERSION,'4.0','ge')) {
			$query = $this->getTable()->getTagsHelper()->getTagItemsQuery($tagid);
		}
		else {
			$tagsHelper = new JHelperTags;
			$query = $tagsHelper->getTagItemsQuery($tagid);
		}
		$query->join('LEFT','#__jtg_files AS a ON m.content_item_id = a.id');
		$query->select("a.*");

		if (!is_null($this->getState('filter.cat')))
		{
			$query->where('a.catid LIKE '.$db->quote('%'.$this->getState('filter.cat').'%'));
		}

		$tracklevel = $this->getState('filter.tracklevel');
		if (!empty($tracklevel)) {
			$query->where('a.level = '.$db->quote($tracklevel));
		}
		$query->where("( a.published = 1 AND a.hidden = 0 )");
	
		// Filter: like / search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('title LIKE ' . $like);
		}
		$mindist = $this->getState('filter.mindist');
		if (!empty($mindist)) $query->where("distance>".$mindist);
		$maxdist = $this->getState('filter.maxdist');
		if (!empty($maxdist)) $query->where("distance<".$maxdist);
		
		$orderby = $this->_buildContentOrderBy(false);
		if (!empty($orderby)) $query->order($orderby);

		return $query;
	}

	/**
	 * function_description
	 *
	 * @global string $option
	 * @return string
	 */
	protected function _buildContentOrderBy (bool $addorder=true)
	{
		$mainframe = Factory::getApplication();
		$params = $mainframe->getParams();
		$ordering = '';

		switch ($params->get('jtg_param_track_ordering'))
		{
			case 'none':
				$ordering = ' a.id ASC';
				break;
			case 'title_a':
				$ordering = ' a.title ASC';
				break;
			case 'title_d':
				$ordering = ' a.title DESC';
				break;
			case 'level_a':
				$ordering = ' a.level ASC';
				break;
			case 'level_d':
				$ordering = ' a.level DESC';
				break;
			case 'hits_a':
				$ordering = ' a.hits ASC';
				break;
			case 'hits_d':
				$ordering = ' a.hits DESC';
				break;
			default:
				$ordering = ' a.id ASC';
				break;
		}

		// V0.9.17: Order $filter_order is set to $ordering (default ordering) when no other ordering is set by the user
		$filter_order = $mainframe->getUserStateFromRequest($this->option . 'filter_order', 'filter_order', $ordering, 'cmd');

		if ($filter_order == $ordering)
		{
			$filter_order_Dir = '';
		}
		else
		{
			$filter_order_Dir = $mainframe->getUserStateFromRequest($this->option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');
		}

		if ($filter_order == '')
		{
			$orderby = '';
		}
		elseif ($filter_order == $ordering)
		{
			$orderby = $ordering;
		}
		else
		{
			$orderby = $filter_order . ' ' . $filter_order_Dir . ', id ASC';
		}

      if ($addorder && !empty($orderby)) $orderby = ' ORDER BY '.$orderby;
		return $orderby;
	}

   public function getCatList()
   {
		$db = $this->getDbo();
		$query = "SELECT id,title FROM #__jtg_cats WHERE published = 1 ORDER BY ordering";
		$db->setQuery($query);
		return $db->loadAssocList();
	}

   public function getCatName($catid=null)
   {
		$db = $this->getDbo();
		$query = "SELECT title FROM #__jtg_cats WHERE published = 1";
		if ( !is_null($catid) )
			$query .= " AND id =".$db->quote($catid);
		$db->setQuery($query);
		return $db->loadResult();
	}
}
