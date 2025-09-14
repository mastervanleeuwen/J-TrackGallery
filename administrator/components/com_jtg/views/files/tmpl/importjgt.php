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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('COM_JTG_ADD_FILES'), 'categories.png');
ToolbarHelper::back();
$model = $this->getModel();
$rows = $model->_fetchJPTfiles();

if ($rows == false)
{
	Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_ERROR_NOJGTFOUND'), 'Error');
}
else
{
	$i = 0;
	$importdone = false;

	foreach ( $rows AS $track )
	{
		if ($model->importFromJPT($track) == true)
		{
			$color = "green";
			$importdone = true;
		}
		else
		{
			$color = "red";
		}

		echo "<font color=\"" . $color . "\">" . $track["file"] . "</font><br />\n";
	}

	if ($importdone == true)
	{
		Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_IMPORT_DONE'));
	}
	else
	{
		Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_IMPORT_FAILURE'), 'Warning');
	}
}
