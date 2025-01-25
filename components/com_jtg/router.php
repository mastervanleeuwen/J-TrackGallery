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

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\Router\RouterBase;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;

class jtgRouter extends RouterBase
{
	var $lookup = array();
	public function preprocess($query)
	{
		$active = $this->menu->getActive();

		if (!isset($query['view'])) 
		{
			return $query;
		}

		/**
		 * If the active item id is not the same as the supplied item id or we have a supplied item id and no active
		 * menu item then we just use the supplied menu item and continue
		 */
		if (isset($query['Itemid']) && ($active === null || $query['Itemid'] != $active->id)) {
			return $query;
		}

		// Get query language
		$language = isset($query['lang']) ? $query['lang'] : '*';
		// Set the language to the current one when multilang is enabled and item is tagged to ALL
		if (Multilanguage::isEnabled() && $language === '*') {
			$language = $this->app->get('language');
		}
		if (!isset($this->lookup[$language])) {
			$this->buildLookup($language);
		}

		// Check if the active menu item matches the requested query
		if ($active !== null && isset($query['Itemid'])) {
			// Check if active->query and supplied query are the same
			$match = true;
			// If the menu item is a jtg view; don't change menu item
			if ($active->query['view'] === 'jtg') return $query;

			foreach ($active->query as $k => $v) {
				if (isset($query[$k]) && $v !== $query[$k]) {
					// Compare again without alias
					if (\is_string($v) && $v == current(explode(':', $query[$k], 2))) {
						continue;
					}

					$match = false;
					break;
				}
			}

			if ($match) {
				// Just use the supplied menu item
				return $query;
			}
		}

		$view = isset($query['view'])?$query['view'] : '';
		$layout = isset($query['layout']) && $query['layout'] !== 'default' ? ':' . $query['layout'] : ':';
		$id = isset($query['id'])?':'.$query['id']:'';
		if (isset($query['cat'])) $id = ':'.$query['cat'];
		$key = $view.$layout.$id;
		$itemid = false;
		if (isset($this->lookup[$language][$key])) $itemid = $this->lookup[$language][$key];
		else if (isset($this->lookup[$language]['jtg:'])) // Fall back to a jtg view
			$itemid = $this->lookup[$language]['jtg:'];
		if ($itemid) {
			$query['Itemid'] = $itemid;
			return $query;
		}

		// Check if the active menuitem matches the requested language
		if ( $active && 
				($language === '*' || \in_array($active->language, array('*', $language)) || !Multilanguage::isEnabled()))
		{
			$query['Itemid'] = $active->id;

			return $query;
		}

		// If not found, return language specific home link
		$default = $this->menu->getDefault($language);

		if (!empty($default->id)) {
			$query['Itemid'] = $default->id;
		}
		return $query;
	}

	// Build lookup table for menu items
	// code based on Joomla core code in MenuRules
	protected function buildLookup($language = '*')
	{
		// Prepare the reverse lookup array.
		if (!isset($this->lookup[$language])) {
			$this->lookup[$language] = array();

			$component  = ComponentHelper::getComponent('com_jtg');
			$views = array('jtg','files','user','cats');

			$attributes = array('component_id');
			$values     = array((int) $component->id);

			$attributes[] = 'language';
			$values[]     = array($language, '*');

			$items = $this->menu->getItems($attributes, $values);

			foreach ($items as $item) {
				$view = '';
				if (isset($item->query['view'])) $view = $item->query['view'];

				$layout = ':';
				if (isset($item->query['layout'])) {
					$layout = ':' . $item->query['layout'];
				}

				$id = '';
				if (isset($item->query['id'])) {
					$id = ':' . $item->query['id'];
				}

				if (isset($item->query['cat'])) { // TODO: check whether we use catid 
					$id = ':' . $item->query['cat'];
				}
				if (!isset($this->lookup[$language][$view.$layout.$id]))
					$this->lookup[$language][$view.$layout.$id] = $item->id;
				else {  // Another menu item has the same view; which link has fewer flags
					$item2 = $this->menu->getItem($this->lookup[$language][$view.$layout.$id]);
					if (count($item->query) < count($item2->query) &&
							($item->language !== '*' || $item2->language === '*'))
						$this->lookup[$language][$view.$layout.$id] = $item->id;
				}
			}
		}
	}

	/**
	 * Function to convert a system URL to a SEF URL
	 *
	 * @param   array  &$query  segmented URL
	 *
	 * @return segmented URL (array)
	 */
	public function build(&$query)
	{
		$segments = array();
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		// TODO: check remove this?
		if (empty($query['Itemid']))
		{
			$menuItem = $menu->getActive();
		}
		else
		{
			$menuItem = $menu->getItem($query['Itemid']);
		}
		// $menuid = $menuItem->id;

		$view = '';
		if (isset($query['view']))
		{
			$view = $query['view'];
			$segments[] = $view;
			unset($query['view']);
		}

		$layout='';
		if (isset($query['layout']))
		{
			if ($query['layout'] !== 'default')
			{
				$layout = $query['layout'];
				$segments[] = $layout;
			}
			unset($query['layout']);
		}

		if (isset($query['controller']))
		{
			$segments[] = $query['controller'];
			unset($query['controller']);
		}

		$task='';
		if (isset($query['task']))
		{
			$task=$query['task'];
			$segments[] = $query['task'];
			unset($query['task']);
		}

		if ($view == 'track' && empty($task) && isset($query['id']))
		{
			if (strlen($layout)==0)
			{
				$segments[] = $this->getAliasFromId($query['id']);
			}
			else
			{
				$segments[] = $query['id'];
			}
			unset($query['id']);
		}
		else if (($view == 'cat' || $task == 'delete' || $task == 'vote') && isset($query['id']))
		{
			$segments[] = $query['id'];
			unset($query['id']);
		}

		return $segments;
	}

	/**
	 * function_description
	 *
	 * @param   array  $segments  segmented URL
	 *
	 * @return return_description
	 */
	private function jtgParseRouteFile(&$segments)
	{
		array_shift($segments);
		$layout = $segments[0];
		array_shift($segments);
		switch ($layout)
		{
			case 'file': // backward compatibility for files/file/<id>
				$vars['view'] = 'track';
				$vars['layout'] = 'default';
				$vars['id'] = $segments[0];
				break;

			case 'default':
				$vars['view'] = 'track';
				$vars['layout'] = $layout;
				$vars['id'] = $segments[0];
				break;

			case 'form':
				$vars['view'] = 'track';
				$vars['layout'] = $layout;
				$vars['id'] = $segments[0];
				break;

			case 'delete':
				$vars['controller'] = 'track';
				$vars['task'] = 'delete';
				$vars['id'] = $segments[0];
				break;

			case 'vote':
				$vars['controller'] = 'track'; 
				$vars['task'] = 'vote';
				$vars['id'] = $segments[0];

		}

		if (!isset($vars))
		{
			return false;
		}

		array_shift($segments);
		return $vars;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $segments  param_description
	 *
	 * @return return_description
	 */
	private function jtgParseRouteCategory(&$segments)
	{
		switch ($segments[0])
		{
			case 'files':
				$vars['view'] = 'files';
				$vars['layout'] = 'list';
				array_shift($segments);
				break;
			case 'cats':
				$vars['view'] = 'cats';
				$vars['layout'] = 'default';
				array_shift($segments);
				break;
			case 'cat':
				$vars['view'] = 'cat';
				break;
			case 'tag':
				$vars['view'] = 'tag';
				break;
			case 'track':
			case 'file':
				$vars['view'] = 'track';
				break;
			case 'jtg':
				$vars['view'] = 'jtg';
				break;
			case 'user':
				$vars['view'] = 'user';
				break;
		}

		if (!isset($vars))
		{
			return false;
		}
		array_shift($segments);

		return $vars;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $segments  param_description
	 *
	 * @return return_description
	 */
	private function jtgParseRouteSubCategory(&$segments)
	{
		$vars['view'] = $segments[0];
		$vars['layout'] = $segments[1];
		// TODO: could remove the case statements? keep files/map?
		$view = $segments[0];
		array_shift($segments);
		switch ($view)
		{
			case 'files':
				switch ($segments[0])
				{
					case 'form':
						$vars['view'] = 'track';
						$vars['layout'] = 'form';
						break;

					case 'user':
						$vars['view'] = 'user';
						break;

					case 'map':
						$vars['view'] = 'jtg';
						$vars['layout'] = 'map';
						break;
				}
				array_shift($segments);
				break;

			case 'jtg':
				switch ($segments[0])
				{
					case 'geo':
						$vars['view'] = 'jtg';
						$vars['layout'] = 'geo';
						array_shift($segments);
						break;
					case 'map':
						array_shift($segments);
				}
				break;

			case 'track':
				if ($segments[0] == 'form')
				{
					$vars['view'] = 'track';
					$vars['layout'] = 'form';
					$vars['id'] = null; // Unset id in case some other component has set it
				}
				else
				{
					$vars['view'] = 'track';
					$vars['layout'] = 'default';
					if (is_numeric($segments[0]))
					{
						$vars['id'] = $segments[0];
					}
					else
					{
						$vars['id'] = $this->getIdFromAlias($segments[0]);
						if (is_null($vars['id'])) return false;
					}
				}
				array_shift($segments);
				break;

			case 'cat':
			case 'tag':
				$vars['view'] = $view;
				$vars['layout'] = 'default';
				$vars['id'] = $segments[0];
				array_shift($segments);
				break;

			default:
				$vars['controller']=$view;
				$vars['task']=$segments[0];
				array_shift($segments);
		}

		if (!isset($vars))
		{
			return false;
		}

		return $vars;
	}

	/**
	 * Function to convert a SEF URL back to a system URL
	 *
	 * @param   array  $segments  segmented URL
	 *
	 * @return return_description
	 */
	public function parse(&$segments)
	{
		$vars = array();

		// Get the active menu item
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$item = $menu->getActive();

		// Count route segments
		$count = count($segments);

		if ( $count == 1 )
		{
			$vars = $this->jtgParseRouteCategory($segments);
		}
		elseif ( $count == 2 )
		{
			if ( isset( $segments[1] )
					AND ( $segments[1] == "default" )
					OR  ( ( $segments[0] == "files" ) AND ( $segments[1] == "list" ) ))
			{
				$vars = $this->jtgParseRouteCategory($segments);
				array_shift($segments);
			}
			else
			{
				$vars = $this->jtgParseRouteSubCategory($segments);
			}
		}
		else
		{
			switch ($segments[0])
			{
				case 'files': // kept for backward compatibility
				case 'track':
					$vars = $this->jtgParseRouteFile($segments);
					break;
			}
		}

		if ( ( $vars === false ) OR ( count($vars) == 0 ) )
		{
			$vars['view'] = 'files';
			$vars['layout'] = 'list';

			return $vars;
		}

		return $vars;
	}

	/**
	 * Helper function for generating the URL to a Category page
	 * This is needed for the Tags functionality
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			$link = 'index.php?option=com_jtg&view=cat&id=' . $id;

			if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
			}
		}

		return $link;
	}

	/**
    * Get track alias from Id number
    *
    * @return string alias
   */
   function getAliasFromId($id)
   {
		//$db = Factory::getContainer()->get('DatabaseDriver');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('alias');
		$query->from($db->quoteName('#__jtg_files'));
		$query->where($db->quoteName('id') . ' = '. $db->quote($id));
		$db->setQuery($query);
		$alias = $db->loadResult();
		if (empty($alias)) $alias = $id;
		return $alias;
	}

   /**
    * Get track Id number from alias
    *
    * @return int id
   */
   function getIdFromAlias($alias)
	{
		//$db = Factory::getContainer()->get('DatabaseDriver');
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id');
		$query->from($db->quoteName('#__jtg_files'));
		$query->where($db->quoteName('alias') . ' = '. $db->quote($alias));
		$db->setQuery($query);
		return $db->loadResult();
	}
}
?>
