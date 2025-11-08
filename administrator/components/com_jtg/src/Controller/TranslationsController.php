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

namespace Jtg\Component\Jtg\Administrator\Controller;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * JtgControllertranslations class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class TranslationsController extends BaseController
{
	/**
	 * function_description
	 *
	 * @param   boolean  $cachable   true if display is cachable
	 * @param   string   $urlparams  URL
	 *
	 * @return return_description
	 */
	public function display($cachable = false, $urlparams = [])
	{
		parent::display();
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function saveLanguages()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('translations');
		$success = $model->saveLanguage();

		// Redirect to translations
		$link = Route::_("index.php?option=com_jtg&task=translations&controller=translations", false);

		if ($success)
		{
			$this->setRedirect($link, Text::_('COM_JTG_TRANSLATIONS_SAVED'));
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_TRANSLATIONS_NOTSAVED'), 'Warning');
			$this->setRedirect($link);
		}
	}
}
