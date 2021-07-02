<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @copyright   2021 J!TrackGallery team
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        https://mastervanleeuwen.github.io/J-TrackGallery/
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
/**
 * JtgModeljtg class for the jtg component
 *
 */

class JtgModelDPCalLocations extends JModelList
{
	protected function getListQuery() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('loc.*')
		->from('#__dpcalendar_locations as loc')
		->where("loc.state='1'");
		// TODO: check whether user can view etc
		//->join('LEFT','#__users AS c ON a.uid=c.id');
		return $query;
	}
}
?>
