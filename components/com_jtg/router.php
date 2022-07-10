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

/*
 * Function to convert a system URL to a SEF URL
*/
	/**
	 * Function to convert a system URL to a SEF URL
	 *
	 * @param   array  &$query  segmented URL
	 *
	 * @return segmented URL (array)
	 */
function jtgBuildRoute(&$query)
{
	$segments = array();
	$app = JFactory::getApplication();
	$menu = $app->getMenu();

	if (empty($query['itemId']))
	{
		$menuItem = $menu->getActive();
	}
	else
	{
		$menuItem = $menu->getItem($query['itemId']);
	}
	// $menuid = $menuItem->id;

	$view = '';
	if (isset($query['view']))
	{
		$view = $query['view'];
		$segments[] = $view;
		unset($query['view']);
	}

	if (isset($query['layout']))
	{
		$segments[] = $query['layout'];
		unset($query['layout']);
	}

	if (isset($query['controller']))
	{
		$segments[] = $query['controller'];
		unset($query['controller']);
	}

	if (isset($query['task']))
	{
		$segments[] = $query['task'];
		unset($query['task']);
	}

	if (($view == 'track' || $view == 'cat') && isset($query['id']))
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
function _jtgParseRouteFile(&$segments)
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
			array_shift($segments);
			$vars['id'] = $segments[0];
			break;

		case 'form':
			$vars['view'] = 'track';
			$vars['layout'] = 'form';
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
function _jtgParseRouteCategory(&$segments)
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
		case 'track':
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
function _jtgParseRouteSubCategory(&$segments)
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
				$vars['id'] = $segments[0];
			}
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
function jtgParseRoute(&$segments)
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
		$vars = _jtgParseRouteCategory($segments);
	}
	elseif ( $count == 2 )
	{
		if ( isset( $segments[1] )
			AND ( $segments[1] == "default" )
			OR  ( ( $segments[0] == "files" ) AND ( $segments[1] == "list" ) ))
		{
			$vars = _jtgParseRouteCategory($segments);
			array_shift($segments);
		}
		else
		{
			$vars = _jtgParseRouteSubCategory($segments);
		}
	}
	else
	{
		switch ($segments[0])
		{
			case 'files': // kept for backward compatibility
			case 'track':
				$vars = _jtgParseRouteFile($segments);
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
