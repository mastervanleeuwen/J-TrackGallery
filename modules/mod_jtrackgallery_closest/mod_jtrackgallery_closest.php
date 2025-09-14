<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Module JTrackGalleryLatest
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        http://jtrackgallery.net/
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;

require_once dirname(__FILE__) . '/helper.php';
$helper = new ModjtrackgalleryClosestHelper;

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


ModuleHelper::getLayoutPath('mod_jtrackgallery_closest', $params->get('layout', 'default'));
