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
$stats = new ModjtrackgalleryStatsHelper;

$moduleclass_sfx = $params->get('moduleclass_sfx');
$tcustom_enable = $params->get('tcustom_enable');
$tcustom = $params->get('tcustom');
$thead = $params->get('thead');
$tcat = $params->get('tcat');
$ttrack = $params->get('ttrack');
$tdis = $params->get('tdis');
$tasc = $params->get('tasc');
$tdec = $params->get('tdec');
$tview = $params->get('tview');
$tvote = $params->get('tvote');
$theado = $params->get('theado');
$tcato = $params->get('tcato');
$ttracko = $params->get('ttracko');
$tdiso = $params->get('tdiso');
$tasco = $params->get('tasco');
$tdeco = $params->get('tdeco');
$tviewo = $params->get('tviewo');
$tvoteo = $params->get('tvoteo');
$cats = $stats->countCats();
$tracks = $stats->countTracks();
$distance_km = $stats->countDistance();
$ascent = $stats->countAscent();
$descent = $stats->countDescent();
$views = $stats->countViews();
$votes = $stats->countVotes();
$distance_mi = $distance_km / 1.609344;
$decimalseparator = $params->get('decimalseparator');

// Round value and account for decimal separator
$distance_km = (string) round($distance_km, 1);
$distance_mi = (string) round($distance_mi, 1);

if ($params->get('unit') == "Kilometer" )
{
	$distance = $distance_km;
}
else
{
	$distance = $distance_mi;
}

$ascent = (string) round($ascent, 1);
$descent = (string) round($descent, 1);

if ($decimalseparator != '.')
{
	$distance = str_replace('.', $decimalseparator, $distance);
	$distance_km = str_replace('.', $decimalseparator, $distance_km);
	$distance_mi = str_replace('.', $decimalseparator, $distance_mi);
	$ascent = str_replace('.', $decimalseparator, $ascent);
	$descent = str_replace('.', $decimalseparator, $descent);
}

ModuleHelper::getLayoutPath('mod_jtrackgallery_stats', $params->get('layout', 'default'));
