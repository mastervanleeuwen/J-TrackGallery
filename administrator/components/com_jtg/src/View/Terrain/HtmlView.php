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

namespace Jtg\Component\Jtg\Administrator\View\Terrain;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

use Jtg\Component\Jtg\Administrator\View\JtgView;

/**
 * JtgViewTerrain class for the jtg component
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
		Factory::getLanguage()->load('com_jtg');
		Factory::getLanguage()->load('com_jtg_common', JPATH_SITE);
		// Com_jtg_additional language files are in /images/jtrackgallery/language folder
		Factory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery', 'en-GB', true);
		Factory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery',    null, true);

		$option = Factory::getApplication()->input->get('option');

		if ($this->getLayout() == 'form')
		{
			$this->_displayForm($tpl);

			return;
		}

		$model = $this->getModel();
		$rows = $this->get('Data');
		$total = $this->get('Total');
		$pagination = $this->get('Pagination');

		if (!isset($lists))
		{
			$lists = false;
		}

		$this->lists = $lists;
		$this->rows = $rows;
		$this->pagination = $pagination;

		parent::display($tpl);
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
		$cid = Factory::getApplication()->input->get('cid', array(), 'array');

		if (count($cid) != 0)
		{
			$id = $cid[0];
			$terrain = $model->getData($id);
			$terrain = $terrain[0];
			$published = $terrain->published;
		}
		else
		{
			$id = 0;
			$terrain = $model->getData();
			$published = 1;
		}

		$lists['block'] = HTMLHelper::_('select.booleanlist', 'published', 'class="inputbox" size="1"', $published);
		$this->id = $id;
		$this->lists = $lists;
		$this->terrain = $terrain;
		parent::display($tpl);
	}
}
