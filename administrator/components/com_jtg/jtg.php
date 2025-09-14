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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/*
 * Define constants for all pages
*/

define('COM_JTG_DIR', 'images/jtrackgallery/');
define('COM_JTG_BASE', JPATH_ROOT . '/' . COM_JTG_DIR);
define('COM_JTG_BASEURL', JUri::root() . '/' . COM_JTG_DIR);

jimport('joomla.filesystem.file');

// Load english language file for 'com_jtg' component then override with current language file
Factory::getLanguage()->load('com_jtg');
Factory::getLanguage()->load('com_jtg_common', JPATH_SITE);

// Com_jtg_additional language files are in /images/jtrackgallery/language folder
Factory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery', 'en-GB', true);
Factory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery',    null, true);

$contr = JPATH_COMPONENT . '/controllers/install.php';
$model = JPATH_COMPONENT . '/models/install.php';

// Require the base controller
require_once JPATH_COMPONENT_SITE . '/helpers/helper.php';
require_once JPATH_COMPONENT_SITE . '/helpers/maphelper.php';
require_once JPATH_COMPONENT . '/controller.php';

// Load the GpsDataClass
JLoader::import('components.com_jtg.helpers.gpsClass', JPATH_SITE, 'gpsClass');

// Initialize the controller
$input = Factory::getApplication()->input;
if ($controller = $input->getWord('controller'))
{
	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';
	$getCmdTask = $input->get('task');

	if (file_exists($path))
	{
		require_once $path;
	}
	else
	{
		$controller = '';
	}
}
else
{
	$getCmdTask = "info";
}

$classname = 'JtgController' . $controller;
$controller = new $classname;
$controller->execute($getCmdTask);

// Access check: is this user allowed to access the backend of J!TrackGallery?
if (!Factory::getUser()->authorise('core.manage', 'com_jtg'))
{
	return Factory::getApplication()->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'),'warning');
}

// Redirect if set by the controller
$controller->redirect();
