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

// Import Joomla! libraries
jimport('joomla.application.component.view');
use Joomla\String\StringHelper;

/**
 * JtgViewMaps class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgViewMaps extends JViewLegacy
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
		$mainframe = JFactory::getApplication();
		$option = JFactory::getApplication()->input->get('option');

		// 	$order = JFactory::getApplication()->input->get('order',true);

		if ($this->getLayout() == 'form')
		{
			$this->_displayForm($tpl);
		}

		jimport('joomla.filesystem.file');
		$config = JtgHelper::getConfig();
		$model = $this->getModel();
		$total = $model->getTotal();
		$maps = $model->getMaps();

		// $tmpl = $model->getTemplates();

		$lists['block']	= JHtml::_('select.booleanlist', 'publish', 'class="inputbox" size="1"', 1);

		// 	$order = JFactory::getApplication()->input->get('order', 'order', 'string' );

		$filter_order		= $mainframe->getUserStateFromRequest(
				$option . "filter_order",
				'filter_order',
				'ordering',
				'cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest(
				$option . "filter_order_Dir",
				'filter_order_Dir',
				'',
				'word');
		$lists['order']		= $filter_order;
		$lists['order_Dir']	= $filter_order_Dir;

		$search = $mainframe->getUserStateFromRequest(
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
		return "<a href=\"javascript:void(0);\" onclick=\"javascript:return Joomla.listItemTask('cb" . $count . "','editmap')\">" . $map . "</a>";
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
		$id = $this->_models["maps"]->_id;
		$map = $model->getMap($id);

		if ($map)
		{
			$published = $map->published;
			$this->map = $map;
		}
		else
		{
			$published = 0;
		}

		$list['published']	= JHtml::_('select.booleanlist', 'publish', 'class="inputbox" size="1"', $published);
		$this->list = $list;

		// Parent::display($tpl);
	}
}
