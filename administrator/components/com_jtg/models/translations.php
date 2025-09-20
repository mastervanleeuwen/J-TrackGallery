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

// Import Joomla! libraries
jimport('joomla.application.component.model');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;

/**
 * Model Class Terrain
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgModelTranslations extends BaseDatabaseModel
{
	/**
	 * function_description
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function saveLanguage()
	{
		jimport('joomla.filesystem.file');
		Session::checkToken() or die( 'JINVALID_TOKEN' );
		$languages = $this->getRawLanguages();
		$written = true;

		foreach ($languages as $lang)
		{
			$file = JPATH_SITE . '/images/jtrackgallery/language/' . $lang['tag'] . '/' . $lang['tag'] . '.com_jtg_additional.ini';
			$inhalt = Factory::getApplication()->input->get($lang['tag'], '', 'raw');
			$inhalt = str_replace('\"', '"', $inhalt);

			if (!File::write($file, $inhalt))
			{
				$written = false;
			}
		}

		return $written;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function getRawLanguages()
	{
		$languages = LanguageHelper::getKnownLanguages();

		return $languages;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function getLanguages()
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$languages = $this->getRawLanguages();
		$newlanguages = array();

		foreach ($languages as $lang)
		{
			$rows = 5;
			$newlanguages[$lang['tag']] = array();
			$newlanguages[$lang['tag']]['name'] = $lang['name'];
			$newlanguages[$lang['tag']]['tag'] = $lang['tag'];
			$path = JPATH_SITE . '/images/jtrackgallery/language/' . $lang['tag'] . '/';
			$file = $path . $lang['tag'] . ".com_jtg_additional.ini";
			$newlanguages[$lang['tag']]['file'] = $file;

			if (!Folder::exists($path))
			{
				Folder::create($path);
			}

			$written = false;

			if (!File::exists($file))
			{
				$buffer = '; These are additional translation strings added by users' . "\n"
				. '; They may be used in Front-end AND Back-end';
				$iswritable = JPath::getPermissions($path);
				$iswritable = $iswritable[1];

				if ($iswritable == "w" )
				{
					$written = File::write($file, $buffer);
				}
			}

			if (File::exists($file) or $written) // It should exist now
			{
				$iswritable = JPath::getPermissions($file);
				$iswritable = $iswritable[1];
				$content = file_get_contents($file);
				$text = explode("\n", $content);

				if ($iswritable == "w" )
				{
					$header_color = "green";
					$header_desc = Text::_('COM_JTG_WRITABLE');
				}
				else
				{
					$header_color = "red";
					$header_desc = Text::_('COM_JTG_UNWRITABLE');
				}
			}
			else
			{
				$header_color = "red";
				$header_desc = Text::_('COM_JTG_UNWRITABLE');
				$content = Text::_('COM_JTG_UNWRITABLE');
			}

			$newlanguages[$lang['tag']]['header'] = $lang['name'] . "<br /><font color=\"" . $header_color . "\"><small>(" . $header_desc . ")</small></font>";
			$size = substr_count($content, "\n");
			$rows = $size + $rows;
			$newlanguages[$lang['tag']]['rows'] = $rows;
			$newlanguages[$lang['tag']]['value'] = $content;
		}

		return $newlanguages;
	}
}
