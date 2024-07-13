<?php

/**
 * @component   J!Track Gallery (jtg) for Joomla! 2.5 and Joomla 3.x
 *
 * @package     Com_Jtg
 * @subpackage  Backend
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @author      Christian Knorr, InJooOSM  <christianknorr@users.sourceforge.net>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3 *
 * @link        http://jtrackgallery.net/
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');
use Joomla\CMS\Factory;

/**
 * class JtgControllerfor the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgController extends JControllerLegacy
{
	/**
	 * function_description
	 */
	public function __construct()
	{
		parent::__construct();
		$this->registerTask('savecat', 'savecat');
	}

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
		require_once JPATH_COMPONENT . '/helpers/jtg.php';

		// JtgHelper::updateReset();

		// Load the submenu.
		JtgHelper::addSubmenu($this->getTask());

		$input = Factory::getApplication()->input;
		switch ($this->getTask())
		{
			default:
				$input->set('view',	'default');
				break;

			case 'maps':
				$input->set('view',	'maps');
				$input->set('layout',	'default');
				break;

			case 'newmap':
			case 'editmap':
				$input->set('view',	'maps');
				$input->set('layout',	'form');
				break;

			case 'config':
				$input->set('view',	'config');
				$input->set('layout',	'default');
				break;

			case 'info':
				$input->set('view',	'info');
				$input->set('layout',	'default');
				break;

			case 'cats':
				$input->set('view',	'cats');
				$input->set('layout',	'default');
				break;

			case 'newcat':
				$input->set('view',	'cats');
				$input->set('layout',	'form');
				break;

			case 'editcat':
				$input->set('view',	'cats');
				$input->set('layout',	'editform');
				break;

			case 'managecatpics':
				$input->set('view',	'cats');
				$input->set('layout',	'managecatpics');
				$input->set('task',	'default');
				break;

			case 'newcatpic':
				$input->set('view',	'cats');
				$input->set('layout',	'managecatpicsform');
				$input->set('task',	'new');
				break;

			case 'editcatpic':
				$input->set('view',	'cats');
				$input->set('layout',	'managecatpicsform');
				$input->set('task',	'edit');
				break;

			case 'files':
			case 'toshow':
			case 'tohide':
			case 'files.batch':
				$input->set('view',	'files');
				$input->set('layout',	'default');
				break;

			case 'upload':
				$input->set('view',	'files');
				$input->set('layout',	'upload');
				break;

			case 'newfiles':
				$input->set('view',	'files');
				$input->set('layout',	'import');
				break;

			case 'newfile':
			case 'editfile':
			case 'updateGeneratedValues':
				$input->set('view',	'files');
				$input->set('layout',	'form');
				break;

			case 'terrain':
				$input->set('view',	'terrain');
				$input->set('layout',	'default');
				break;

			case 'newterrain':
			case 'editterrain':
				$input->set('view',	'terrain');
				$input->set('layout',	'form');
				break;

			case 'comments':
				$input->set('view',	'comments');
				$input->set('layout',	'default');
				break;

			case 'editComment':
				$input->set('view',	'comments');
				$input->set('layout',	'form');
				break;

			case 'importjgt':
				$input->set('view',	'files');
				$input->set('layout',	'importjgt');
				break;
			case 'element':
				$input->set('view',	'files');
				$input->set('layout',	'element');
				break;

			case 'translations':
				$input->set('view',	'translations');
				$input->set('layout',	'default');
				break;
		}

		parent::display();
	}
}
