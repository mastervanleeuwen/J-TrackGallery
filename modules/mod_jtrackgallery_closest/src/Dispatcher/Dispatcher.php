<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Module JTrackGalleryClosest
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        http://jtrackgallery.net/
 *
 */

namespace Jtg\Module\JTrackGalleryClosest\Site\Dispatcher;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\DispatcherInterface;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

use Jtg\Module\JTrackGalleryClosest\Site\Helper\ClosestHelper;

class Dispatcher implements DispatcherInterface
{
	protected $module;

	protected $app;

	public function __construct(\stdClass $module, CMSApplicationInterface $app, Input $input)
	{
		$this->module = $module;
		$this->app = $app;
	}

	public function dispatch()
	{
		$language = $this->app->getLanguage();
		$language->load('mod_jtrackgallery_closest', JPATH_BASE . '/modules/mod_jtrackgallery_closest');

		$helper = new ClosestHelper;
		$params = new Registry($this->module->params);
		
		$width = $params->get('width');
		$height = $params->get('height');
		$zoom = $params->get('zoom');
		$map = $params->get('map');
		$color = $params->get('color');
		$count = $params->get('count');
		$lat = $params->get('lat');
		$lon = $params->get('lon');
		$max_dist = $params->get('max_dist');

		$tracks = $helper->getTracks($lon,$lat,$max_dist,$count);
		$apikey = $params->get('apikey');

		if ($params->get('unit') == "Kilometer" )
		{
			$unit = 1;
		}
		else
		{
			$unit = 1 / 1.609344;
		}

		require ModuleHelper::getLayoutPath('mod_jtrackgallery_closest', $params->get('layout', 'default'));
	}
};