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

use Joomla\CMS\Language\Text;

?>
<div style="padding-left:10px">
<?php

if ($tcustom_enable)
{
	// Use custom display, replace each data when needed
	$tcustom = str_replace('$cats', $cats, $tcustom);
	$tcustom = str_replace('$tracks', $tracks, $tcustom);
	$tcustom = str_replace('$distance_km', $distance_km, $tcustom);
	$tcustom = str_replace('$distance_mi', $distance_mi, $tcustom);
	$tcustom = str_replace('$ascent', $ascent, $tcustom);
	$tcustom = str_replace('$descent', $descent, $tcustom);
	$tcustom = str_replace('$views', $views, $tcustom);
	$tcustom = str_replace('$votes', $votes, $tcustom);
	echo $tcustom;
}
else
{
	if ($theado == "1")
	{
		echo "<div>";
		echo Text::_($thead);
		echo "</div>";
	}

	echo '<ul>';

	if ($tcato == "1")
	{
		echo '<li>';
		echo sprintf($tcat, $cats);
		echo '</li>';
	}

	if ($ttracko == "1")
	{
		echo '<li>';
		echo sprintf($ttrack, $tracks);
		echo '</li>';
	}

	if ($tdiso == "1")
	{
		echo '<li>';
		echo sprintf($tdis, $distance);
		echo '</li>';
	}

	if ($tasco == "1")
	{
		echo '<li>';
		echo sprintf($tasc, $ascent);
		echo '</li>';
	}

	if ($tdeco == "1")
	{
		echo '<li>';
		echo sprintf($tdec, $descent);
		echo
		"</li>";
	}

	if ($tviewo == "1")
	{
		echo '<li>';
		echo sprintf($tview, $views);
		echo '</li>';
	}

	if ($tvoteo == "1")
	{
		echo '<li>';
		echo sprintf($tvote, $votes);
		echo '</li>';
	}

	echo '</ul>';
}
?>
</div>
