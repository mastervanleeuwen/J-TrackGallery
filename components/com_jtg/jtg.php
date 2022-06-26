<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
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

global $jtg_microtime;
$jtg_microtime = microtime(true);

// Require the base controller
require_once JPATH_COMPONENT . '/controller.php';

// Require the base helper
require_once JPATH_COMPONENT . '/helpers/layout.php';
require_once JPATH_COMPONENT . '/helpers/helper.php';
JLoader::register('gpsCLass',
		'./components/com_jtg/helpers/'
);
JLoader::import('components.com_jtg.helpers.gpsClass', JPATH_SITE, 'gpsClass');

$mainframe = JFactory::getApplication();
$mainframe->getLanguage()->load('com_jtg', JPATH_SITE . '/components/com_jtg', 'en-GB', true);
$mainframe->getLanguage()->load('com_jtg', JPATH_SITE . '/components/com_jtg', null, true);
$mainframe->getLanguage()->load('com_jtg_common', JPATH_SITE . '/components/com_jtg', 'en-GB', true);
$mainframe->getLanguage()->load('com_jtg_common', JPATH_SITE . '/components/com_jtg', null, true);

// Com_jtg_additional language files are in /images/jtrackgallery/language
// folder
$mainframe->getLanguage()->load(
		'com_jtg_additional/', JPATH_SITE . '/images/jtrackgallery', 'en-GB',
		true
		);
$mainframe->getLanguage()->load(
		'com_jtg_additional/', JPATH_SITE . '/images/jtrackgallery', null, true
);
$cfg = JtgHelper::getConfig();

// Set the template
$tmpl = strlen($cfg->template) ? $cfg->template : 'default';

$document = JFactory::getDocument();
$document->addStyleSheet(
		JUri::base() . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_style.css'
);

// Override style with user templates
jimport('joomla.filesystem.file');
$template_css = 'templates/' . $mainframe->getTemplate() . '/css/jtg_style.css';

if (JFile::exists($template_css))
{
	// Override with site template
	$document->addStyleSheet($template_css);
}

// Initialize the controller
if ($controller = JFactory::getApplication()->input->getWord('controller'))
{
	$path = JPATH_COMPONENT . '/controllers/' . $controller . '.php';

	if (file_exists($path))
	{
		require_once $path;
	}
	else
	{
		$controller = '';
	}
}

$classname = 'JtgController' . ucfirst($controller);
$controller = new $classname;

// Register Extra tasks
$controller->registerTask('save', 'save');

$controller->execute(JFactory::getApplication()->input->get('task'));

// Redirect if set by the controller
$controller->redirect();

// En-/disable execution time
if ((JDEBUG) and (isset($jtg_microtime)))
{
	$seconds = (microtime(true) - $jtg_microtime);
	$milliseconds = ($seconds * 1000);
	$microseconds = ($seconds * 1000000);
	echo (int) round($microseconds, 0) . " μs<br />" . (int) round($milliseconds, 0) . " ms<br />" . (int) round($seconds, 0) . " s";
}
