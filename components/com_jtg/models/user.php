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
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * JtgModelFiles class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */



class JtgModelUser extends JModelList
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
		    	    'mindist',
		    	    'maxdist',
		    	    'trackcat',
		    	    'tracklevel');
	    	}
	 		parent::__construct($config);
	}


	protected function getListQuery(){
		// TODO: add accesslevel logic, or remove completely? replace by per-track access using native Joomla! logic?
		
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		$uid = $user->id;
		
		$query->select('a.*, c.name AS user')
		->from('#__jtg_files as a')
		->join('LEFT','#__users AS c ON a.uid=c.id');
		
		// Filter company
		$trackcat = $this->getState('filter.trackcat');
		if (!empty($trackcat)) {
		    //error_log('Got category from state '.$trackcat);
			$query->where('a.catid LIKE '.$db->quote('%'.$trackcat.'%'));
		}
		else {
		    $trackcat = Factory::getApplication()->input->get('cat');
		    //error_log('Got category from url '.$trackcat);
		    if ($trackcat !== null) {
		        $this->setState('filter.trackcat', $trackcat);
		        $query->where('a.catid LIKE '.$db->quote('%'.$trackcat.'%'));
		    }
		}
		$tracklevel = $this->getState('filter.tracklevel');
		if (!empty($tracklevel)) {
			$query->where('a.level = '.$db->quote($tracklevel));
		}
		$query->where("a.uid=$uid");
	
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

		//	$db->escape($this->getState('list.direction', 'desc')));

		//error_log("Query for track list: ".$db->replacePrefix( (string) $query ));//debug
		
		return $query;
	}


	/**
	 * Get comments this user's comments
	 *
	 * @param  
	 *
	**/
	public function getComments()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$uid = Factory::getUser()->id;
		
		$query->select('a.title as tracktitle, c.*')
		->from('#__jtg_comments as c')
		->join('LEFT','#__jtg_files as a ON c.tid = a.id')
		->where('c.uid='.$uid);
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * Get comments to this user's tracks
	 *
	 * @param  
	 *
	**/
	public function getCommentsToTracks()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$uid = Factory::getUser()->id;
		
		$query->select('a.title as tracktitle, c.*')
		->from('#__jtg_comments as c')
		->join('LEFT','#__jtg_files as a ON c.tid = a.id')
		->where('a.published=1 AND c.published=1')
		->where('a.uid='.$uid);
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * Get total distance etc from database
	 *
	 * @param  
	 *
	 * @return object with results 
	 */
	function getTotals($uid)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('count(a.id) as ntrk, sum(a.distance) as distance, sum(a.ele_asc) as ele_asc, sum(a.ele_desc) as ele_desc')
		->from('#__jtg_files as a')
		->where("a.uid=$uid");
		$db->setQuery($query);
		$result = $db->loadObject();
		return $result;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $selected  param_description
	 *
	 * @return array
	 */
	function getLevel ($selected)
	{
		$return = "\n";
		$cfg = JtgHelper::getConfig();
		$levels = explode("\n", $cfg->level);
		array_unshift($levels, 'dummy');
		$i = 0;

		foreach ($levels as $level)
		{
			if (trim($level) != "")
			{
				if ($i == $selected)
				{
					$selectedlevel = $i;
					$selectedtext = $level;
				}

				$i ++;
			}
		}

		$return .= $selectedlevel . "/" . ($i - 1) . " - " . Text::_(trim($selectedtext));

		return $return;
	}

	/**
	 * function_description
	 *
	 * @global string $option
	 * @return string
	 */
	protected function _buildContentOrderBy (bool $addorder=true)
	{
		$app = Factory::getApplication();

		$params = $app->getParams();
		$ordering = '';

		switch ($params->get('jtg_param_track_ordering'))
		{
			case 'none':
				$ordering = '';
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
			case 'title_a_catid_a':
				$ordering = ' a.title ASC AND a.catid ASC';
				break;
			case 'title_a_catid_d':
				$ordering = ' a.title ASC, a.catid DESC';
				break;
			case 'title_d_catid_a':
				$ordering = ' a.title DESC, a.catid ASC';
				break;
			case 'title_d_catid_d':
				$ordering = ' a.title DESC, a.catid ASC';
				break;
			case 'hits_a':
				$ordering = ' a.hits ASC';
				break;
			case 'hits_d':
				$ordering = ' a.hits DESC';
				break;
			case 'catid_a':
				$ordering = ' a.catid ASC';
				break;
			case 'catid_d':
				$ordering = ' a.catid DESC';
				break;
			default:
				$ordering = '';
				break;
		}

		// V0.9.17: Order $filter_order is set to $ordering (default ordering) when no other ordering is set by the user
		$filter_order = $app->getUserStateFromRequest($this->option . 'filter_order', 'filter_order', $ordering, 'cmd');

		if ($filter_order == $ordering)
		{
			$filter_order_Dir = '';
		}
		else
		{
			$filter_order_Dir = $app->getUserStateFromRequest($this->option . 'filter_order_Dir', 'filter_order_Dir', '', 'word');
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

	/**
	 * function_description
	 *
	 * @param   integer  $id  track id
	 *
	 * @return return_description
	 */
	function getVotes ($id)
	{
		$app = Factory::getApplication();

		$class = array(
				'nostar',
				'onestar',
				'twostar',
				'threestar',
				'fourstar',
				'fivestar',
				'sixstar',
				'sevenstar',
				'eightstar',
				'ninestar',
				'tenstar'
		);

		$db = $this->getDbo();

		// Count votings
		$query = "SELECT COUNT(*) FROM #__jtg_votes" . "\n WHERE trackid='" . $id . "'";

		$db->setQuery($query);
		$count = (int) $db->loadResult();

		// Sum rating
		$query = "SELECT SUM(rating) FROM #__jtg_votes" . "\n WHERE trackid='" . $id . "'";
		$db->setQuery($query);
		$givenvotes = (int) $db->loadResult();

		// Fetch rating
		$rate = null;

		if ($count != 0)
		{
			while ($rate === null)
			{
				$query = "SELECT vote FROM #__jtg_files" . "\n WHERE id='" . $id . "'";

				$db->setQuery($query);
				$rate = $db->loadResult();

				if ($rate === null)
				{
					$newvote = (float) (round(($givenvotes / $count), 3));
					$query = "UPDATE #__jtg_files SET" . " vote='" . $newvote . "'" . " WHERE id='" . $id . "'";
					$db->setQuery($query);

					if (! $db->execute())
					{
						echo $db->stderr();

						return false;
					}
				}
			}
		}
		else
		{
			// Save voting: 0
			$query = "UPDATE #__jtg_files SET" . " vote='0'" . " WHERE id='" . $id . "'";
			$db->setQuery($query);

			if (! $db->execute())
			{
				echo $db->stderr();

				return false;
			}
		}

		$object = array(
				"count" => $count,
				"rate" => (float) $rate,
				"sum" => (int) $givenvotes,
				"class" => $class[(int) round($rate, 0)]
		);

		return $object;
	}

	/**
	 * function_description
	 *
	 * @param   string  $where  where query string
	 *
	 * @return array
	 */
	function getTerrain ($where = null)
	{
		$app = Factory::getApplication();
		$db = $this->getDbo();

		$query = "SELECT * FROM #__jtg_terrains " . $where . " ORDER BY title ASC";

		$db->setQuery($query);
		$row = $db->loadObjectList();
		$terrain = array();

		if ($row)
		{
			foreach ($row as $v)
			{
				$v->title = Text::_($v->title);
				$terrain[] = $v;
			}
		}

		return $terrain;
	}

	/**
	 * Retrieve name, e-mail address of track author
	 *
	 * @param   int  $id track id
	 *
	 * @return  array with search result
	 */
	function getAuthorData ($id)
	{
		$db = $this->getDbo();
		$query = "SELECT a.uid, b.name, b.email FROM #__jtg_files AS a" . "\n LEFT JOIN #__users AS b ON a.uid=b.id" . "\n WHERE a.id='" . $id . "'";

		$db->setQuery($query);
		$user = $db->loadObject();

		return $user;
	}
}
