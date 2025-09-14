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

jimport('joomla.application.component.controller');

use Joomla\CMS\Factory;

/**
 * jtg Component Controller
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgController extends JControllerLegacy
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
	public function display ($cachable = false, $urlparams = false)
	{
		// Make sure we have a default view
		$input = Factory::getApplication()->input;
		if (! $input->get('view'))
		{
			$input->set('view', 'jtg');
		}

		if ($input->get('view') == 'jtg')
		{
			$view = $this->getView('jtg','html');
			$view->setModel($this->getModel('DPCalLocations')); // Add second model
		}

		// Update the hit count for the file
		if ($input->get('view') == 'track')
		{
			$model = $this->getModel('track');
			$model->hit();
		}

		parent::display();
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	public function displayimg ()
	{
		$app = Factory::getApplication();

		// By default, just display an image
		$document = Factory::getDocument();
		$doc = JDocument::getInstance('raw');

		// Swap the objects
		$document = $doc;
		$ok = null;
		$app->triggerEvent('onCaptcha_Display', array($ok));

		if (! $ok)
		{
			echo "<br/>Error displaying Captcha<br/>";
		}
	}
}
