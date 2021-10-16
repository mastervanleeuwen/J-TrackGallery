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

/**
 * Table class
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class TableJTG_Maps extends JTable
{
	var $id				= null;

	var $name			= null;

	var $order			= null;

	var $param			= null;

	var $published		= null;

	var $type			= null;

	var $apikey			= null;

	var $checked_out	= null;

	/**
	 * function_description
	 *
	 * @param   object  &$db  database
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__jtg_maps', 'id', $db);
	}

	/**
	 * function_description
	 *
	 * @param   array   $array   param_description
	 * @param   string  $ignore  param_description
	 *
	 * @return object
	 */
	function bind($array, $ignore = '')
	{
		if (key_exists('params', $array) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
