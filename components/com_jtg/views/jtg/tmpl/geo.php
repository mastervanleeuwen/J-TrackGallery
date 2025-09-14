<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$document = Factory::getDocument();
$user = Factory::getUser();
$userid = (int) $user->id;
$tmpl = strlen($this->cfg->template) ? $this->cfg->template : 'default';
$iconpath = Uri::root() . "components/com_jtg/assets/template/" . $tmpl . "/images/";

if ( $userid )
{
	$document->addScriptDeclaration("var alerttext = '" . str_replace("'", "\'", Text::_('COM_JTG_SET_HOMEPOSITION')) . "';");
}
else
{
	$document->addScriptDeclaration("var alerttext = '" . str_replace("'", "\'", Text::_('COM_JTG_HOMEPOSITION_GUESTS')) . "';");
}

//$document->addScript( Uri::root(true) . '/media/system/js/mootools.js');
$document->addScriptDeclaration('var iconpath = \'' . $iconpath . '\';');
$document->addScript( Uri::root(true) . '/components/com_jtg/assets/js/homeposition.js');

// HTMLHelper::_('behavior.tooltip'); // with this option IE8 doesn't work
$otherusers = 0;
$scriptbody = "";
$imgpath = 'templates/' . $template . '/css/ol_images';

if ( Folder::exists(JPATH_SITE . '/' . $imgpath))
{
	$imgpath = Uri::root() . $imgpath;
}
else
{
	$imgpath = Uri::root() . 'components/com_jtg/assets/template/default/images/';
}


$scriptheader = ("<script type=\"text/javascript\">
		var iconpath = '" . $iconpath . "';\n");

$app = Factory::getApplication();
$defaultvars = (
		"	var imgpath = '" . $imgpath . "';
		var jtg_param_geo_lat = " . $app->input->getFloat('jtg_param_geo_lat') . ";
		var jtg_param_geo_lon = " . $app->input->getFloat('jtg_param_geo_lon') . ";
		var jtg_param_geo_zoom = " . $app->input->getInt('jtg_param_geo_zoom') . ";\n");

if ( $userid )
{
	// Sort logged in user to 1st place in array to give the option
	// To calculate the distances
	$latlon = array_merge(
			JtgHelper::getLatLon($userid),
			JtgHelper::getLatLon(false, $userid)
	);

	$homepos = Text::_('COM_JTG_MY_HOMEPOSITION');
}
else
{
	$latlon = JtgHelper::getLatLon();
	$homepos = "false";
}

$gpsCoords = new GpsCoordsClass;

for ($x = 0;$x <= count($latlon);$x++)
{
	if ((isset($latlon[$x]))
		AND ( (float) $latlon[$x]->jtglat != 0)
		AND ( (float) $latlon[$x]->jtglon != 0)
		AND ($latlon[$x]->id == $userid))
	{
		$userlat = $latlon[$x]->jtglat;
		$userlon = $latlon[$x]->jtglon;
		$uservis = $latlon[$x]->jtgvisible;
	}
	elseif ((isset($latlon[$x]))
			AND ( (float) $latlon[$x]->jtglat != 0)
			AND ( (float) $latlon[$x]->jtglon != 0)
			AND ($latlon[$x]->jtglat) && ($latlon[$x]->jtglon) && ($latlon[$x]->jtgvisible))
	{
		if ( ( ( $userid ) && ( $latlon[$x]->jtgvisible != "non" ) ) || ( ( !$userid ) && ( $latlon[$x]->jtgvisible == "all" ) ) )
		{
			if (isset($userlon))
			{
				$distance = (int) $gpsCoords->getDistance(
						array(
								array($userlon, $userlat),
								array($latlon[$x]->jtglon, $latlon[$x]->jtglat))
				);
				$distance = JtgHelper::getFormattedDistance($distance, 0, $this->cfg->unit);

				$distancetext = "<br />" . Text::_('COM_JTG_DISTANCE_GUEST');
			}
			else
			{
				$distance = "";
				$distancetext = "<br />" . Text::_('COM_JTG_NO_DISTANCE_GUEST');
			}

			if (empty($vars))
			{
				$vars = (
						"	var SizeIconOtherUser = [22,22];
						var OffsetIconOtherUser = [-0.5,-0.66];
						var IconOtherUser = '" . $iconpath . "user.png';
						var MarkerHomePosition = '" . $homepos . "';
						var inittext = '" . Text::_('COM_JTG_HERE_LIVE') . ": ';
						var distancetext = '" . $distancetext . "';
						var distance=Array();
						var username=Array();
						var name=Array();
						var lat=Array();
						var lon=Array();
						var link=Array();
						var id=Array();\n");
			}

			$scriptbody .=
			"	lat[" . $otherusers . "] = '" . $latlon[$x]->jtglat . "';
			lon[" . $otherusers . "] = '" . $latlon[$x]->jtglon . "';
			username[" . $otherusers . "] = '" . $latlon[$x]->username . "';
			distance[" . $otherusers . "] = '" . $distance . "';
			name[" . $otherusers . "] = '" . $latlon[$x]->name . "';
			id[" . $otherusers . "] = '" . $latlon[$x]->id . "';
			link[" . $otherusers . "] = '" . JtgHelper::getProfileLink($latlon[$x]->id, $latlon[$x]->username) . "';\n";
			$otherusers++;
		}
	}
	elseif (empty($vars))
	{
		$scriptbody = "	var MarkerHomePosition = '" . $homepos . "';\n";
	}
}

// If no other person saved
if (empty($vars))
{
	$vars = '';
}

$scriptfooter = ("</script>\n");
$scriptbody = "	var otherusers = '" . $otherusers . "';\n" . $scriptbody;
$script = $scriptheader . $defaultvars . $vars . $scriptbody . $scriptfooter;

echo $script;
echo "<div id=\"jtg_map\"  class=\"olMap\" style=\"width: " . $this->cfg->map_width . "px; height: " . $this->cfg->map_height . ";\" ></div>
<div id=\"otheruser\" style=\"width: " . $this->cfg->map_width . ";\" >" . Text::_('COM_JTG_HERE_LIVE_DESC') . "</div>\n";

if ( $userid )
{
	?>
<form action="<?php echo $this->geo; ?>" method="post" name="adminForm"
	id="adminForm">
	<?php echo '<br />' . Text::_('COM_JTG_LOCATION_DESCRIPTION') . '<br />';?>
	<table>
		<tr>
			<td><?php

			echo Text::_('COM_JTG_LAT');

			if (isset($userlat))
			{
				$lat = round($userlat, 15);
			}
			else
			{
				$lat = '';
			}

			if (isset($userlon))
			{
				$lon = round($userlon, 15);
			}
			else
			{
				$lon = '';
			}
			?>
			</td>
			<td><input type="text" size="15" class="output" name="lat" id="lat"
				value="<?php echo $lat; ?>"
				onchange="handleFillLL();mapcenter();"></input> <?php
				echo Text::_('COM_JTG_LAT_U');
				echo "</td>\n			<td>";
				echo HTMLHelper::tooltip(Text::_('COM_JTG_TT_LAT'));?>
			</td>
		</tr>
		<tr>
			<td><?php
			echo Text::_('COM_JTG_LON');
			?>
			</td>
			<td><input type="text" size="15" class="output" name="lon" id="lon"
				value="<?php
			echo $lon; ?>"
				onchange="handleFillLL();mapcenter();"></input> <?php
				echo Text::_('COM_JTG_LON_U');
				echo "</td>\n			<td>";
				echo HTMLHelper::tooltip(Text::_('COM_JTG_TT_LON'));?>
			</td>
		</tr>
		<tr>
			<td><?php echo Text::_('COM_JTG_VISIBLE'); ?></td>
			<td><select name="visible" id="visible" size="3">
					<?php
					// Selected="selected"
					$snon = "";
					$sreg = "";
					$sall = "";

					if ($uservis == 'non')
					{
						$snon = " selected=\"selected\"";
					}
					elseif ( $uservis == "reg" )
					{
						$sreg = " selected=\"selected\"";
					}
					else
					{
						$sall = " selected=\"selected\"";
					}

					echo "					<option value=\"all\"" . $sall . ">" . Text::_('COM_JTG_VISIBLE_ALL') . "</option>
					<option value=\"reg\"" . $sreg . ">" . Text::_('COM_JTG_VISIBLE_REG') . "</option>
					<option value=\"non\"" . $snon . ">" . Text::_('COM_JTG_VISIBLE_NONE') . "</option>
					";
					?>
</select>
			</td>
		</tr>
	</table>
	<?php
	echo HTMLHelper::_('form.token') . "\n"; ?>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="controller" value="geo" /> <input type="hidden"
		name="task" value="" />
	<?php
	if (isset($this->id))
	{
		echo '	<input type="hidden" name="id" value="' . $this->id . '" />';
	}
	?>
	<input type="submit" name="Submit" class="button"
		value="<?php echo Text::_('COM_JTG_SAVE') ?>"
		onclick="submitbutton('save')" />
</form>
<?php
}
else
{
?>
<input type="hidden" name="lat" id="lat"
	value=""></input>
<input type="hidden" name="lon" id="lon"
	value=""></input>
<?php
}
?>
<script type="text/javascript">init();</script>
<div class="no-float">
<?php
// Echo $this->disclaimericons;
echo $this->footer;
?>
</div>
