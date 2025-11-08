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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;
use Jtg\Component\Jtg\Site\Helpers\JtgMapHelper;
use Jtg\Component\Jtg\Site\Helpers\GPSData;

// Toolbar
if ($this->id < 1)
{
$title = Text::_('COM_JTG_ADD_FILE');
}
else
{
	$title = Text::_('COM_JTG_EDIT_FILE');
}

ToolbarHelper::title($title, 'categories.png');
ToolbarHelper::back();
ToolbarHelper::spacer();

if ($this->id < 1)
{
	ToolbarHelper::save('savefile', $alt = 'COM_JTG_SAVE', 'save.png');
}
else
{
	ToolbarHelper::save('updatefile', $alt = 'COM_JTG_SAVE');
	ToolbarHelper::custom('updateGeneratedValues', 'apply', 'apply', 'COM_JTG_REFRESH_DATAS', false);
}
//ToolbarHelper::help('files/form', true);

$document = Factory::getDocument();
$document->addStyleSheet(Uri::base(true) . '/components/com_jtg/template.css');
$document->addStyleSheet(Uri::root(true) . '/components/com_jtg/assets/template/default/jtg_style.css');
$document->addStyleSheet(Uri::root(true) . '/media/com_jtg/js/openlayers/ol.css');
$document->addStyleSheet(Uri::root(true) . '/media/com_jtg/js/openlayers/ol.css.map');

// Add jtg_map stylesheet
$cfg = JtgHelper::getConfig();
$tmpl = strlen($cfg->template) ? $cfg->template : 'default';
$document->addStyleSheet(Uri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');
$map = "";

if ($this->id >= 1)
{
	// Edit file
	$infoIconText = 0;
	if (version_compare(JVERSION, '4.0', 'ge')) {
		$infoIconText = '<i class="fas fa-info-circle"></i>';
	}
	else {
		HTMLHelper::_('behavior.tooltip');
	}
	$cfg = JtgHelper::getConfig();
	$params = ComponentHelper::getParams('com_jtg');
	$model = $this->getModel();
	$track = $model->getFile($this->id);
	$document = Factory::getDocument();
	$document->addScript( Uri::root(true) . '/media/com_jtg/js/openlayers/ol.js');
	$document->addScript( Uri::root(true) . '/components/com_jtg/assets/js/jtg.js');
	if ($params->get('jtg_param_disable_map_animated_cursor') == "0") {
		$document->addScript(Uri::root(true) . '/components/com_jtg/assets/js/animatedCursor.js');
	}
	$file = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . $this->track->file;
	$gpsData = new GPSData($file, $track->file);
	$imageList = $model->getImages($this->id);

	if ($gpsData->displayErrors())
	{
		$map = "";
	}
	else
	{
		$map = '<style type="text/css">

.olButton::before{
	display: none;
}
#jtg_map img{
	max-width: none; /* joomla3 max-width=100% breaks popups*/
}

/* Fix Bootstrap-Openlayers issue */
.olMap img { max-width: none !important;
}

img.olTileImage {
	max-width: none !important;
}
</style>';
		$map .= ("\n<div id=\"jtg_map\"  align=\"center\" class=\"olMap\" ");
		$map .= ("style=\"width: 400px; height: 500px; background-color:#EEE; vertical-align:middle;\" >");
		$map .= ("\n</div>");
		$map .= JtgMapHelper::parseTrackMapJS( $gpsData, $this->id, $this->track->default_map, $imageList, false, false );
	}
}

?>

<form action="" method="post" name="adminForm" id="adminForm"
	class="adminForm" enctype="multipart/form-data">
	<table class="table table-striped">
		<thead>
			<tr>
				<th colspan="2" align="center"><h1><?php echo $title; ?></h1></th><th></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
				<?php
				echo Text::_('COM_JTG_GPS_FILE') . ":";
				echo $this->id < 1? '*': '';
				?>
				</td>
				<td>
					<?php
					if ($this->id < 1)
					{
					?><input type="file" name="file"
						value="" size="30" /> <?php
					}
					else
					{
						echo wordwrap($this->track->file,25,"<wbr>",true);
					}
					?>
				</td>
				<td rowspan="13" valign="top"><?php echo $map; ?></td>
			</tr>
<?php
if ($this->id >= 1)
{
?>
			<tr>
				<td>Id:</td>
				<td><?php echo $this->id; ?></td>
			</tr>
<?php
}
?>
			<tr>
				<td><?php echo Text::_('COM_JTG_PUBLISHED'); ?>:*</td>
				<td><?php echo $this->lists['published']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_TITLE'); ?>:*</td>
				<td><input id="title" class="inputbox form-control" type="text" name="title"
					value="<?php echo (isset($this->id) AND ($this->id != 0))? $this->track->title: ''; ?>"
					style="width:100%;" /></td>
			</tr>
			<tr>
				<td><?php echo Text::_('JALIAS'); ?>:</td>
				<td><input id="alias" class="inputbox form-control" type="text" name="alias" placeholder="<?php echo Text::_('JFIELD_ALIAS_PLACEHOLDER'); ?>"
					value="<?php echo (isset($this->id) AND ($this->id != 0))? $this->track->alias: ''; ?>"
					style="width:100%;" /></td>
			<tr>
				<td><?php echo Text::_('COM_JTG_DATE'); ?>:*</td>
				<td><input id="date" class="inputbox form-control" type="text" name="date"
					value="<?php echo (isset($this->id) AND ($this->id != 0))? $this->track->date: ''; ?>"
					size="10" /></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_INFO_AUTHOR'); ?>:*<br />
				</td>
				<td><?php echo $this->lists['uid']; ?></td>
			</tr>
			<tr>
				<td>
<?php
		echo Text::_('COM_JTG_LEVEL').':*';
		echo HTMLHelper::tooltip(Text::_('COM_JTG_TT_LEVEL'),'','tooltip.png',$infoIconText);
?>
				</td>
				<td><?php echo $this->lists['level']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_CAT'); ?>:</td>
				<td><?php echo $this->lists['cats']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_ACCESS_LEVEL'); ?>:</td>
				<td><?php echo $this->lists['access']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_HIDDEN'); ?>:</td>
				<td><?php echo $this->lists['hidden']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_FILE_DEFAULT_MAP'); ?>:</td>
				<td><?php echo $this->lists['default_map']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_TERRAIN'); ?>:</td>
				<td><?php echo $this->lists['terrain']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('JTAG'); ?>:</td>
				<td><?php echo $this->lists['tags']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_CALCULATED_VALUES'); ?>:</td>
				<td><?php echo $this->lists['values']; ?></td>
			</tr>
			<tr>
				<td colspan="3"><?php echo Text::_('COM_JTG_DESCRIPTION'); ?>:*
				<?php
				if ( isset($this->track->description) )
				{
					$trackdescription = $this->track->description;
				}
				else
				{
					$trackdescription = null;
				}

				echo $this->editor->display('description', $trackdescription, '100%', '200px', '15', '25', false, null);
				?>
				</td>
			</tr>
			<?php
			/*
			 echo "	<tr>
			<td>" . Text::_('COM_JTG_WPS') . ":</td>
			<td colspan=\"2\"></td>
			</tr>
			<tr>
			<td>" . Text::_('COM_JTG_TRACKS') . ":</td>
			<td colspan=\"2\"></td>
			</tr>
			";
		 */
			?>
			<tr>
				<td valign="top" colspan="3"><?php echo Text::_('COM_JTG_IMAGES'); ?>
					(max. 10): <input type="file" name="images[]" class="multi"
					maxlength="10"><br clear="all" /> <br /> <?php echo isset($this->images)? $this->images: ''; ?>
				</td>
			</tr>
			<?php
			if ($cfg->terms == 1)
			{
			?>
			<tr>
				<td><?php echo Text::_('COM_JTG_TERMS'); ?></td>
				<td><input id="terms" type="checkbox" name="terms" value="" /> <?php echo Text::_('COM_JTG_AGREE'); ?>
					<a class="modal"
					href="<?php echo Uri::base() . "../?option=com_content&view=article&id=" . $cfg->terms_id; ?>"
					target="_blank"><?php echo Text::_('COM_JTG_TERMS'); ?> </a></td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
	<?php echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="controller" value="files" /> <input type="hidden"
		name="task" value="" />
	<?php
	if ($this->id)
	{
		echo "<input type=\"hidden\" name=\"id\" value=\"" . $this->id . "\" />";
		echo "<input type=\"hidden\" name=\"file\" value=\"" . $this->track->file . "\" />";
	}
	?>
</form>
