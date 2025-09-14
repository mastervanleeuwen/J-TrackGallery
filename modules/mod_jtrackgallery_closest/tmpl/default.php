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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$tcustom_enable = $params->get('tcustom_enable');

// Load english language file for 'com_jtg' component then override with current language file
Factory::getLanguage()->load('com_jtg_common', JPATH_SITE,	null, true);

// Com_jtg_additional language files are in /images/jtrackgallery/language folder
Factory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery',	null, true);

$max_dist = $params->get('max_dist');
$earthRadius = 6378.137;
foreach ($tracks as $track)
{
	$distance_km = (string) round($track->distance, 1);
	$distance_mi_or_km = (string) round($unit * $track->distance, 1);
	$distance_mi = (string) round($track->distance / 1.609344, 1);
	$decimalseparator = $params->get('decimalseparator');

	if ($max_dist > 0 && $track->disttopoint*$earthRadius > $max_dist) continue;
	if ($decimalseparator != '.')
	{
		$distance_km = str_replace('.', $decimalseparator, $distance_km);
		$distance_mi_or_km = str_replace('.', $decimalseparator, $distance_mi_or_km);
		$distance_mi = str_replace('.', $decimalseparator, $distance_mi);
	}

	if ($tcustom_enable)
	{
		$tcustom = $params->get('tcustom');
		$tcustom = str_replace('$cats', ($track->cat? Text::_($track->cat): '?'), $tcustom);
		$tcustom = str_replace('$distance_km', $distance_km, $tcustom);
		$tcustom = str_replace('$distance_mi', $distance_mi, $tcustom);
	}

	if ($params->get('style') == 0)
	{
		$link = JRoute::_('index.php?option=com_jtg&view=track&id=' . $track->id);
		echo '<div align="center" style="margin-bottom:20px;">';
		if ($map != 'none') { 
			echo '<img src="https://maps.googleapis.com/maps/api/staticmap?center=' . $track->start_n . ',' .
			$track->start_e . '&zoom=' . $zoom . '&size=' . $width . 'x' . $height .
			'&maptype=' . 	$map . '&markers=color:' . $color . '|' . $track->start_n . ',' .
			$track->start_e . ($apikey? '&key=' . $apikey: '') . '&sensor=false" >';
		}
		echo '<div align="center"><a href="' . $link . '">' . $track->title . '</a></div>';

		if ($tcustom_enable)
		{
			echo $tcustom;
		}
		else
		{
			if ( ($params->get('cats') != 0 ) and ($track->cat) )
			{
				echo '<div align="center">' . sprintf($params->get('tcats'), Text::_($track->cat)) . '</div>';
			}

			if ($params->get('distance') != 0)
			{
				echo '<div align="center">' . sprintf($params->get('tdistance'), $distance_mi_or_km) . '</div>';
			}
		}

		echo '</div>';
	}
	elseif ($params->get('style') == 1)
	{
		echo '<table><tr>';
		$link = Route::_('index.php?option=com_jtg&view=files&layout=file&id=' . $track->id);
		echo '<td align="center" style="padding: 10px" valign="top">';
		if ( $map != 'none' ) {
		echo '<img src="https://maps.googleapis.com/maps/api/staticmap?center=' . $track->start_n . ',' .
			$track->start_e . '&zoom=' . $zoom . '&size=' . $width . 'x' . $height . '&maptype=' .
			$map . '&markers=color:' . $color . '|' . $track->start_n . ',' . $track->start_e .
			($apikey? '&key=' . $apikey: '') . '&sensor=false" >';
		}
		echo '<div align="center"><a href="' . $link . '">' . $track->title . '</a></div>';

		if ($tcustom_enable)
		{
			echo $tcustom;
		}
		else
		{
			if ( ($params->get('cats') != 0 ) and ($track->cat) )
			{
				echo '<div align="center">' . sprintf($params->get('tcats'), Text::_($track->cat)) . '</div>';
			}

			if ($params->get('distance') != 0)
			{
				echo '<div align="center">' . sprintf($params->get('tdistance'), $distance_mi_or_km) . '</div>';
			}
		}

		echo '</td>';
		echo '</tr></table>';
	}
}
