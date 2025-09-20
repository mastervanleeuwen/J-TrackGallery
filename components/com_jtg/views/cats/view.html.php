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

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

use Jtg\Component\Jtg\Site\Helpers\LayoutHelper;

/**
 * HTML View class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgViewCats extends HtmlView
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
		$this->lh = LayoutHelper::navigation();
		$this->footer = LayoutHelper::footer();
		$pathway = $app->getPathway();
		$pathway->addItem(Text::_('COM_JTG_CATS'), '');
		$sitename = $app->getCfg('sitename');
		$document = Factory::getDocument();
		$document->setTitle(Text::_('COM_JTG_CATS') . " - " . $sitename);
		$model = $this->getModel();
		$this->cats = $model->getCats();

		parent::display($tpl);
	}
}
