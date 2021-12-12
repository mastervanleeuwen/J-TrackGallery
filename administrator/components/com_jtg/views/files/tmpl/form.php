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

// Toolbar
if ($this->id < 1)
{
$title = JText::_('COM_JTG_ADD_FILE');
}
else
{
	$title = JText::_('COM_JTG_EDIT_FILE');
}

JToolBarHelper::title($title, 'categories.png');
JToolBarHelper::back();
JToolBarHelper::spacer();

if ($this->id < 1)
{
	JToolBarHelper::save('savefile', $alt = 'COM_JTG_SAVE', 'save.png');
}
else
{
	JToolBarHelper::save('updatefile', $alt = 'COM_JTG_SAVE');
	JToolBarHelper::custom('updateGeneratedValues', 'apply', 'apply', 'COM_JTG_REFRESH_DATAS', false);
}

JToolBarHelper::help('files/form', true);
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base(true) . '/components/com_jtg/template.css');
$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/default/jtg_style.css');
$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/default/ol.css');
$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/default/ol.css.map');

// Add jtg_map stylesheet
$cfg = JtgHelper::getConfig();
$tmpl = strlen($cfg->template) ? $cfg->template : 'default';
$document->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_map_style.css');
$map = "";

if ($this->id >= 1)
{
	// Edit file
	$infoIconText = 0;
	if (version_compare(JVERSION, '3.9', 'lt'))
	{
		JHtml::_('behavior.tooltip');
	}
	else
	{
		JHtmlBootstrap::tooltip('.hasTooltip');
		if (version_compare(JVERSION, '4.0', 'ge')) $infoIconText = '<i class="fas fa-info-circle"></i>';
	}
	$cfg = JtgHelper::getConfig();
	$params = JComponentHelper::getParams('com_jtg');
	$model = $this->getModel();
	$track = $model->getFile($this->id);
	$document = JFactory::getDocument();
	require_once '../components/com_jtg/helpers/gpsClass.php';
	$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/ol.js');
	$document->addScript( JUri::root(true) . '/components/com_jtg/assets/js/jtg.js');
	if ($params->get('jtg_param_disable_map_animated_cursor') == "0") {
		$document->addScript(JUri::root(true) . '/components/com_jtg/assets/js/animatedCursor.js');
	}
	$file = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . $this->track->file;
	$gpsData = new GpsDataClass($cfg->unit);
	$gpsData->loadFileAndData( $file, $track->file );
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
	<table class="adminlist">
		<thead>
			<tr>
				<th colspan="2" align="center"><h1><?php echo $title; ?></h1></th><th></th>
			</tr>
		</thead>
		<tbody>
			<tr class="row1 row-odd">
				<td>
				<?php
				echo JText::_('COM_JTG_GPS_FILE') . ":";
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
						echo $this->track->file;
					}
					?>
				</td>
				<td rowspan="12" valign="top"><?php echo $map; ?></td>
			</tr>
<?php
if ($this->id >= 1)
{
?>
			<tr class="row0 row-even">
				<td>Id:</td>
				<td><?php echo $this->id; ?></td>
			</tr>
<?php
}
?>
			<tr class="row1 row-odd">
				<td><?php echo JText::_('COM_JTG_PUBLISHED'); ?>:*</td>
				<td><?php echo $this->lists['published']; ?></td>
			</tr>
			<tr class="row0 row-even">
				<td><?php echo JText::_('COM_JTG_TITLE'); ?>:*</td>
				<td><input id="title" type="text" name="title"
					value="<?php echo (isset($this->id) AND ($this->id != 0))? $this->track->title: ''; ?>"
					size="30" /></td>
			</tr>
			<tr class="row1 row-odd">
				<td><?php echo JText::_('COM_JTG_DATE'); ?>:*</td>
				<td><input id="date" type="text" name="date"
					value="<?php echo (isset($this->id) AND ($this->id != 0))? $this->track->date: ''; ?>"
					size="10" /></td>
			</tr>
			<tr class="row0 row-even">
				<td><?php echo JText::_('COM_JTG_INFO_AUTHOR'); ?>:*<br />
				</td>
				<td><?php echo $this->lists['uid']; ?></td>
			</tr>
			<tr class="row1 row-odd">
				<td>
<?php
		echo JText::_('COM_JTG_LEVEL').':*';
		echo JHtml::tooltip(JText::_('COM_JTG_TT_LEVEL'),'','tooltip.png',$infoIconText);
?>
				</td>
				<td><?php echo $this->lists['level']; ?></td>
			</tr>
			<tr class="row0 row-even">
				<td><?php echo JText::_('COM_JTG_CAT'); ?>:</td>
				<td><?php echo $this->lists['cats']; ?></td>
			</tr>
			<tr class="row1 row-odd">
				<td><?php echo JText::_('COM_JTG_ACCESS_LEVEL'); ?>:</td>
				<td><?php echo $this->lists['access']; ?></td>
			</tr>
			<tr class="row0 row-even">
				<td><?php echo JText::_('COM_JTG_HIDDEN'); ?>:</td>
				<td><?php echo $this->lists['hidden']; ?></td>
			</tr>
			<tr class="row0 row-odd">
				<td><?php echo JText::_('COM_JTG_FILE_DEFAULT_MAP'); ?></td>
				<td><?php echo $this->lists['default_map']; ?></td>
			</tr>
			<tr class="row1 row-odd">
				<td><?php echo JText::_('COM_JTG_TERRAIN'); ?>:</td>
				<td><?php echo $this->lists['terrain']; ?></td>
			</tr>
			<tr class="row0 row-even">
				<td><?php echo JText::_('COM_JTG_CALCULATED_VALUES'); ?>:</td>
				<td><?php echo $this->lists['values']; ?></td>
			</tr>
			<tr class="row1 row-odd">
				<td colspan="3"><?php echo JText::_('COM_JTG_DESCRIPTION'); ?>:*
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
			<td>" . JText::_('COM_JTG_WPS') . ":</td>
			<td colspan=\"2\"></td>
			</tr>
			<tr>
			<td>" . JText::_('COM_JTG_TRACKS') . ":</td>
			<td colspan=\"2\"></td>
			</tr>
			";
		 */
			?>
			<tr class="row0 row-even">
				<td valign="top" colspan="3"><?php echo JText::_('COM_JTG_IMAGES'); ?>
					(max. 10): <input type="file" name="images[]" class="multi"
					maxlength="10"><br clear="all" /> <br /> <?php echo isset($this->images)? $this->images: ''; ?>
				</td>
			</tr>
			<?php
			if ($cfg->terms == 1)
			{
			?>
			<tr>
				<td><?php echo JText::_('COM_JTG_TERMS'); ?></td>
				<td><input id="terms" type="checkbox" name="terms" value="" /> <?php echo JText::_('COM_JTG_AGREE'); ?>
					<a class="modal"
					href="<?php echo JUri::base() . "../?option=com_content&view=article&id=" . $cfg->terms_id; ?>"
					target="_blank"><?php echo JText::_('COM_JTG_TERMS'); ?> </a></td>
			</tr>
			<?php
			}
			?>
		</tbody>
	</table>
	<?php echo JHtml::_('form.token'); ?>
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
