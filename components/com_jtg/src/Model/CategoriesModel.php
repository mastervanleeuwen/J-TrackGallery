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

namespace Jtg\Component\Jtg\Site\Model;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * JtgModelCats class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class CategoriesModel extends ListModel
{
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function getCats()
	{
		$mainframe = Factory::getApplication();
		$db = Factory::getDbo();
		$query = "SELECT * FROM #__jtg_cats"
		. "\n WHERE published=1"
		. "\n ORDER BY ordering";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$limit = count($rows);
		$children = array();

		foreach ($rows as $v )
		{
			$v->title = Text::_($v->title);
			$pt 	= $v->parent_id;
			$list 	= @$children[$pt] ? $children[$pt] : array();

			// count tracks (could be done with a join statement?)
			$querycount = "SELECT COUNT(tracks.id) AS ntracks FROM #__jtg_files AS tracks WHERE tracks.published = 1 AND hidden = 0 AND tracks.catid LIKE '%".$v->id."%'";
		   $db->setQuery($querycount);
		   $v->ntracks = $db->loadResult();

			// store result
			array_push($list, $v);
			$children[$pt] = $list;
		}

		$list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children);
		$list = array_slice($list, 0, $limit);

		return $list;
	}
}
