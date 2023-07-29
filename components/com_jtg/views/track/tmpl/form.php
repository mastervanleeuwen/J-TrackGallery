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

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

//
// This form has three states or modes:
//   1) Update mode: update existing record
//   2a) New mode (no id set)
//   2b) New mode, but a file has been uploaded, also $id is set
// The disctinction between 2b and 1 is now made based on whether 
// the title is already set; this logic can be improved.
//
$tracktitle='';
$user = JFactory::getUser();
$uid = $user->id;
$app = JFactory::getApplication(); 
if (isset($this->id))
{
	if (!($this->canDo->get('core.edit') || 
			($this->canDo->get('core.edit.own') && $this->track->uid == $uid) ||
			($this->id == $app->getUserState('com_jtg.newfileid') && $this->canDo->get('core.create') ) ) )
	{
		$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		$app->setHeader('status', 403, true);
		return;
	}
	$description = $this->track->description;
	$buttonaction = "Joomla.submitbutton('update')";
	if ($this->id == $app->getUserState('com_jtg.newfileid') )
	{
		$title = JText::_('COM_JTG_NEW_TRACK');
		$buttontext = JText::_('COM_JTG_SAVE');
	}
	else { // existing file
		$title = JText::_('COM_JTG_UPDATE_GPS_FILE');
		$buttontext = JText::_('COM_JTG_SAVE_TO_FILEVIEW');
	}
	$tracktitle = $this->track->title;
	$title .= ": ".$this->track->title;
}
else
{
	if (!($this->canDo->get('core.create'))) 
	{
		$app = JFactory::getApplication(); 
		$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
		$app->setHeader('status', 403, true);
		return;
	}
	$description = '';
	if (isset($this->track->description)) $description = $this->track->description;
	$buttontext = JText::_('COM_JTG_SAVE');
	$title = JText::_('COM_JTG_NEW_TRACK');
	// TODO: This should normally not happen, since the file needs to be uploaded first?
	$buttonaction = "Joomla.submitbutton('save')";
}
$document = JFactory::getDocument();
$document->setTitle($title);
$pathway = $app->getPathway();
$pathway->addItem($title, '');

$cfg = JtgHelper::getConfig();

$infoIconText = '';
$version_parts = explode('.',JVERSION);
if (version_compare(JVERSION,'4.0','lt'))
{
	JHtml::_('behavior.modal');
	JHtml::_('behavior.tooltip');
}
else
{
	JHtmlBootstrap::tooltip('.hasTooltip');
	if ($version_parts[0] > 3) $infoIconText = '<i class="fas fa-info-circle"></i>';
}

if (version_compare(JVERSION,'4.0','lt'))
{
	$editor = JFactory::getConfig()->get('editor');
}
else {
	$editor = Factory::getApplication()->getConfig()->get('editor');
}
$editor = Editor::getInstance($editor);;

// Field list
$catlist = $this->model->getCats();
$lists['content'] = JHtml::_('select.genericlist', $catlist, 'catid[]', 'class="form-select" multiple="multiple" ', 'id', 'title', explode(',',$this->track->catid));
$terrainlist = $this->model->getTerrain(" WHERE published=1 ");
$size = min(count($terrainlist), 6);
$lists['terrain'] = JHtml::_('select.genericlist', $terrainlist, 'terrain[]', 'class="form-select" multiple="multiple" size="' . $size . '"', 'id', 'title', explode(',',$this->track->terrain));
$lists['access'] = JtgHelper::getAccessList($this->track->access);
$lists['hidden']  = JHtml::_('select.booleanlist', 'hidden', null, $this->track->hidden);
$lists['published']  = JHtml::_('select.booleanlist', 'published', null, $this->track->published);
$maplist = $this->model->getDefaultMaps();
array_unshift($maplist, array('id' => 0, "name" => JText::_('JNONE')) );
$lists['default_map']   = JHtml::_('select.genericlist', $maplist, 'default_map', 'class="form-select size="1"', 'id', 'name', $this->track->default_map);

?>
<script type="text/javascript">

Joomla.submitbutton = function(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel')
		{
		Joomla.submitform( pressbutton );
		return;
	}
	if (pressbutton == 'reset') {
		Joomla.submitform( pressbutton );
		return;
	}
	// Do field validation
	if (document.getElementById('title').value == ""){
		alert( "<?php echo JText::_('COM_JTG_NEED_TITLE', true); ?>");
	}
	if (document.getElementById('catid') && document.getElementById('catid').value == "") {
		alert( "<?php echo JText::_('COM_JTG_NEED_CATEGORY', true); ?>");
   }
<?php
if ($this->cfg->terms == 1)
{
?>
		else if (document.getElementById('terms').checked == false) {
			alert( "<?php echo JText::_('COM_JTG_NEED_TERMS', true); ?>");
		}
		else
		{
			Joomla.submitform( pressbutton );
		}
<?php
}
else
{
?>
		 else {
			Joomla.submitform( pressbutton);
		}
<?php
}
?>

}
</script>

<?php
if (isset($this->id)) 
{
?>
<style type="text/css">
#jtg_map.olMap {
   height: <?php echo $this->cfg->map_height; ?>;
   width: <?php echo $this->cfg->map_width; ?>;
   z-index: 0;
}
.olButton::before {
   display: none;
}

#jtg_map.fullscreen {
   height: 800px;
   width: 100%;
   z-index: 10000;
}

/* Fix Bootstrap-Openlayers issue */
img.olTileImage {
   max-width: none !important;
}
.olPopup img { max-width: none !important;
}

</style>
<?php
}
?>
<div class="componentheading">
	<h1><?php echo $title; ?></h1>
</div>
<div>
   <center><div id="jtg_map" class="olMap"></div><br /></center>
<?php
if (isset($this->mapJS))
{
?>
        <div id="popup" class="ol-popup">
          <a href="#" id="popup-closer" class="ol-popup-closer"></a>
          <div id="popup-content"></div>
</div>
<?php
}
?>
<div>
	<form name="adminForm" id="adminForm" method="post"
		enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_jtg&view=track&layout=form', false); ?>">
		<table class="table table-striped" style="width:100%;">
			<tbody>
<?php
if (!isset($this->id))
{
?>
				<tr>
					<td><?php echo JText::_('COM_JTG_GPS_FILE'); ?>*
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_FILES'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png',$infoIconText);
					?>
					</td>
					<td><input type="file" name="file" value="" onchange="Joomla.submitform('uploadGPX')" style="width:100%;"></td>
				</tr>
<?php
}
else
{
?>
				<tr>
					<td><?php echo JText::_('COM_JTG_ID'); ?>:</td>
					<td><font color="grey"><?php echo $this->id; ?> </font></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JTG_FILE'); ?>:</td>
					<td><font color="grey"><?php echo wordwrap($this->track->file,25,'<wbr>',true); ?> </font></td>
				</tr>
<?php
}
?>
				<tr>
					<td><?php echo JText::_('COM_JTG_HIDDEN'); ?>*</td>
					<td><?php echo $lists['hidden']; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JTG_PUBLISHED'); ?>*</td>
					<td><?php echo $lists['published']; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JTG_TITLE'); ?>*</td>
					<td><input id="title" class="form-control" type="text" name="title"
						value="<?php echo $tracktitle; ?>" /></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JTG_LEVEL'); ?>*
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_LEVEL'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png', $infoIconText); ?>
					</td>
					<td><?php echo $this->model->getLevelSelect($this->track->level); ?>
					</td>
				</tr>
<?php
	if ($this->params->get('jtg_param_use_cats'))
	{ ?>
				<tr>
					<td><?php echo JText::_('COM_JTG_CAT'); ?></td>
					<td><?php echo $lists['content']; ?></td>
				</tr>
<?php
	}

if ($this->cfg->access == 1)
{
?>
				<tr>
					<td><?php echo JText::_('COM_JTG_ACCESS_LEVEL'); ?>&nbsp;
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_ACCESS'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png', $infoIconText);?>
					</td>
					<td><?php echo $lists['access']; ?></td>
				</tr>
<?php
}
?>
				<tr>
					<td><?php echo JText::_('COM_JTG_FILE_DEFAULT_MAP'); ?></td>
					<td><?php echo $lists['default_map']; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JTG_TERRAIN'); ?>
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_TERRAIN'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png', $infoIconText); ?>
					</td>
					<td><?php echo $lists['terrain']; ?></td>
				</tr>
			</tbody>
		</table>
		<p><?php echo JText::_('COM_JTG_DESCRIPTION'); ?>*:
			<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_DESC'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png', $infoIconText); ?>
		</p>
<?php 
		echo $editor->display('description', $description, '100%', '200px', null, null, false, null);
		echo HTMLHelper::_('bootstrap.startAccordion', 'calcVals');
		echo HTMLHelper::_('bootstrap.addSlide', 'calcVals', '<div class="jtg-header">'.JText::_('COM_JTG_CALCULATED_VALUES').'</div>', 'collapse1');
?>
		<table class="table">
			<tbody>
			<tr>
				<td><?php echo JText::_('COM_JTG_DISTANCE'); ?></td>
				<td><input id="distance" type="text" name="distance" class="form-control" value="<?php echo $this->track->distance; ?>" /> <?php echo JText::_('COM_JTG_DISTANCE_UNIT_'.strtoupper($this->cfg->unit)).' <font color="grey">( '.JtgHelper::getFormattedDistance($this->gpsTrack->distance, '',$this->cfg->unit).' )</font>'; ?> </td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_ELEVATION_UP') ?></td>
				<td><input id="ascent" type="text" name="ascent" class="form-control" value="<?php echo $this->track->ele_asc; ?>" /> <?php echo JText::_('COM_JTG_ELEVATION_UNIT').' <font color="grey">( '.$this->gpsTrack->totalAscent.' ) </font>'; ?> </td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_ELEVATION_DOWN') ?></td>
				<td><input id="descent" type="text" name="descent" class="form-control" value="<?php echo $this->track->ele_desc; ?>" /> <?php echo JText::_('COM_JTG_ELEVATION_UNIT').' <font color="grey">( '.$this->gpsTrack->totalDescent.' )</font>'; ?> </td>
			</tr>
			</tbody>
		</table>
		</div>
<?php 
			echo HTMLHelper::_('bootstrap.endSlide');
			echo HTMLHelper::_('bootstrap.endAccordion');
			$max_images = $this->cfg->max_images;

			if (!empty($this->imageList))
			{
				$max_images = ( $max_images - count($this->imageList) );

				if ($max_images <= 0)
					{
						$max_images = 0;
					}
			}

			// Accept  jpg,png,gif
			$accept = $this->cfg->type;
			$accept = explode(",", $accept);
			$tt = JText::sprintf('COM_JTG_ALLOWED_FILETYPES', implode(", ", $accept)) . '  ' . JText::_('COM_JTG_MAXIMAL') . ' ' . $max_images;
		?>
		<div>
		<div class="jtg-header"><?php echo JText::_('COM_JTG_IMAGES'); ?>
		<?php
			echo JHtml::tooltip($tt, JText::_('COM_JTG_TT_HEADER'), 'tooltip.png', $infoIconText);
		?>
		</div>
			<input
			<?php
				echo $max_images <= 0 ? 'disabled="disabled" ': ''; ?>
					type="file" name="images[]" class="multi"
					maxlength="<?php echo $max_images; ?>"
					accept="<?php echo implode("|", $accept) ?>">
				<br clear="all" />
				<?php
					if (!empty($this->imageList)) {
						$imgurlpath=JUri::base() . "images/jtrackgallery/uploaded_tracks_images/track_" . $this->track->id . "/";
						echo "<div class=\"jtg-photo-grid\">";
						foreach ($this->imageList as $image) {
							$thumb_name = 'thumb1_' . $image->filename;
							echo "<div class=\"jtg-photo-item\"><input type=\"checkbox\" name=\"deleteimage_" . $image->id . "\" value=\"" .
								$image->filename . "\"> " . JText::_('COM_JTG_DELETE_IMAGE') . " (" . $image->filename . ")<br />" .
								"<img src=\"" . $imgurlpath . 'thumbs/' . $thumb_name . "\" alt=\"" . $image->filename . "\" title=\"" . $image->filename . " (thumbnail)\" /><br />".
								"<input type=\"text\" class=\"inputbox jtg-photo-input\" name=\"img_title_".$image->id . "\" value = \"".$image->title."\" placeholder=\"Title\" maxlength=\"256\"> <br>".
								"<input type=\"text\" class=\"inputbox jtg-photo-input\" id=\"img_long_".$image->id."\" name=\"img_long_".$image->id."\" placeholder=\"".JText::_('COM_JTG_LON')."\" value = \"".(!is_null($image->lon)?number_format($image->lon,5):'')."\" size=\"8\" > ".JText::_('COM_JTG_LON_U').
								" <input type=\"text\" class=\"inputbox jtg-photo-input\" name=\"img_lat_".$image->id."\" id=\"img_lat_".$image->id."\" placeholder=\"".JText::_('COM_JTG_LAT')."\" value = \"".(!is_null($image->lat)?number_format($image->lat,5):'')."\" size=\"8\"> ".JText::_('COM_JTG_LAT_U')."<br>".
								"<button type=\"button\" class=\"btn btn-sm btn-primary\" onclick=\"listenLocation(".$image->id.");\">".JText::_('COM_JTG_SELECTONMAP')."</button> </div>\n";
						}
						echo "</div>";
				}
				?>
	</div>
				<?php
				if ($this->cfg->terms == 1)
				{
				?>
				<table class="table table-striped" style="width:100%">
				<tbody>
				<tr>
					<td><?php echo JText::_('COM_JTG_TERMS'); ?></td>
					<td><input id="terms" type="checkbox" name="terms" value="" /> <?php echo JText::_('COM_JTG_AGREE'); ?>
						<a class="modal" href="<?php echo $this->terms; ?>"
						target="_blank"><?php echo JText::_('COM_JTG_TERMS'); ?> </a></td>
				</tr>
				</tbody>
				</table>
				<?php
				}
			?>
		<div>
		<input id="mappreview" type="hidden" name="mappreview">
		<?php
		echo JHtml::_('form.token') . "\n"; ?>
		<input type="hidden" name="option" value="com_jtg" /> <input
			type="hidden" name="controller" value="track" />
		<?php
		echo isset($this->id)? '<input type="hidden" name="id" value=" ' . $this->id . '" />': '';
		?>
		<input type="hidden" name="task" value="" />
		<div>
			<br />
			<button class="btn btn-primary" type="button" onclick="<?php echo $buttonaction; ?>">
				<?php echo $buttontext; ?>
			</button>
			<button class="btn btn-danger" type="button" onclick="Joomla.submitbutton('reset')">
				<?php echo JText::_('COM_JTG_RESET') ?>
			</button>
			<?php
			if (isset($this->id) && !empty($this->track->title))
			{
				$canceltext = JText::_('COM_JTG_CANCEL_TO_FILEVIEW');
				$cancelaction = "Joomla.submitbutton('cancel')";
				if ($app->getUserState('com_jtg.newfileid') == $this->id) {
					$canceltext = JText::_('JCANCEL');
					$cancelaction = "Joomla.submitform('deletenew')";
				}
			}
			else
			{
				$canceltext = JText::_('JCANCEL');
				$cancelaction = "Joomla.submitform('cancel')";	
			}
			?>
			<button class="btn btn-secondary" type="button"
				onclick="<?php echo $cancelaction;?>">
				<?php echo $canceltext; ?>
			</button>
		</div>
	</form>
</div>
<?php

echo $this->footer;

if (isset($this->mapJS)) echo $this->mapJS;
?>
