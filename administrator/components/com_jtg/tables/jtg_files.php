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

// Include library dependencies
jimport('joomla.filter.input');
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;

/**
 * Table class
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class TableJTG_Files extends Table implements TaggableTableInterface
{
	use TaggableTableTrait;

	var $id				= null;

	var $uid			= null;

	var $catid			= null;

	var $title			= null;

	var $file			= null;

	var $terrain		= null;

	var $description	= null;

	var $published		= null;

	var $date			= null;

	var $hits			= null;

	var $checked_out	= null;

	var $start_n		= null;

	var $start_e		= null;

	var $distance		= null;

	var $ele_asc		= null;

	var $ele_desc		= null;

	var $level			= null;

	var $access			= null;

	var $istrack		= null;

	var $iswp			= null;

	var $isroute		= null;

	var $vote			= null;

	var $hidden			= null;

	/**
	 * function_description
	 *
	 * @param   object  &$db  database
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__jtg_files', 'id', $db);
		$this->typeAlias = 'com_jtg.file';
	}

	function getTypeAlias() { return 'com_jtg.file'; }

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

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function check()
	{
		jimport('joomla.filter.output');

		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$this->alias = JFilterOutput::stringURLSafe($this->alias);

		return true;
	}
}
