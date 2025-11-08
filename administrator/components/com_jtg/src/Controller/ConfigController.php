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
 * Controller Class Configuration
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class ConfigController extends BaseController
{
	/**
	 * View method for JTG
	 *
	 * This function override joomla.application.component.controller
	 * View Cache not yet implemented in JTrackGallery
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		Factory::getLanguage()->load('com_jtg');
		Factory::getLanguage()->load('com_jtg_common', JPATH_SITE);
		parent::display();
	}

	/**
	 * function_description
	 *
	 * @uses JtgModelConfigat::saveConfig
	 * @return  redirect
	 */
	function saveconfig()
	{
		// 	check the token
		Session::checkToken() or die( 'JINVALID_TOKEN' );
		$model = $this->getModel('config');
		$error = $model->saveConfig();

		if ($error !== true)
		{
			Factory::getApplication()->enqueueMessage($error, 'Warning');
		}

		$link = Route::_("index.php?option=com_jtg&task=config&controller=config", false);
		$this->setRedirect($link, Text::_('COM_JTG_CONFIG_SAVED'));
	}
}
