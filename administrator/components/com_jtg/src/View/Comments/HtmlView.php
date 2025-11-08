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

namespace Jtg\Component\Jtg\Administrator\View\Comments;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\Utilities\ArrayHelper;

use Jtg\Component\Jtg\Administrator\View\JtgView;

/**
 * JtgViewComments class for the jtg component
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
		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		$model = $this->getModel();
		$comment = $model->getComment($cid);
		if (version_compare(JVERSION,'4.0','ge')) {
			$editor = Factory::getApplication()->getConfig()->get('editor');
		}
		else 
		{
			$editor = Factory::getConfig()->get('editor');
		}
		$editor = Editor::getInstance($editor);;

		$this->comment = $comment;
		$this->editor = $editor;

		parent::display($tpl);
	}
}
