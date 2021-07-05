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
	$buttonaction = "submitbutton('update')";
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
	$buttonaction = "submitbutton('save')";
}
$document = JFactory::getDocument();
$document->setTitle($title);
$pathway = $app->getPathway();
$pathway->addItem($title, '');

$cfg = JtgHelper::getConfig();

JHtml::_('behavior.modal');
JHtml::_('behavior.tooltip');
$yesnolist = array(
	array('id' => 0, 'title' => JText::_('JNO')),
	array('id' => 1, 'title' => JText::_('JYES'))
);
$editor = JFactory::getEditor();

// Field list
$catlist = $this->model->getCats();
$lists['content'] = JHtml::_('select.genericlist', $catlist, 'catid[]', 'multiple="multiple" ', 'id', 'title', explode(',',$this->track->catid));
$terrainlist = $this->model->getTerrain(" WHERE published=1 ");
$size = min(count($terrainlist), 6);
$lists['terrain'] = JHtml::_('select.genericlist', $terrainlist, 'terrain[]', 'multiple="multiple" size="' . $size . '"', 'id', 'title', explode(',',$this->track->terrain));
$lists['access'] = JtgHelper::getAccessList($this->track->access);
$lists['hidden']  = JHtml::_('select.genericlist', $yesnolist, 'hidden', 'class="inputbox" size="1"', 'id', 'title', $this->track->hidden);
$lists['published']  = JHtml::_('select.genericlist', $yesnolist, 'published', 'class="inputbox" size="1"', 'id', 'title', $this->track->published);
$maplist = $this->model->getDefaultMaps();
array_unshift($maplist, array('id' => 0, "name" => JText::_('JNONE')) );
$lists['default_map']   = JHtml::_('select.genericlist', $maplist, 'default_map', 'size="1"', 'id', 'name', $this->track->default_map);

$k = 0;

if (isset($this->map)) echo $this->map;
?>
<script type="text/javascript">

Joomla.submitbutton = function(pressbutton)
{
	var form = document.adminForm;
	if (pressbutton == 'cancel')
		{
		submitform( pressbutton );
		return;
	}
	if (pressbutton == 'reset') {
		submitform( pressbutton );
		return;
	}
	// Do field validation
	if (document.getElementById('title').value == ""){
		alert( "<?php echo JText::_('COM_JTG_NEED_TITLE', true); ?>");
	}
	if (document.getElementById('catid').value == "") {
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
			submitform( pressbutton );
		}
<?php
}
else
{
?>
		 else {
			submitform( pressbutton);
		}
<?php
}
?>

}
</script>

<?php
if (isset($this->map)) 
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
        <div id="popup" class="ol-popup">
          <a href="#" id="popup-closer" class="ol-popup-closer"></a>
          <div id="popup-content"></div>
</div>
<div>
	<form name="adminForm" id="adminForm" method="post"
		enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_jtg&view=track&layout=form', false); ?>">
		<table style="width:100%;">
			<tbody>
<?php
if (!isset($this->id))
{
?>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_GPS_FILE'); ?>*
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_FILES'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png');
					?>
					</td>
					<td><input type="file" name="file" value="" size="30" onchange="submitform('uploadGPX')"></td>
				</tr>
<?php
}
else
{
?>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_ID'); ?>:</td>
					<td><font color="grey"><?php echo $this->id; ?> </font></td>
				</tr>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_FILE'); ?>:</td>
					<td><font color="grey"><?php echo $this->track->file; ?> </font></td>
				</tr>
<?php
}
?>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_HIDDEN'); ?>*</td>
					<td><?php echo $lists['hidden']; ?></td>
				</tr>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k
					?>">
					<td><?php echo JText::_('COM_JTG_PUBLISHED'); ?>*</td>
					<td><?php echo $lists['published']; ?></td>
				</tr>
				<tr class="sectiontableentry<?php
				echo $k;
				$k = 1 - $k;
				?>">
					<td><?php echo JText::_('COM_JTG_TITLE'); ?>*</td>
					<td><input id="title" type="text" name="title"
						value="<?php echo $tracktitle; ?>"
						size="30" /></td>
				</tr>
				<tr class="sectiontableentry<?php
				echo $k;
				$k = 1 - $k;
				?>">
					<td><?php echo JText::_('COM_JTG_LEVEL'); ?>*
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_LEVEL'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png'); ?>
					</td>
					<td><?php echo $this->model->getLevelSelect($this->track->level); ?>
					</td>
				</tr>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_CAT'); ?></td>
					<td><?php echo $lists['content']; ?></td>
				</tr>
<?php
if ($this->cfg->access == 1)
{
?>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_ACCESS_LEVEL'); ?>&nbsp;
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_ACCESS'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png');?>
					</td>
					<td><?php echo $lists['access']; ?></td>
				</tr>
<?php
}
?>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_FILE_DEFAULT_MAP'); ?></td>
					<td><?php echo $lists['default_map']; ?></td>
				</tr>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_TERRAIN'); ?>
					<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_TERRAIN'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png'); ?>
					</td>
					<td><?php echo $lists['terrain']; ?></td>
				</tr>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td colspan="2"><p><?php echo JText::_('COM_JTG_DESCRIPTION'); ?>*:
						<?php echo JHtml::tooltip(JText::_('COM_JTG_TT_DESC'), JText::_('COM_JTG_TT_HEADER'), 'tooltip.png'); ?>
					</p>
					<?php echo $editor->display('description', $description, '100%', '200', '15', '25', false, null); ?>
					</td>
				</tr>
				<input id="mappreview" type="hidden" name="mappreview">
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<?php
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
					<td colspan="2"><?php echo JText::_('COM_JTG_IMAGES'); ?> :
					<?php
					echo JHtml::tooltip($tt, JText::_('COM_JTG_TT_HEADER'), 'tooltip.png');
					?>
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
           				echo "<div class=\"jtg-photo-item\"><input type=\"checkbox\" name=\"deleteimage_" . $image->id . "\" value=\""
                  	. $image->filename . "\">" . JText::_('COM_JTG_DELETE_IMAGE') . " (" . $image->filename . ")<br />" .
                  	"<img src=\"" . $imgurlpath . 'thumbs/' . $thumb_name . "\" alt=\"" . $image->filename . "\" title=\"" . $image->filename . " (thumbnail)\" /><br />".
                  	"<input type=\"text\" class=\"jtg-photo-title\" name=\"img_title_".$image->id . "\" value = \"".$image->title."\" placeholder=\"Title\" maxlength=\"256\"> <br /></div>\n";
						}
		         	echo "</div>";
						}
				?>
				</tr>
				<?php
				if ($this->cfg->terms == 1)
				{
				?>
				<tr class="sectiontableentry<?php
					echo $k;
					$k = 1 - $k;
					?>">
					<td><?php echo JText::_('COM_JTG_TERMS'); ?></td>
					<td><input id="terms" type="checkbox" name="terms" value="" /> <?php echo JText::_('COM_JTG_AGREE'); ?>
						<a class="modal" href="<?php echo $this->terms; ?>"
						target="_blank"><?php echo JText::_('COM_JTG_TERMS'); ?> </a></td>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>
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
			<button class="button" type="button" onclick="<?php echo $buttonaction; ?>">
				<?php echo $buttontext; ?>
			</button>
			<button class="button" type="button" onclick="submitbutton('reset')">
				<?php echo JText::_('COM_JTG_RESET') ?>
			</button>
			<?php
			if (isset($this->id) && !empty($this->track->title))
			{
				$canceltext = JText::_('COM_JTG_CANCEL_TO_FILEVIEW');
				$cancelaction = "submitbutton('cancel')";
				if ($app->getUserState('com_jtg.newfileid') == $this->id) {
					$canceltext = JText::_('JCANCEL');
					$cancelaction = "submitform('deletenew')";
				}
			}
			else
			{
				$canceltext = JText::_('JCANCEL');
				$cancelaction = "submitform('cancel')";	
			}
			?>
			<button class="button" type="button"
				onclick="<?php echo $cancelaction;?>">
				<?php echo $canceltext; ?>
			</button>
		</div>
	</form>
</div>
<?php

echo $this->footer;

if (isset($this->map)) {
	echo "\n<script type=\"text/javascript\">\n
   var olmap={ title: 'com_jtg_map_object' } \n
   slippymap_init();</script>\n";
}