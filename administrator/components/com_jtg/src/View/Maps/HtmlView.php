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

namespace Jtg\Component\Jtg\Administrator\View\Maps;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\String\StringHelper;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;
use Jtg\Component\Jtg\Administrator\View\JtgView;

/**
 * JtgViewMaps class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class HtmlView extends JtgView 
{
	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$option = Factory::getApplication()->input->get('option');
		
		Factory::getLanguage()->load('com_jtg');
		Factory::getLanguage()->load('com_jtg_common', JPATH_SITE);
	
		if ($this->getLayout() == 'form')
		{
			$this->_displayForm($tpl);
		}

		$config = JtgHelper::getConfig();
		$model = $this->getModel();
		$total = $model->getTotal();
		$maps = $model->getMaps();

		$lists['block']	= HTMLHelper::_('select.booleanlist', 'publish', null, 1);

		$filter_order		= $app->getUserStateFromRequest(
				$option . "filter_order",
				'filter_order',
				'ordering',
				'cmd');
		$filter_order_Dir	= $app->getUserStateFromRequest(
				$option . "filter_order_Dir",
				'filter_order_Dir',
				'',
				'word');
		$lists['order']		= $filter_order;
		$lists['order_Dir']	= $filter_order_Dir;

		$search = $app->getUserStateFromRequest(
				$option . "search",
				'search',
				'',
				'string');
		$search = StringHelper::strtolower($search);
		$lists['search'] = $search;
		$state = $search;

		$pagination = $this->get('Pagination');
		$this->pagination = $pagination;
		$this->state = $state;
		$this->config = $config;
		$this->total = $total;
		$this->maps = $maps;
		$this->lists = $lists;
		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   object   $map    param_description
	 * @param   integer  $count  param_description
	 *
	 * @return string
	 */
	function buildEditKlicks($map, $count, $id)
	{
		if (version_compare(JVERSION, '4.0','lt'))
		{
			return "<a href=\"javascript:void(0);\" onclick=\"javascript:return Joomla.listItemTask('cb" . $count
			. "','editmap')\">" . $map . "</a>";
		}
		else
		{
			return "<a href=\"".Route::_("index.php?option=com_jtg&task=editmap&controller=maps&id=$id")."\">". $map."</a>";
		}
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	protected function _displayForm($tpl)
	{
		$model = $this->getModel();
		$id = false;

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		if (count($cid))
		{
			$id = $cid[0];
		}
		else
		{
			$id = Factory::getApplication()->input->getInt('id');
		}
	
		if ($id)
		{
			$map = $model->getMap($id);
			$published = $map->published;
			$this->map = $map;
		}
		else
		{
			$published = 0;
		}

		$list['published']	= HTMLHelper::_('select.booleanlist', 'publish', null, $published);
		$this->list = $list;

		// Parent::display($tpl);
	}
}
