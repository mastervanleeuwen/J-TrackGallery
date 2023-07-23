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
use Joomla\CMS\Uri\Uri;
$app = JFactory::getApplication();
$sitename = $app->getCfg('sitename');
$document = JFactory::getDocument();
$document->setTitle($this->track->title . " - " . $sitename);
$pathway = $app->getPathway();
$pathway->addItem($this->track->title, '');

echo $this->menubar;

$JtgHelper = new JtgHelper;
$maySeeSingleFile = $this->maySeeSingleFile($this);

if ($maySeeSingleFile === true)
{	
	$mapimagefile='images/jtrackgallery/maps/track_'.$this->track->id.'.png';
	if (JFile::exists(JPATH_SITE.'/'.$mapimagefile)) {
		JFactory::getDocument()->setMetaData('og:image',Uri::root().$mapimagefile,'property');
	}
	
	if ( $this->params->get("jtg_param_hide_track_info") )
	{
		$gps_info = false;
	}
	else
 	{
		$gps_info = true;
	}

	$durationbox = (bool) $this->params->get("jtg_param_show_durationcalc");
?>

<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v11.0&appId=180128866079216&autoLogAppEvents=1" nonce="mBqx9YbV"></script>

<?php
	//echo $this->map;

	if ( !empty($this->imageList) && 
		( ( $this->cfg->gallery == "jd2" ) OR ( $this->cfg->gallery == "highslide" ) ) )
	{
		switch ($this->cfg->gallery)
  		{
			case 'jd2' :
?>
               <script type="text/javascript">
               startGallery = function()  {
               var myGallery = new gallery($('myGallery'), {
               timed: true,
               showArrows: true,
               embedLinks: false,
               showCarousel: false
         });
         }
         window.addEventListener('domready',startGallery);
         </script>
<?php
				break;
            case 'highslide' :
?>
              <script type="text/javascript">
               hs.graphicsDir = '<?php echo JUri::base() . "components/com_jtg/assets/highslide/graphics/"; ?>';
               hs.align = 'center';
               hs.transitions = ['expand', 'crossfade'];
               hs.fadeInOut = true;
               hs.outlineType = 'rounded-white';
               hs.headingEval = 'this.a.title';
               hs.numberPosition = 'heading';
               hs.useBox = true;
               hs.width = 600;
               hs.height = 400;
               hs.showCredits = false;
               hs.dimmingOpacity = 0.8;

               // Add the slideshow providing the controlbar and the thumbstrip
               hs.addSlideshow({
               //slideshowGroup: 'group1',
               interval: 5000,
               repeat: false,
               useControls: true,
fixedControls: 'fit',
               overlayOptions: {
               position: 'top right',
               offsetX: 200,
               offsetY: -65
         },
         thumbstrip: {
         position: 'rightpanel',
         mode: 'float',
         relativeTo: 'expander',
         width: '210px'
         }
         });
         // Make all images animate to the one visible thumbnail
         var miniGalleryOptions1 = {
         	thumbnailId: 'thumb1'
         }
 </script>
<?php
			break;
		}
	}

	echo $this->parseTemplate("headline", $this->track->title, "jtg_param_header_map", null, $this->track->title);
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
<center><div id="jtg_map" class="olMap"></div></center>
	<div id="popup" class="ol-popup">
   <a href="#" id="popup-closer" class="ol-popup-closer"></a>
   <div id="popup-content"></div>
</div>
<?php
	echo $this->mapJS;
	$sortedcats = JtgModeljtg::getCatsData(true);
	$catids = explode(',',$this->track->catid);
	$usepace = is_numeric($catids[0])? $sortedcats[$catids[0]]->usepace: 0;
	$graphJS = "";
	if (isset($this->gpsTrack)) $graphJS = JtgMapHelper::parseGraphJS($this->gpsTrack, $this->cfg, $this->params, $usepace);
	if (!empty($graphJS))
	{
	?>
<div id="profile" style="width: <?php echo $this->cfg->charts_width; ?>;" >
	<div class="profile-img" id="elevation" style="width: 100%; height: <?php echo $this->cfg->charts_height; ?>;"></div>
</div>
	<?php
		echo $graphJS;
		echo '<div class="description"></div>'."\n";
	}

	if ($gps_info && isset($this->gpsTrack)){
		echo JtgHelper::parseTrackInfo($this->track, $this->gpsTrack, $this->params, $this->cfg);
	}
	echo "<div class=\"description\">\n";
	if ($this->cfg->usevote == 1)
	{
		//echo $this->parseTemplate("headline", JText::_('COM_JTG_VOTING'), "jtg_param_header_rating");
		$vote = $this->model->getVotes($this->id);

		$stars = array(
			1 => "one",
			2 => "two",
			3 => "three",
			4 => "four",
			5 => "five",
			6 => "six",
			7 => "seven",
			8 => "eight",
			9 => "nine",
			10 => "ten"
		);

		echo "<div class=\"ratinglabel\">";
		if ( $vote['count'] == 0 )
		{
			echo JText::_('COM_JTG_NOT_VOTED') . "\n";
		}
		else
		{
			echo JText::sprintf(
				'COM_JTG_TRACK_RATING',
				$JtgHelper->getLocatedFloat($vote['rate'],0),
				$vote['count']
				) . "\n";
		}
		echo "</div>\n<div id=\"ratingbox\">
		<ul id=\"1001\" class=\"rating " . $vote['class'] . "\">\n";


		for ($i = 1; $i <= 10; $i++)
		{
			$link = "index.php?option=com_jtg&controller=track&id=" . $this->track->id . "&task=vote&rate=" . $i . "#jtg_param_header_rating";
			$link = JRoute::_($link, false);
			echo "		<li id=\"" . $i . "\" class=\"rate " . $stars[$i] . "\">\n"
				. "			<a href=\"" . $link . "\" title=\"" . JText::_('COM_JTG_STARS_' . $i) . "\" rel=\"nofollow\">" . $i . "</a>\n"
				. "		</li>\n";
		}

		echo "	</ul>\n</div>";
	}
	else
	{
  		echo "<a name=\"jtg_param_header_rating\"></a>";
	}
?>

<div class="fb-share-button" data-href="<?php echo Uri::getInstance()->toString()?>" data-layout="button" data-size="small"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(Uri::getInstance()->toString())?>&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Share</a></div>
</div>


<div class="no-float"></div>

<?php

if ($this->track->description)
{
	echo $this->parseTemplate("headline", JText::_('COM_JTG_DESCRIPTION'), "jtg_param_header_description");
	echo $this->parseTemplate("description", JHTML::_('content.prepare', $this->track->description));
}
else
{
	echo '<a name="jtg_param_header_description"></a>';
}

$user = JFactory::getUser();
if ($this->canDo->get('jtg.download'))
{
	$download_buttons ='';
	echo "<div class=\"gps-info\"> <div class=\"block-header\">".
          JText::_('COM_JTG_DOWNLOAD')."</div>";
	if ( (bool) $this->params->get("jtg_param_offer_download_original") )
	{
		$ext = strtoupper(JFile::getExt($this->track->file));
		$download_buttons .= "<button class=\"btn btn-secondary jtg-download\" type=\"button\"
		onclick=\"document.getElementById('format').value = 'original';Joomla.submitbutton('download')\">
		$ext ". JText::_('COM_JTG_ORIGINAL_FILE') ."</button>";
	}

	if ( (bool) $this->params->get("jtg_param_offer_download_gpx") )
	{
		$download_buttons .= "<button class=\"btn btn-secondary jtg-download\" type=\"button\"
		onclick=\"document.getElementById('format').value = 'gpx';Joomla.submitbutton('download')\">
		GPX " . JText::_('COM_JTG_CONVERTED_FILE') ."</button>";
	}

	if ( (bool) $this->params->get("jtg_param_offer_download_kml") )
	{
		$download_buttons .= "<button class=\"btn btn-secondary jtg-download\" type=\"button\"
		onclick=\"document.getElementById('format').value = 'kml';Joomla.submitbutton('download')\">
		KML " . JText::_('COM_JTG_CONVERTED_FILE') . "</button>";
	}

	if ( (bool) $this->params->get("jtg_param_offer_download_tcx") )
	{
		$download_buttons .= "<button class=\"btn btn-secondary jtg-download\" type=\"button\"
		onclick=\"document.getElementById('format').value = 'tcx';Joomla.submitbutton('download')\">
		TCX " . JText::_('COM_JTG_CONVERTED_FILE') . "</button>";
	}
?>

<form name="adminForm" id="adminForm" method="post"
	action="<?php echo JRoute::_("index.php?option=com_jtg&controller=download&task=download"); ?>">

	<div class="block-text"> <label for="format"><?php echo JText::_('COM_JTG_DOWNLOAD_THIS_TRACK'); ?>&nbsp;</label>
    	<?php echo $download_buttons;?>
		<?php echo JHtml::_('form.token') . "\n"; ?>
	<input type="hidden" name="format" id="format" value="original" />
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="id" value="<?php echo $this->track->id; ?>" /> 
    <input type="hidden" name="task" value="" />
</div>
</form>
</div>

<?php
}

if ( ($durationbox) AND ($this->track->distance != "") AND ((float) $this->track->distance != 0))
    {
?>
						<div class="gps-info">
						<!-- <div id="count-box"> -->
							<div class="block-header">
							 <?php echo JText::_('COM_JTG_TIMECOUNT'); ?>
							</div>
						<?php if ($this->gpsTrack->speedDataExists)
						{ ?>
								<div class="block-text"> <label class="timecalc" for="pace"> <?php echo JText::_('COM_JTG_AVG_SPEED_FROM_PACE'); ?>
								</label> <input type="text" name="pace" id="pace" value=""
									size="4" />
								<?php echo '(' . JText::_('COM_JTG_PACE_UNIT_' . strtoupper($this->cfg->unit)) . ')'; ?>
							    <input type="button" name="button" class="btn btn-sm btn-secondary"
									value="<?php echo JText::_('JSUBMIT'); ?>"
									onclick="getAvgTimeFromPace(document.getElementById('pace').value,<?php echo $this->track->distance; ?>,
									<?php echo '\'' . JText::_('COM_JTG_SEPARATOR_DEC') . '\''; ?>);" /> 
								</div>
						<?php } ?>
							
								<div class="block-text"> <label class="timecalc" for="speed"><?php echo JText::_('COM_JTG_AVG_SPEED'); ?></label>
								 <input type="text" name="speed" id="speed" value=""
									size="4" />
								<?php echo '(' . JText::_('COM_JTG_SPEED_UNIT_' . strtoupper($this->cfg->unit)) . ')'; ?>
							
								<input type="button" name="button" class="btn btn-sm btn-secondary"
									value="<?php echo JText::_('JSUBMIT'); ?>"
									onclick="getAvgTime(document.getElementById('speed').value,<?php echo $this->track->distance; ?>,
									<?php echo '\'' . JText::_('COM_JTG_SEPARATOR_DEC') . '\''; ?>);" />
								</div>
									
								<div class="block-text">
								<label class="timecalc" for "time"><?php echo JText::_('COM_JTG_ESTIMATED_TIME'); ?></label>
							    <input type="text" name="time" id="time" value=""
									size="9" readonly="readonly" />
								</div>
						</div>
<?php
}

if (($this->imageList) AND ( $this->cfg->gallery != "none" ))
{
	echo $this->parseTemplate('headline', JText::_('COM_JTG_GALLERY'), 'jtg_param_header_gallery');
	echo "<div class=\"description\">";
	
	$imgurlpath=Uri::root() . "images/jtrackgallery/uploaded_tracks_images/track_" . $this->track->id . "/";
   switch ($this->cfg->gallery)
   {
		case 'jd2' :
         	JHTML::_('behavior.framework', true); // Load mootools
$document->addScript( Uri::root(true) . '/components/com_jtg/assets/js/jd.gallery.js');
               echo "<div id=\"myGallery\">";

               foreach ($imageList as $image)
               {
                  echo "  <div class=\"imageElement\"> <h3>" . $track->title . " <small>(" . $image->filename . ")</small></h3>
                  <p></p>
                  <img src=\"" . $imgurlpath . $image->filename . "\" class=\"full\" height=\"0px\" />
                  </div>\n";
               }

               echo "</div>\n";
               break;

            case 'highslide' :

               $document->addScript( Uri::root(true) . '/components/com_jtg/assets/highslide/highslide-with-gallery.packed.js');
               $document->addStyleSheet(Uri::root(true) . '/components/com_jtg/assets/highslide/highslide.css');

               // TODO This style sheet is not overridden.
              	echo "\n<div class=\"highslide-gallery\" style=\"width: auto; margin: auto\">\n";
               echo "\n<div class=\"jtg-photo-grid\">\n";
               $imgcount = count($this->imageList);

               foreach ($this->imageList as $image)
               {
                  if ($imgcount < 5)
                  {
                     $thumb = 'thumbs/thumb1_' . $image->filename;
                  }
 else
                  {
                     $thumb = 'thumbs/thumb2_' . $image->filename;
                  }

                  if ( ! JFile::exists(JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $this->track->id . '/' . $thumb) )
                  {
                     $thumb = $image->filename;
                  }
                  $title = $image->title;
                  if (strlen($title)==0) $title = $image->filename;
                  echo "  <div class=\"jtg-photo-item\"> <a class=\"highslide\" href='" . $imgurlpath . $image->filename . "' title=\"" . $title . "\" onclick=\"return hs.expand(this)\">
                     <img src=\"" . $imgurlpath . $thumb . "\" alt=\"$image->filename\"  /></a>
                     <div class=\"jtg-caption\">$image->title</div> <br>
                     </div>\n";
               }

               echo "</div></div>\n";
               break;

            case 'straight' :
               echo "<div class=\"jtg-photo-grid\">\n";
               foreach ($imageList as $image)
               {
                  echo "<div class=\"jtg-photo-item\"> <img src=\"" . $imgurlpath
                     . $image->filename . "\" alt=\"" . $image->filename . " (" . $image->filename . ")" . "\" title=\"" . $track->title
                     . "\" />\n <br>$image->title</div>";
               }
               echo "</div>\n";
               break;

            case 'ext_plugin':
               $gallery_folder = "jtrackgallery/uploaded_tracks_images/track_" . $this->track->id;
               $external_gallery = str_replace('%folder%', $gallery_folder, $cfg->gallery_code);
               echo JHTML::_('content.prepare', $external_gallery);
               break;

            default:
         }
			echo "</div>";
      }
else
{
	echo '<a name="jtg_param_header_gallery"></a>';
}

if ( $this->cfg->approach != 'no' )
{
	echo $this->parseTemplate("headline", JText::_('COM_JTG_APPROACH_SERVICE'), "jtg_param_header_approach");
	$description = "	<table id=\"approach\">
	<tr valign=\"top\">";

	switch ($this->cfg->approach)
	{
		case 'ors':
			$description .= $this->approach('ors');
			break;
		case 'cm':
			$description .= $this->approach('cm');
			break;
		case 'cmkey':
			$description .= $this->approach('cmkey');
			break;
		case 'easy':
			$description .= $this->approach('easy');
			break;
	}

	$description .= "		</tr>
	</table>\n";
	echo $this->parseTemplate("description", $description);
}
else
{
	echo "<a name=\"jtg_param_header_approach\"></a>";
}
// Approach END
?>
<div>
<?php
	if ( $user->id && $this->params->get("jtg_param_show_plugin_button") )
	{
?>
<button class="btn btn-primary" type="button" onclick="navigator.clipboard.writeText('<?php echo "{JTRACKGALLERYMAP} gpxfilename=".$this->track->file." {/JTRACKGALLERYMAP}";?>'); alert('<?php echo JText::sprintf('COM_JTG_SCRIPT_MESSAGE',$this->track->file);?>')"><?php echo JText::_('COM_JTG_SCRIPT_CLIPBOARD');?></button>

<?php
}
?>
<?php
	if ($this->canDo->get('core.edit') || ($this->canDo->get('core.edit.own') && $this->track->uid == $user->id)) { ?>
  <button class="btn btn-primary" type="button" onclick="location = '<?php echo JRoute::_("index.php?option=com_jtg&view=track&layout=form&id=$this->id"); ?>'">
    <?php echo JText::_('JACTION_EDIT'); ?>
  </button>
<?php
	}
	if ($this->canDo->get('core.delete')) {
?>
  <a href="<?php echo JRoute::_('index.php?option=com_jtg&controller=track&task=delete&id=').$this->id; ?>"
               onclick="return confirm('<?php echo JText::_('COM_JTG_VALIDATE_DELETE_TRACK')?>')">
  <button class="btn btn-danger" type="button">
    <?php echo JText::_('JACTION_DELETE'); ?>
  </button></a>
<?php
	}
?>
</div>
<?php
// Adding the comments
if ($this->cfg->comments == 1)
{
	echo $this->parseTemplate("headline", JText::_('COM_JTG_COMMENTS'), "jtg_param_header_comment");
	$comments = $this->model->getComments($this->id, $this->cfg->ordering);

	if (!$comments)
	{
		echo $this->parseTemplate("description",JText::_('COM_JTG_NO_COMMENTS_DESC'));
	}
	else
	{
		for ($i = 0, $n = count($comments); $i < $n; $i++)
		{
			$comment = $comments[$i];
			?>
<div class='comment'>
	<div class="comment-header">
		<div class="comment-title">
			<?php echo $i + 1 . ": " . $comment->title; ?>
		</div>
		<div class="date">
			<?php if ($comment->date != null) echo JHtml::_('date', $comment->date, JText::_('COM_JTG_DATE_FORMAT_LC4')); ?>
		</div>
		<div class="no-float"></div>
	</div>
	<div class="comment-autor">
		<?php echo $comment->user; ?>
		<br />
		<?php
		if (! empty($comment->email) ) {
			echo $this->model->parseEMailIcon($comment->email);
		}
		if ($comment->homepage)
		{
			echo ' ' . $this->model->parseHomepageIcon($comment->homepage);
		}
		?>
	</div>
	<div class="comment-text">
		<?php echo $comment->text; ?>
	</div>
	<div class="no-float"></div>
</div>
<?php
		}
	}
	//

	if ($this->canDo->get('jtg.comment'))
	{
?>
<script type=\"text/javascript\">
   Joomla.submitbutton = function(pressbutton)  {
   var form = document.adminForm;
   submitform( pressbutton);}
</script>
<?php
		echo $this->model->addcomment($this->cfg);
	}
	else
	{
		echo $this->parseTemplate('description',JText::_('COM_JTG_ADD_COMMENT_NOT_AUTH'));
	}
}
elseif ($this->cfg->comments == 3)
{
	$jcommentsfile = 'components/com_jcomments/jcomments.php';

	if ((JFile::exists(JPATH_SITE . '/' . $jcommentsfile)))
	{
		// 	global $mosConfig_absolute_path;
		require_once 'components/com_jcomments/jcomments.php';
		echo JComments::showComments($this->track->id, "com_jtg");
	}
	else
	{
		JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_ERROR_ACTIVATE_JCOMMENTS'), 'Error');
	}
}
else
{
	echo "<a name=\"jtg_param_header_comment\"></a>";
}
?>
<div style="display: none">
	<!-- load necessary pics in background -->
	<img src="///www.openlayers.org/api/img/cloud-popup-relative.png"
		alt="display:none" /> <img
		src="///www.openlayers.org/api/img/marker.png" alt="display:none" />
	<img src="///www.openlayers.org/api/theme/default/img/close.gif"
		alt="display:none" />
</div>
<?php
}
elseif ($maySeeSingleFile === false)
{
	echo '<p class="error">' . JText::_('COM_JTG_NOT_AUTH') . '</p>';
}
else
{
	echo '<p class="error">' . $maySeeSingleFile . '</p>';
}

echo $this->footer;

?>
