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

namespace Jtg\Component\Jtg\Administrator\Table;
// No direct access
defined('_JEXEC') or die('Restricted access');

// Include library dependencies
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\Registry\Registry;

/**
 * Table class
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class FilesTable extends Table implements TaggableTableInterface
{
	use TaggableTableTrait;

	var $id				= null;

	var $uid				= null;

	var $catid			= null;

	var $title			= null;

	var $alias			= null;

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
		if (version_compare(JVERSION,'4.0','ge')) {
			$this->typeAlias = 'com_jtg.file';
		}
		else {
			JObserverMapper::addObserverClassToClass('JTableObserverContenthistory', 'TableJTG_Files', array('typeAlias' => 'com_jtg.file'));
			JTableObserverTags::createObserver($this, array('typeAlias' => 'com_jtg.file'));
		}
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
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Check whether a given alias already exists in the table
	 *
	 * @param   string  $alias  alias to check for
	 * @param   integer $id     track id to ignore (current track id) 
	 *
	 * @return boolean
	 */
	function aliasExists($alias, $id = null)
	{
		$db = $this->getDbo();
		//or: $db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from($db->quoteName('#__jtg_files'));
		$query->where($db->quoteName('alias') . ' = '. $db->quote($alias));
		if (!is_null($id)) $query->andWhere($db->quoteName('id') . ' != '. $db->quote($id));
		$db->setQuery($query);
		return $db->loadResult() > 0;
	}

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function check()
	{
		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}

		$this->alias = OutputFilter::stringURLSafe($this->alias);
		if (empty($this->alias)) $this->alias = OutputFilter::stringURLSafe(date('Y-m-d H:i:s'));
		$cnt = 1;
		$alias_test = $this->alias;
		while ($this->aliasExists($this->alias, $this->id))
		{
			$this->alias = substr($alias_test,0,250).'-'.$cnt;
			$cnt++;
		}

		return true;
	}
}
