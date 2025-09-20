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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;

/**
 * JtgModelFiles class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */



class JtgModelFiles extends ListModel
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
				// MvL TODO: what is the function of these? Should they be the names of the database fields, or of the filterform fields
		    	$config['filter_fields'] = array(
					'search',
					'mindist',
					'maxdist',
					'tag');
				$params = JComponentHelper::getParams('com_jtg');
				if ($params->get('jtg_param_use_cat')) $config['filter_fields'][]='trackcat';
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
		// TODO: remove _buildquery below
		// TODO: add accesslevel logic, or remove completely? replace by per-track access using native Joomla! logic?
		
		$db = $this->getDbo();
		$user = Factory::getUser();
		$uid = $user->id;
		
		$input = Factory::getApplication()->input;

		if (!is_null($this->getState('filter.tag')))
      {
			if (version_compare(JVERSION,'4.0','ge')) {
	         $query = $this->getTable()->getTagsHelper()->getTagItemsQuery($this->getState('filter.tag'));
			}
			else {
				$tagsHelper = new JHelperTags;
	         $query = $tagsHelper->getTagItemsQuery($this->getState('filter.tag'));
			}
         $query->join('LEFT','#__jtg_files AS a ON m.content_item_id = a.id');
         $query->select("a.*");
      }
      else {
			$query = $db->getQuery(true);
			$query->select('a.*, c.name AS user')
				->from('#__jtg_files as a')
				->join('LEFT','#__users AS c ON a.uid=c.id');
		}
		
		// Filter company
      $trackcats = $this->getState('filter.trackcat');
		if (!empty($trackcats)) {
			$catselect = "";
			foreach ($trackcats as $trackcat) {
				if (strlen($catselect)) $catselect .= ' OR ';
				$catselect.='a.catid LIKE '.$db->quote('%'.$trackcat.'%');
			}
			$query->where('('.$catselect.')');
		}
		else {
		    $trackcat = $input->get('cat');
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
		// Filter by state (published, trashed, etc.) OR user tracks
		if ($input->get('layout') == 'user') {
			$query->where("a.uid=$uid");
		}
		else {
			if (is_null($uid)) {
				$query->where("( a.published = 1 AND a.hidden = 0 )");
			}
			else {
				$query->where("(( a.published = 1 AND a.hidden = 0 ) OR ( a.uid=$uid))");
			}
		}
	
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

        // This may be a simpler way to sort?
		//->order(	$db->escape($this->getState('list.ordering', 'pa.id')) . ' ' //.
			//	$db->escape($this->getState('list.direction', 'desc')));

		//error_log("Query for track list: ".$db->replacePrefix( (string) $query ));//debug
		
		return $query;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $selected  param_description
	 *
	 * @return return_description
	 */
	function getLevelSelect ($selected)
	{
		$return = "<select name=\"level\">\n";
		$cfg = JtgHelper::getConfig();
		$levels = explode("\n", $cfg->level);
		array_unshift($levels, 'dummy');
		$i = 0;

		foreach ($levels as $level)
		{
			if (trim($level) != "")
			{
				$return .= ("					<option value=\"" . $i . "\"");

				if ($i == $selected)
				{
					$return .= (" selected=\"selected\"");
				}

				$return .= (">");

				if ($i == 0)
				{
					$return .= Text::_('COM_JTG_SELECT');
				}
				else
				{
					$return .= $i . " - " . Text::_(trim($level));
				}

				$return .= ("</option>\n");
				$i ++;
			}
		}

		return $return . "				</select>\n";
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
	 * @global string $option
	 * @return string
	 */
	protected function _buildContentWhere()
	{
		$app = Factory::getApplication();

		$input = $app->input;
		$search = $input->get('search');
		$cat = $input->get('cat');
		$terrain = $input->get('terrain');
		$user = Factory::getUser();
		$uid = $user->id;
		$index = "a";
		$where = array();
		$db = $this->getDbo();

		if ($search)
		{
			$where[] = 'LOWER(a.title) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
			$where[] = 'LOWER(b.title) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
			$where[] = 'LOWER(c.username) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
			// TODO  seems this (next line) is wrong. What is it for?
			// $index = "d";
		}


		if ($terrain)
		{
			// $where[] = '('.$index.'.terrain) = '.$db->Quote( $db->escape(
			// $terrain, true ), false);
			$where[] = '(' . $index . '.terrain) LIKE ' . $db->Quote('%' . $db->escape($terrain, true) . '%', false);
		}

		// Restrict track list to published not hidden tracks OR user tracks
		$pubhid = "(( a.published = '1' AND a.hidden = '0' ) OR ( a.uid='$uid'))";
		$where = (count($where) ? ' WHERE (' . implode(' OR ', $where) . ') ' : '');

		if ($where == "")
		{
			$where = " WHERE " . $pubhid;
		}
		else
		{
			$where .= " AND " . $pubhid;
		}

		if ($cat)
		{
			$where .= ' AND ' . $index . '.catid LIKE ' . $db->Quote('%' . $db->escape($cat, true) . '%', false);
		}

		// Add frontend filtering related to access level

		$access = JtgHelper::giveAccessLevel(); // User access level
		$params = $app->getParams();
		$otherfiles = $params->get('jtg_param_otherfiles');// Access level defined in backend
		$where = JtgHelper::MayIsee($where, $access, $otherfiles);
		return $where;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function getCats ()
	{
		$db = $this->getDbo();

		$query = "SELECT * FROM #__jtg_cats WHERE published=1 ORDER BY ordering ASC";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$limit = count($rows);
		$children = array();

		foreach ($rows as $v)
		{
			$v->title = Text::_($v->title);
			$pt = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}

		$list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, $maxlevel = 9999, $level = 0, $type = 0);
		$list = array_slice($list, 0, $limit);
		$cats = array();
		$nullcat = array(
				'id' => 0,
				'title' => Text::_('JNONE'),
				'name' => Text::_('JNONE'),
				'image' => ""
		);
		$cats[0] = ArrayHelper::toObject($nullcat);

		foreach ($list as $cat)
		{
			if ($cat->treename == $cat->title)
			{
				$title = $cat->title;
			}
			else
			{
				$title = $cat->treename;
			}

			$arr = array(
					'id' => $cat->id,
					'title' => $title,
					'name' => Text::_($cat->title),
					'image' => $cat->image
			);
			$cats[$cat->id] = ArrayHelper::toObject($arr);
		}

		return $cats;
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
		$db = $this->getDbo();

		// $query = "SELECT * FROM #__jtg_terrains ORDER BY ordering,title ASC";
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

	/**
	 * Gives back lat/lon from start (if given) and endpoint to make an
	 * approachlink
	 * Homepage: http://openrouteservice.org/
	 * WIKI: http://wiki.openstreetmap.org/wiki/OpenRouteService
	 *
	 * @param   unknown_type  $to_lat  param_description
	 * @param   unknown_type  $to_lon  param_description
	 * @param   unknown_type  $lang    param_description
	 *
	 * @return array
	 */
	function approachors ($to_lat, $to_lon, $lang)
	{
		$user = Factory::getUser();
		$latlon = JtgHelper::getLatLon($user->id);
		$link = "http://openrouteservice.org/?";

		if (isset($latlon[0]))
		{
			$middle_lon = ((float) $to_lon + (float) $latlon[0]->jtglon) / 2;
			$middle_lat = ((float) $to_lat + (float) $latlon[0]->jtglat) / 2;
			$link .= "start=" . $latlon[0]->jtglon . "," . $latlon[0]->jtglat . "&amp;end=" . $to_lon . "," . $to_lat . "&amp;lat=" . $middle_lat .
			"&amp;lon=" . $middle_lon;
		}
		else
		{
			$link .= "end=" . $to_lon . "," . $to_lat;
		}

		return $link . "&amp;lang=" . $lang . "&amp;pref=";
	}

	/**
	 * Gives back lat/lon from start (if given) and endpoint to make an
	 * approachlink
	 * Homepage: http://maps.cloudmade.com/
	 * WIKI: http://wiki.openstreetmap.org/wiki/CloudMade
	 *
	 * @param   string  $to_lat  param_description
	 * @param   string  $to_lon  param_description
	 * @param   string  $lang    param_description
	 *
	 * @return array
	 */
	function approachcm ($to_lat, $to_lon, $lang)
	{
		$link = "http://maps.cloudmade.com/?";
		$user = Factory::getUser();
		$latlon = JtgHelper::getLatLon($user->id);

		if (isset($latlon[0]))
		{
			if ($latlon[0]->jtglat)
			{
				$from_lat = $latlon[0]->jtglat;
			}

			if ($latlon[0]->jtglon)
			{
				$from_lon = $latlon[0]->jtglon;
			}
		}

		if (isset($from_lon) && isset($from_lat))
		{
			$middle_lon = ((float) $to_lon + (float) $from_lon) / 2;
			$middle_lat = ((float) $to_lat + (float) $from_lat) / 2;
			$link .= "lat=" . $middle_lat . "&amp;";
			$link .= "lng=" . $middle_lon . "&amp;";
			$link .= "directions=" . $from_lat . "," . $from_lon;
			$link .= "," . $to_lat . "," . $to_lon . "&amp;zoom=16";
		}
		else
		{
			$link .= "directions=" . $to_lat . "," . $to_lon . "&amp;";
			$link .= "lat=" . $to_lat . "&amp;";
			$link .= "lng=" . $to_lon . "&amp;";
			$link .= "zoom=15";
		}

		return $link . "&amp;styleId=1&amp;opened_tab=1&amp;travel=";
	}

	/**
	 * Gives back lat/lon from start (if given) and endpoint to make an
	 * approachlink
	 * Homepage: http://maps.cloudmade.com/
	 * WIKI: http://wiki.openstreetmap.org/wiki/CloudMade
	 *
	 * @param   string  $to_lat  latitude
	 * @param   string  $to_lon  longitudex
	 * @param   string  $lang    user language tag
	 *
	 * @return array
	 */
	function approachcmkey ($to_lat, $to_lon, $lang)
	{
		$key = "651006379c18424d8b5104ed4b7dc210";
		$link = "http://navigation.cloudmade.com/" . $key . "/api/0.3/";
		$user = Factory::getUser();
		$latlon = JtgHelper::getLatLon($user->id);

		if (isset($latlon[0]))
		{
			if ($latlon[0]->jtglat)
			{
				$from_lat = $latlon[0]->jtglat;
			}

			if ($latlon[0]->jtglon)
			{
				$from_lon = $latlon[0]->jtglon;
			}
		}

		if (isset($from_lon) && isset($from_lat))
		{
			$middle_lon = ((float) $to_lon + (float) $from_lon) / 2;
			$middle_lat = ((float) $to_lat + (float) $from_lat) / 2;
			$link .= "directions=" . $from_lat . "," . $from_lon . "," . $to_lat . "," . $to_lon . "&amp;" . "lat=" . $middle_lat . "&amp;" . "lng=" .
					$middle_lon;
		}
		else
		{
			$link .= "directions=" . $to_lat . "," . $to_lon . "lat=" . $to_lat . "&amp;" . "lng=" . $to_lon . "&amp;zoom=15";
		}

		return $link . "&amp;zoom=15&amp;travel=";
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
	 * get a list for default overlays
	 *
	 * @param   unknown_type  $exclusion  param_description
	 *
	 * @return unknown
	 */
	function getDefaultOverlays()
	{
		$db = $this->getDbo();

		$query = "SELECT id,name FROM #__jtg_maps WHERE published=1
				AND (param LIKE \"%isBaseLayer: false%\" OR param LIKE \"%isBaseLayer:false%\")";
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
    * sort categories
    *
    * @param  boolean  $sort sort by id instead of title
    * @param  integer $catid select only this category
    *
    * @return sorted rows
    */
	// TODO: this is now a static function in JtgModelFiles and JtgModelJtg; decide where it goes
   static public function getCatsData($sort=false, $catid=null)
   {
      $db = $this->getDbo();

      $query = "SELECT * FROM #__jtg_cats WHERE published = 1";
      if ( !is_null($catid) )
         $query .= " AND id =".$db->quote($catid);
      $query .= "\n ORDER BY title ASC";

      $db->setQuery($query);
      $rows = $db->loadObjectList();

      if ( $sort === false )
      {
         return $rows;
      }
      else
      {
         $nullcat = array(
               "id"        => 0,
               "parent"    => 0,
               "title"        => "<label title=\"" . Text::_('COM_JTG_CAT_NONE') . "\">-</label>",
               "description"  => null,
               "image"        => null,
               "ordering"     => 0,
               "published"    => 1,
               "checked_out"  => 0
         );
         $nullcat = ArrayHelper::toObject($nullcat);
   
	      $sortedrow = array();

         foreach ( $rows AS $cat )
         {
            $sortedrow[$cat->id] = $cat;
         }

         $sortedrow[0] = $nullcat;

         return $sortedrow;
      }
   }

	 /**
    * Load a track(s) list in #__jtg_files according to incoming parameters
    *
    * @param   string  $order   ordering of the list
    * @param   string  $limit   SQL limit statement
    * @param   string  $where   SQL Where filter
    * @param   string  $access  Track acces level
    *
    * @return Loadobjeclist() track lists extracted from #__jtg_files
    *
    * Used for most popular tracks etc in map view 
    */
   public function getTracksData($order, $limit, $where = "",$access = null)
   {
      if ( $where != "" )
      {
         $where = " AND ( " . $where . " )";
      }

      /*
       * $access 0 1 or 2
       * a.access = track access level
       * 0 for everybody
       * 1 for registered users
       * 2 for access limited to author (private)
       * 9 for administrators
       */
      if ( $access !== null )
      {
         $where .= " AND a.access <= " . $access;
      }

      $db = $this->getDbo();
      $user = Factory::getUser();
      $uid = $user->id;
      $query = "SELECT a.*, b.title AS cat FROM #__jtg_files AS a"
      . "\n LEFT JOIN #__jtg_cats AS b"
      . "\n ON a.catid=b.id WHERE (a.published = 1 OR a.uid='$uid') " . $where
      . "\n" . $order
      . "\n" . $limit;
      $db->setQuery($query);
      $rows = $db->loadObjectList();
		return $rows;
   }
}
