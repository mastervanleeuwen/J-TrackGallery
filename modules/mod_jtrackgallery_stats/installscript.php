<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;

return new class () implements InstallerScriptInterface {

	private string $minimumJoomla = '4.4.0';
	private string $minimumPhp    = '7.4.0';

	public function install(InstallerAdapter $adapter): bool
	{
		return true;
	}

	public function update(InstallerAdapter $adapter): bool
	{
		return true;
	}

	public function uninstall(InstallerAdapter $adapter): bool
	{
		return true;
	}

	public function preflight(string $type, InstallerAdapter $adapter): bool
	{
		if (version_compare(PHP_VERSION, $this->minimumPhp, '<')) {
			Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_PHP'), $this->minimumPhp), 'error');
			return false;
		}

		if (version_compare(JVERSION, $this->minimumJoomla, '<')) {
			Factory::getApplication()->enqueueMessage(sprintf(Text::_('JLIB_INSTALLER_MINIMUM_JOOMLA'), $this->minimumJoomla), 'error');
			return false;
		}

		if (File::exists(JPATH_BASE . '/modules/mod_jtrackgallery_stats /mod_jtrackgallery_stats .php'))
		{
			File::delete(JPATH_BASE . '/modules/mod_jtrackgallery_stats /mod_jtrackgallery_stats .php');
			File::delete(JPATH_BASE . '/modules/mod_jtrackgallery_stats /helper.php');
		}
		return true;
	}

	public function postflight(string $type, InstallerAdapter $adapter): bool
	{
		return true;
	}
};
