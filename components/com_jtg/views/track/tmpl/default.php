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

	$defaultlinecolor = "#000000";
	$charts_bg = $this->cfg->charts_bg? '#' . $this->cfg->charts_bg :"#ffffff";

	// $charts_linec is used for elevation
	$charts_linec = $this->cfg->charts_linec? '#' . $this->cfg->charts_linec: $defaultlinecolor;
	$charts_linec_speed = $this->cfg->charts_linec_speed? '#' . $this->cfg->charts_linec_speed: $defaultlinecolor;
	$charts_linec_pace = $this->cfg->charts_linec_pace? '#' . $this->cfg->charts_linec_pace: $defaultlinecolor;
	$charts_linec_heartbeat = $this->cfg->charts_linec_heartbeat? '#' . $this->cfg->charts_linec_heartbeat: $defaultlinecolor;

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

	if ( $this->params->get("jtg_param_hide_track_info") )
	{
		$gps_info = false;
	}
	else
	{
		$gps_info = true;
	}

	if ( $this->params->get("jtg_param_show_heightchart") AND $this->elevationDataExists)
	{
		$heightchart = true;
	}
	else
	{
		$heightchart = false;
	}

	$sortedcats = JtgModeljtg::getCatsData(true);
	if ( $this->params->get("jtg_param_show_speedchart") AND $this->speedDataExists )
	{
		$speedchart = true;
		$pacechart = $this->track->catid? $sortedcats[$this->track->catid]->usepace: 0;
	}
	else
	{
		$speedchart = false;
		$pacechart = false;
	}

	if ($this->params->get("jtg_param_show_speedchart") AND $this->beatDataExists )
	{
		$beatchart = true;
	}
	else
	{
		$beatchart = false;
	}

	$havechart = ($heightchart or $speedchart) or $beatchart;

	if ($havechart)
	{
		$axisnumber = 0;

		if ($heightchart)
		{
			// Heightchart is always on left (first) axis
			$heightchartaxis = $axisnumber + 1;
			$heightchartopposite = (($heightchartaxis & 1) == 0) ? 'true' : 'false';
			$axisnumber ++;
		}

		if ($speedchart)
		{
			if ($pacechart)
			{
				// Pace is on same axis but speed must be hidden
				$pacechartaxis = $axisnumber + 1;
				$pacechartopposite = (($pacechartaxis & 1) == 0) ? 'true' : 'false';
				$axisnumber ++;
				$speedcharthide = 1;
			}

			// Speedchart is on left (first) axis or on right axis when there is a heighchart
			$speedcharthide = 0;
			$speedchartaxis = $axisnumber + 1;
			$speedchartopposite = (($speedchartaxis & 1) == 0) ? 'true' : 'false';
			$axisnumber ++;
		}

		if ($beatchart)
		{
			// Beatchart is on left (first) axis or on right axis when there is a heighchart or a speed chart
			$beatchartaxis = $axisnumber + 1;
			$beatchartopposite = (($beatchartaxis & 1) == 0) ? 'true' : 'false';
			$axisnumber ++;
		}

		// Code support for joomla version greater than 3.0
		if (JVERSION >= 3.0)
		{
			JHtml::_('jquery.framework');
		}
		else
		{
			JHtml::script('jquery.js', 'components/com_jtg/assets/js/', false);
		}
?>

<!-- begin Charts -->

<script type="text/javascript">
	jQuery.noConflict();
</script>
<?php
   //$document->addScript("///code.highcharts.com/highcharts.js");
   //$document->addScript("///code.highcharts.com/modeules/highcharts.js");
   //$document->addScript(Uri::root(true) . '/components/com_jtg/assets/js/highcharts.js');
   $document->addScript('https://testing.gta-trek.eu/components/com_jtg/assets/js/highcharts.js');
?>
<script type="text/javascript">
		var isIE=0;
		if (navigator.appName == 'Microsoft Internet Explorer')
			isIE=1;
		(function($){ // encapsulate jQuery
		 $(function () {
			$('#elevation').highcharts({
			chart: {
				type: 'line',
				zoomType: 'xy',
				backgroundColor: '<?php echo $charts_bg; ?>'
			},
			 credits: {
			enabled: 'false'
			},
				plotOptions: {
				area: {
				stacking: 'normal',
				lineColor: '#FFFFFF',
				lineWidth: 1,
				marker: {
					lineWidth: 1,
					lineColor: '#FFFFFF'
				}
				},
				series: {
				fillOpacity: 0.1
				}
			},

			title: {
				text: null
			},
			xAxis: [{
				labels: {
					formatter: function() {
					return this.value + '<?php echo JText::_('COM_JTG_DISTANCE_UNIT_' . strtoupper($this->cfg->unit)); ?>';
				}
			},
				tooltip: {
				valueDecimals: 2,
				valueSuffix: '<?php echo JText::_('COM_JTG_DISTANCE_UNIT_' . strtoupper($this->cfg->unit)); ?>'

				}
			}],
			yAxis: [
			<?php if ($heightchart)
			{
			?>
			{ // Elevation data
				labels: {
					formatter: function() {
					return this.value +'<?php echo JText::_('COM_JTG_ELEVATION_UNIT'); ?>';
				},
				style: {
					color: '<?php echo $charts_linec; ?>'
				}
				},
				title: {
				text: '<?php echo JText::_('COM_JTG_ELEVATION') . '(' . JText::_('COM_JTG_ELEVATION_UNIT'); ?>)',
				style: {
					color: '<?php echo $charts_linec; ?>'
				}
				}
				,opposite: <?php echo  $heightchartopposite; ?>
			}
<?php
}

if ($pacechart)
{
?>
			, { // Pace data
				gridLineWidth: 0,
				title: {
				text: '<?php echo JText::_('COM_JTG_PACE') . '(' . JText::_('COM_JTG_PACE_UNIT_' . strtoupper($this->cfg->unit)); ?>)',
				style: {
					color: '<?php echo $charts_linec_pace; ?>'
				}
				},
				labels: {
				formatter: function() {
					return this.value +'<?php echo JText::_('COM_JTG_PACE_UNIT_' . strtoupper($this->cfg->unit)); ?>';
				},
				style: {
					color: '<?php echo $charts_linec_pace; ?>'
				}
				}
				,opposite: <?php echo $pacechartopposite; ?>
			}
<?php
}

if ($speedchart)
{
?>
			, { // Speed data
				gridLineWidth: 0,
				title: {
				text: '<?php echo JText::_('COM_JTG_SPEED') . '(' . JText::_('COM_JTG_SPEED_UNIT_' . strtoupper($this->cfg->unit)); ?>)',
				style: {
					color: '<?php echo $charts_linec_speed; ?>'
				}
				},
				labels: {
				formatter: function() {
					return this.value +'<?php echo JText::_('COM_JTG_SPEED_UNIT_' . strtoupper($this->cfg->unit)); ?>';
				},
				style: {
					color: '<?php echo $charts_linec_speed; ?>'
				}
				}
				,opposite: <?php echo $speedchartopposite; ?>
			}
<?php
}

if ($beatchart)
{
?>
				,{ // Heart beat data
				gridLineWidth: 0,
				title: {
				text: '<?php echo JText::_('COM_JTG_HEARTFREQU') . '(' . JText::_('COM_JTG_HEARTFREQU_UNIT'); ?>)',
				style: {
					color: '<?php echo $charts_linec_heartbeart; ?>'
				}
				},
				labels: {
				formatter: function() {
					return this.value +'<?php echo JText::_('COM_JTG_HEARTFREQU_UNIT'); ?>'
				},
				style: {
					color: '<?php echo $charts_linec_heartbeart; ?>'
				}
				}
				,opposite: <?php echo $beatchartopposite; ?>
			}
<?php
}
?>
			],
<?php
// If AnimatedCursorLayer is enabled
// jtg_param_use_map_autocentering
$autocenter = (bool) $this->params->get("jtg_param_use_map_autocentering", true) ? 'true':'false';
if (! (bool) $this->params->get("jtg_param_disable_map_animated_cursor", false))
{
?>
			plotOptions: {
				series: {
					point: {
						events: {
							mouseOver: function () {
								var index = this.series.processedXData.indexOf(this.x);
								hover_profil_graph(longitudeData[index],latitudeData[index], index, <?php echo $autocenter ?>);
							}
						}
					},
					events: {
						mouseOut: function () {
						out_profil_graph();
						}
					}
				}
			},
<?php
}
?>
			tooltip: {
			valueDecimals: 2,
			formatter: function() {
			var s = '<b><?php echo JText::_('COM_JTG_DISTANCE'); ?>: '
				+ this.x
				+' <?php echo JText::_('COM_JTG_DISTANCE_UNIT_' . strtoupper($this->cfg->unit)); ?></b>';
			$.each(this.points, function(i, point) {
				s += '<br/>'+ point.series.name +': '+
				point.y + ' ' + point.series.options.unit;
			});
			return s;
			},
			shared: true
			},
			legend: {
				layout: 'vertical',
				align: 'left',
				x: 120,
				verticalAlign: 'top',
				y: 0,
				floating: true,
				labelFormatter: function() {
				return this.name <?php echo $axisnumber > 1? "+ ' (" . JText::_('COM_JTG_CLICK_TO_HIDE') . ")'": ''; ?>;
				}
			},
			series: [
<?php
if ($heightchart)
{
?>
				{
				name: '<?php echo JText::_('COM_JTG_ELEVATION'); ?>',
				unit: 'm',
				color: '<?php echo $charts_linec; ?>',
				yAxis: <?php echo $heightchartaxis - 1; ?>,
				data: <?php echo $this->heighdata; ?>,
				marker: {
				enabled: false
				},
				tooltip: {
				valueSuffix: ' m'
				}

			}
<?php
}

if ($pacechart)
{
	?>
				, {
				name: '<?php echo JText::_('COM_JTG_PACE'); ?>',
				unit:'<?php echo JText::_('COM_JTG_PACE_UNIT_' . strtoupper($this->cfg->unit)); ?>',
				color: '<?php echo $charts_linec_pace; ?>',
				yAxis: <?php echo $pacechartaxis - 1; ?>,
				data: <?php echo $this->pacedata; ?>,
				marker: {
				enabled: false
				},
				tooltip: {
				valueSuffix: ' <?php echo JText::_('COM_JTG_PACE_UNIT_' . strtoupper($this->cfg->unit)); ?>'
				}

			}
<?php
}

if ($speedchart)
{
?>
				, {
				name: '<?php echo JText::_('COM_JTG_SPEED'); ?>',
				unit:'<?php echo JText::_('COM_JTG_SPEED_UNIT_' . strtoupper($this->cfg->unit)); ?>',
				color: '<?php echo $charts_linec_speed; ?>',
				yAxis: <?php echo $speedchartaxis - 1; ?>,
				data: <?php echo $this->speeddata; ?>,
				visible: <?php echo $pacechart? 'false': 'true'; ?>,
				marker: {
				enabled: false
				},
				tooltip: {
				valueSuffix: ' <?php echo JText::_('COM_JTG_SPEED_UNIT_' . strtoupper($this->cfg->unit)); ?>'
				}

			}
<?php
}

if ($beatchart)
{
?>
				, {
				name: '<?php echo JText::_('COM_JTG_HEARTFREQU'); ?>',
				unit: '<?php echo JText::_('COM_JTG_HEARTFREQU_UNIT'); ?>',
				color: '<?php echo $charts_linec_heartbeart; ?>',
				yAxis: <?php echo $heartbeatchartaxis; ?>,
				data: <?php echo $this->beatdata; ?>,
				tooltip: {
				valueSuffix: '<?php echo JText::_('COM_JTG_HEARTFREQU_UNIT'); ?>'
				}
			}
<?php
}
?>
			]
			});
		});
		})(jQuery);
		</script>
<!-- end Charts -->

	<?php
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
<center><div id="jtg_map" class="olMap"></div><br /></center>
	<div id="popup" class="ol-popup">
   <a href="#" id="popup-closer" class="ol-popup-closer"></a>
   <div id="popup-content"></div>
</div>
<?php
	if ($havechart)
	{
	?>
    <div id="profile" style="width:<?php echo $this->cfg->charts_width; ?>;" >
	<div class="profile-img" id="elevation" style="width:<?php echo $this->cfg->charts_width; ?>
		; height: <?php echo $this->cfg->charts_height; ?>;"></div>
	</div>
	<?php
	}

	if ($gps_info){
	?>
	<div class="description"></div>
	<div class="gps-info-cont">
	    <div class="block-header"><?php echo JText::_('COM_JTG_DETAILS');?></div>
		<div class="gps-info"><table class="gps-info-tab">

<?php
if ( ($this->track->distance != "") AND ((float) $this->track->distance != 0) )
{
?>
			<tr>
				<td><?php echo JText::_('COM_JTG_DISTANCE'); ?>:</td>
				<td><?php echo JtgHelper::getFormattedDistance($this->distance, $this->cfg->unit); ?></td>
			</tr>
<?php
}

if ($this->track->ele_asc)
{
?>
			<tr>
				<td><?php echo JText::_('COM_JTG_ELEVATION_UP'); ?>:</td>
				<td><?php
					echo $this->track->ele_asc;
					echo ' ' . JText::_('COM_JTG_METERS');
					?>
				</td>
			</tr>
<?php
}

if ($this->track->ele_desc)
{
?>
			<tr>
				<td><?php echo JText::_('COM_JTG_ELEVATION_DOWN'); ?>:</td>	
				<td><?php echo $this->track->ele_desc; ?>
			    	<?php echo ' ' . JText::_('COM_JTG_METERS'); ?>
					</td>
			</tr>
<?php
}
?>

		<?php if ( $this->track->level != "0" )
		{
		?>
			<tr>
				<td><?php echo JText::_('COM_JTG_LEVEL'); ?>:</td>
				<td><?php echo $this->model->getLevel($this->track->level); ?></td>
			</tr>
<?php
} ?>
	 		<tr>
				<td><?php echo JText::_('COM_JTG_CATS'); ?>:</td>
				<td colspan="2"><?php
				echo $JtgHelper->parseMoreCats($sortedcats, $this->track->catid, "TrackDetails", true);
				?>
				</td>
			</tr>
<?php
if (! $this->params->get("jtg_param_disable_terrains"))
{
	// Terrain description is enabled
	if ($this->track->terrain)
	{
		$terrain = $this->track->terrain;
		$terrain = explode(',', $terrain);
		$newterrain = array();

		foreach ($terrain as $t)
		{
			$t = $this->model->getTerrain(' WHERE id=' . $t);

			if ( ( isset($t[0])) AND ( $t[0]->published == 1 ) )
			{
				$newterrain[] = $t[0]->title;
			}
		}

		$terrain = implode(', ', $newterrain);
		//echo $this->parseTemplate('headline', JText::_('COM_JTG_TERRAIN'), "jtg_param_header_terrain");
		//echo $this->parseTemplate('description', $terrain);
		echo "<tr><td>".JText::_('COM_JTG_TERRAIN')."</td><td>".$terrain."</td></tr>";
	}
	else
	{
		echo "<a name=\"jtg_param_header_terrain\"></a>";
	}
}
?>
		</table>
		</div>
		<div class="gps-info">
		<table class="gps-info-tab">
				<tr>
					<td><?php echo JText::_('COM_JTG_UPLOADER'); ?>:</td>
					<td><?php echo JtgHelper::getProfileLink($this->track->uid, $this->track->user); ?></td>
				</tr>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JTG_DATE'); ?>:</td>
					<td><?php echo $this->date; ?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_JTG_HITS'); ?>:</td>
					<td><?php echo $this->track->hits; ?></td>
				</tr>

				
		</table>
	<?php
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
		$template = "<div id=\"ratingbox\">
			<ul id=\"1001\" class=\"rating " . $vote['class'] . "\">\n";


	for ($i = 1; $i <= 10; $i++)
	{
		$link = "index.php?option=com_jtg&controller=track&id=" . $this->track->id . "&task=vote&rate=" . $i . "#jtg_param_header_rating";
		$link = JRoute::_($link, false);
		$template .= "		<li id=\"" . $i . "\" class=\"rate " . $stars[$i] . "\">\n"
		. "			<a href=\"" . $link . "\" title=\"" . JText::_('COM_JTG_STARS_' . $i) . "\" rel=\"nofollow\">" . $i . "</a>\n"
		. "		</li>\n";
	}

	$template .= "	</ul>\n";

	if ( $vote['count'] == 0 )
	{
		$template .= JText::_('COM_JTG_NOT_VOTED') . "\n";
	}
	else
	{
		$template .= JText::sprintf(
				'COM_JTG_TRACK_RATING',
				$JtgHelper->getLocatedFloat($vote['rate'],0),
				$vote['count']
				) . "\n";
	}

	//echo $this->parseTemplate("description", $template);
	echo $template."</div>";
    }
	else
    {
    	echo "<a name=\"jtg_param_header_rating\"></a>";
    }

	?>
	</div>

    <div class = "block-text">
	    <?php echo JText::_('COM_JTG_BIG_MAP') ?>:
			<a rel="width[1000];height[700];" class="jcebox"
				href="///maps.google.com/maps?q=<?php echo $this->track->start_n . "," . $this->track->start_e; ?>"
				target="_blank">Google</a>, 
			<a rel="width[1000];height[700];"
				class="jcebox"
				href="///openstreetmap.org/?mlat=<?php echo $this->track->start_n . "&amp;mlon=" . $this->track->start_e; ?>"
				target="_blank">OpenStreetMap</a>,
			<a
				rel="width[1000];height[700];" class="jcebox"
				href="///www.geocaching.com/map/default.aspx?lat=<?php echo $this->track->start_n . "&amp;lng=" . $this->track->start_e; ?>"
				target="_blank">Geocaching.com</a>
	</div>
<div class="fb-share-button" data-href="<?php echo Uri::getInstance()->toString()?>" data-layout="button" data-size="small"><a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(Uri::getInstance()->toString())?>&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore">Share</a></div>
	</div>

	<div class="no-float"></div>
<?php } // end if $gps_info
?>

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
	// Registered users only
	// TODO: print text if download only authorised for some users?
	//echo $this->parseTemplate("description", JText::_('COM_JTG_NOT_DOWNLOAD'));
	if ( (bool) $this->params->get("jtg_param_offer_download_original") )
	{
		$ext = JFile::getExt($this->track->file);
		$download_buttons .= "<button class=\"button\" type=\"button\"
		onclick=\"document.getElementById('format').value = 'original';Joomla.submitbutton('download')\">
		$ext ". JText::_('COM_JTG_ORIGINAL_FILE') ."</button>";
	}

	if ( (bool) $this->params->get("jtg_param_offer_download_gpx") )
	{
		$download_buttons .= "<button class=\"button\" type=\"button\"
		onclick=\"document.getElementById('format').value = 'gpx';Joomla.submitbutton('download')\">
		GPX " . JText::_('COM_JTG_CONVERTED_FILE') ."</button>";
	}

	if ( (bool) $this->params->get("jtg_param_offer_download_kml") )
	{
		$download_buttons .= "<button class=\"button\" type=\"button\"
		onclick=\"document.getElementById('format').value = 'kml';Joomla.submitbutton('download')\">
		KML " . JText::_('COM_JTG_CONVERTED_FILE') . "</button>";
	}

	if ( (bool) $this->params->get("jtg_param_offer_download_tcx") )
	{
		$download_buttons .= "<button class=\"button\" type=\"button\"
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
						<?php if ($pacechart)
						{ ?>
								<div class="block-text"> <label class="timecalc" for="pace"> <?php echo JText::_('COM_JTG_AVG_SPEED_FROM_PACE'); ?>
								</label> <input type="text" name="pace" id="pace" value=""
									size="4" />
								<?php echo '(' . JText::_('COM_JTG_PACE_UNIT_' . strtoupper($this->cfg->unit)) . ')'; ?>
							    <input type="button" name="button" class="button"
									value="<?php echo JText::_('JSUBMIT'); ?>"
									onclick="getAvgTimeFromPace(document.getElementById('pace').value,<?php echo $this->distance_float; ?>,
									<?php echo '\'' . JText::_('COM_JTG_SEPARATOR_DEC') . '\''; ?>);" /> 
								</div>
						<?php } ?>
							
								<div class="block-text"> <label class="timecalc" for="speed"><?php echo JText::_('COM_JTG_AVG_SPEED'); ?></label>
								 <input type="text" name="speed" id="speed" value=""
									size="4" />
								<?php echo '(' . JText::_('COM_JTG_SPEED_UNIT_' . strtoupper($this->cfg->unit)) . ')'; ?>
							
								<input type="button" name="button" class="button"
									value="<?php echo JText::_('JSUBMIT'); ?>"
									onclick="getAvgTime(document.getElementById('speed').value,<?php echo $this->distance_float; ?>,
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
	if ($this->canDo->get('core.edit') || ($this->canDo->get('core.edit.own') && $this->track->uid == $user->id)) { ?>
  <button class="button" type="button" onclick="location = '<?php echo JRoute::_("index.php?option=com_jtg&view=track&layout=form&id=$this->id"); ?>'">
    <?php echo JText::_('JACTION_EDIT'); ?>
  </button>
<?php
	}
	if ($this->canDo->get('core.delete')) {
?>
  <a href="<?php echo JRoute::_('index.php?option=com_jtg&controller=track&task=delete&id=').$this->id; ?>"
               onclick="return confirm('<?php echo JText::_('COM_JTG_VALIDATE_DELETE_TRACK')?>')">
  <button class="button" type="button">
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

echo $this->mapJS;
echo $this->footer;

?>
