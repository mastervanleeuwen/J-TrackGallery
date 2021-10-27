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
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\Utilities\ArrayHelper;
/**
 * JtgModeljtg class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgModeljtg extends JModelList
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'search',
				'mindist',
				'maxdist',
				'trackcat',
				'tracklevel');
		}
		parent::__construct();
	}

   protected function getListQuery(){
      // TODO: add accesslevel logic, or remove completely? replace by per-track access using native Joomla! logic?

      $db = JFactory::getDBO();
      $query = $db->getQuery(true);
      $user = JFactory::getUser();
      $uid = $user->id;

      $query->select('a.*, c.name AS user')
      ->from('#__jtg_files as a')
      ->join('LEFT','#__users AS c ON a.uid=c.id');

      // Filter company
      $trackcat = $this->getState('filter.trackcat');
      if (!empty($trackcat)) {
         $query->where('a.catid LIKE '.$db->quote('%'.$trackcat.'%'));
      }
      else {
          $trackcat = JFactory::getApplication()->input->get('cat');
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
      if (JFactory::getApplication()->input->get('layout') == 'user') {
         $query->where("a.uid=$uid");
      }
      else {
         $query->where("(( a.published = '1' AND a.hidden = '0' ) OR ( a.uid=$uid))");
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

      //error_log($db->replacePrefix( (string) $query ));//debug

      return $query;
   }

	/**
	 * function_description
	 *
	 * @param   integer  $id  file id
	 *
	 * @return return_description
	 */
	function getFile($id)
	{
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__jtg_files WHERE id='" . $id . "'";

		$db->setQuery($query);
		$result = $db->loadObject();

		if (!$result)
		{
			return JTable::getInstance('jtg_files', 'table');
		}

		return $result;
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
	 */
	static public function getTracksData($order, $limit, $where = "",$access = null)
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

		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
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

	/**
	 * sort categories
	 *
	 * @param  boolean  $sort sort by id instead of title
	 * @param  integer $catid select only this category 
	 *
	 * @return sorted rows
	 */
	static public function getCatsData($sort=false, $catid=null)
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

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
					"id"			=> 0,
					"parent"		=> 0,
					"title"			=> "<label title=\"" . JText::_('COM_JTG_CAT_NONE') . "\">-</label>",
					"description"	=> null,
					"image"			=> null,
					"ordering"		=> 0,
					"published"		=> 1,
					"checked_out"	=> 0
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
	 * function_description
	 *
	 * @param   unknown_type  $sort  param_description
	 *
	 * @return return_description
	 */
	static public function getTerrainData($sort=false)
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__jtg_terrains"
		. "\n ORDER BY title ASC";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if ( $sort === false )
		{
			return $rows;
		}
		else
		{
			$nullter = array(
					"id"			=> 0,
					"title"			=> "<label title=\"" . JText::_('COM_JTG_TERRAIN_NONE') . "\">-</label>",
					"ordering"		=> 0,
					"published"		=> 1,
					"checked_out"	=> 0
			);
			$nullter = (object) $nullter;
			$sortedrow = array();

			foreach ( $rows AS $ter )
			{
				$sortedrow[$ter->id] = $ter;
			}

			$sortedrow[0] = $nullter;

			return $sortedrow;
		}
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	static public function getVotesData()
	{
		$mainframe = JFactory::getApplication();

		$db = JFactory::getDBO();

		$query = "SELECT trackid AS id ,rating FROM #__jtg_votes"
		. "\n ORDER BY trackid ASC";

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		return $rows;
	}
}
