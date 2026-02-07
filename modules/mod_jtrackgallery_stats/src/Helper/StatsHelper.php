<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Module JTrackGalleryLatest
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        http://jtrackgallery.net/
 *
 */

namespace Jtg\Module\JTrackGalleryStats\Site\Helper;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

/**
 * ModjtrackgalleryStatsHelper class for Module JTrackGalleryStats
 *
 * @package     Comjtg
 * @subpackage  Module JTrackGalleryStats
 * @since       0.8
 */
class StatsHelper
{
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function countCats()
	{
		$db = $db = Factory::getContainer()->get('DatabaseDriver');
		$query = "SELECT COUNT(*) FROM #__jtg_cats WHERE published='1'";
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function countTracks()
	{
		$db = $db = Factory::getContainer()->get('DatabaseDriver');
		$query = "SELECT COUNT(*) FROM #__jtg_files WHERE published='1'";
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function countDistance()
	{
		$db = $db = Factory::getContainer()->get('DatabaseDriver');

		$query = "SELECT SUM(distance) FROM #__jtg_files WHERE published='1'";
		$db->setQuery($query);

		// In km
		$result = (int) $db->loadResult();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function countAscent()
	{
		$db = $db = Factory::getContainer()->get('DatabaseDriver');
		$query = "SELECT SUM(ele_asc) FROM #__jtg_files WHERE published='1'";
		$db->setQuery($query);

		// In km
		$result = ($db->loadResult() / 1000);

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function countDescent()
	{
		$db = $db = Factory::getContainer()->get('DatabaseDriver');
		$query = "SELECT SUM(ele_desc) FROM #__jtg_files WHERE published='1'";
		$db->setQuery($query);

		// In km
		$result = ($db->loadResult() / 1000);

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function countViews()
	{
		$db = $db = Factory::getContainer()->get('DatabaseDriver');
		$query = "SELECT SUM(hits) FROM #__jtg_files WHERE published='1'";
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function countVotes()
	{
		$db = $db = Factory::getContainer()->get('DatabaseDriver');
		$query = "SELECT COUNT(*) FROM #__jtg_votes";
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}
}
