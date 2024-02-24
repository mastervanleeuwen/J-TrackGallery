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
$this->get('State'); // have to get state before we can set state variables
$this->getModel()->setState('list.limit','0'); // show all tracks
$this->items = $this->get('Items');
$this->filterForm = $this->get('FilterForm');

if (!$this->params->get('jtg_param_use_cats')) $this->filterForm->removeField('trackcat','filter');
if (!$this->cfg->uselevel) $this->filterForm->removeField('tracklevel','filter');

echo $this->lh;

echo "\n<script>\n";
echo "  DPCalLocs = ".JtgMapHelper::parseDPCalLocations($this->dpcallocs);
echo "\n  DPCalIconFile = '/components/com_jtg/assets/images/orange-dot.png';\n";
echo "</script>\n";

JFactory::getDocument()->addScript(JUri::root(true) . '/components/com_jtg/assets/js/geolocation.js',array('version'=>'auto'));
JFactory::getDocument()->addScript(JUri::root(true) . '/components/com_jtg/assets/js/jtgOverView.js',array('version'=>'auto'));
JFactory::getDocument()->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons'); // For geolocation/center icon
?>

<style type="text/css">
#jtg_map.olMap {
	height: <?php echo$this->cfg->map_height; ?>;
	width: <?php echo$this->cfg->map_width; ?>;
	z-index: 0;
}

.olButton::before{
	display: none;
}
#jtg_map.fullscreen {
	height: 800px;
	width: 100%;
	z-index: 10000;
}
/* Fix Joomla3 max-width=100% breaks popups
   Fix Yooytheme theme.css also breaks popups */
#jtg_map canvas,img{
	max-width: none;
}

/* Fix Bootstrap-Openlayers issue */
.olMap img { max-width: none !important;
}

.olPopup img { max-width: none ! important; }

img.olTileImage {
	max-width: none !important;
}
</style>
<center>
<?php
if (count($this->items))
{
?>
	<div id="jtg_map" class="olMap"></div>
	<div id="popup" class="ol-popup">
		<a href="#" id="popup-closer" class="ol-popup-closer"></a>
		<div id="popup-content"></div>
	</div>
<?php
}
else
{
	echo "<div>".JText::_('COM_JTG_LIST_NO_TRACK')."</div>";
}
?>
	<div>
		<br>
		<div id="geo-msg"></div>
		<form action="<?php echo JURI::getInstance(); ?>" method="post"
			name="adminForm" id="adminForm">
<?php
	if ($this->params->get('jtg_param_overview_filterbox', 1)) {
?> 
	<div class="row-fluid">
		<div class="span12">
		<?php
			echo JLayoutHelper::render(
				'joomla.searchtools.default',
				array('view' => $this)
			);
		?>
		</div>
	</div>
<?php
	}
?>
</form>
</center>
<?php

// Karten-Auswahl END
if ($this->newest != 0)
{
	?>

<div class="<?php echo $this->toptracks; ?>">
	<div class="<?php echo $this->toptracks; ?>_title">
		<span class="headline"> <a href="#TT_newest"><?php
		echo JText::_('COM_JTG_NEWEST');
		?> </a>
		</span>
		<ul class="title">
			<li>
				<div class="list-left">
					<b><?php echo JText::_('COM_JTG_TITLE'); ?> </b>
				</div>
				<div class="list-right">
					<b><?php echo JText::_('COM_JTG_CAT'); ?> </b>
				</div>
				<div class="no-float"></div>
			</li>
		</ul>
	</div>
	<div class="<?php echo $this->toptracks; ?>_entry">
		<ul class="entry">
			<?php
			if ( count($this->newest) == 0 )
			{
				echo JText::_('COM_JTG_NOENTRY');
			}
			else
			{
				foreach ($this->newest as $new)
				{
					$link = JRoute::_('index.php?option=com_jtg&view=track&id=' . $new->id);
					?>
			<li>
				<div class="list-left">
					<a title="<?php echo $this->boxlinktext[$new->access]; ?>"
						class="access_<?php echo $new->access; ?>"
						href="<?php echo $link; ?>"><?php

						if ($new->title != "")
						{
							echo htmlentities($new->title, ENT_QUOTES, "UTF-8");
						}
						else
						{
							echo '<i>' . JText::_('COM_JTG_NO_TITLE') . '</i>';
						}
						?> </a>
				</div>
				<div class="list-right">
					<?php echo JtgHelper::parseMoreCats($this->sortedcats, $new->catid, "box", true); ?>
				</div>
				<div class="no-float"></div>
			</li>
			<?php
				}
			}
			?>
		</ul>
	</div>
</div>
<?php
}

if ($this->hits != 0)
{
	?>
<div class="<?php echo $this->toptracks; ?>">
	<div class="<?php echo $this->toptracks; ?>_title">
		<span class="headline"> <a href="#TT_hits"><?php
		echo JText::_('COM_JTG_MOSTHITS');
		?> </a>
		</span>
		<ul class="title">
			<li>
				<div class="list-left">
					<b><?php echo JText::_('COM_JTG_TITLE'); ?> </b>
				</div>
				<div class="list-right">
					<b><?php echo JText::_('COM_JTG_HITS'); ?> </b>
				</div>
				<div class="no-float"></div>
			</li>
		</ul>
	</div>
	<div class="<?php echo $this->toptracks; ?>_entry">
		<ul class="entry">
			<?php

			if ( count($this->hits) == 0 )
			{
				echo JText::_('COM_JTG_NOENTRY');
			}
			else
			{
				foreach ($this->hits as $hits)
				{
					$link = JRoute::_('index.php?option=com_jtg&view=track&id=' . $hits->id);
					?>
			<li>
				<div class="list-left">
					<a title="<?php echo $this->boxlinktext[$hits->access]; ?>"
						class="access_<?php echo $hits->access; ?>"
						href="<?php echo $link; ?>"><?php

						if ($hits->title != "")
						{
							echo htmlentities($hits->title, ENT_QUOTES, 'UTF-8');
						}
						else
						{
							echo '<i>' . JText::_('COM_JTG_NO_TITLE') . '</i>';
						}
						?> </a>
				</div>
				<div class="list-right">
					<?php
					echo JtgHelper::getLocatedFloat($hits->hits,0);
					?>
				</div>
				<div class="no-float"></div>
			</li>
			<?php
				}
			}
			?>
		</ul>
	</div>
</div>
<?php
}

if ($this->best != 0)
{
	?>
<div class="<?php echo $this->toptracks; ?>">
	<div class="<?php echo $this->toptracks; ?>_title">
		<span class="headline"> <a href="#TT_best"><?php
		echo JText::_('COM_JTG_MOSTVOTES');
		?> </a>
		</span>
		<ul class="title">
			<li>
				<div class="list-left">
					<b><?php echo JText::_('COM_JTG_TITLE'); ?> </b>
				</div>
				<div class="list-right">
					<b><?php echo JText::_('COM_JTG_STARS'); ?> </b>
				</div>
				<div class="no-float"></div>
			</li>
		</ul>
	</div>
	<div class="<?php echo $this->toptracks; ?>_entry">
		<ul class="entry">
			<?php

			if ( count($this->best[1]) == 0 )
			{
				echo JText::_('COM_JTG_NOENTRY');
			}
			else
			{
				foreach ($this->best[1] as $best)
				{
					$link = JRoute::_('index.php?option=com_jtg&view=track&id=' . $best->id);
					?>
			<li>
				<div class="list-left">
					<a title="<?php echo $this->boxlinktext[$best->access]; ?>"
						class="access_<?php echo $best->access; ?>"
						href="<?php echo $link; ?>"><?php

						if ($best->title != "")
						{
							echo htmlentities($best->title, ENT_QUOTES, "UTF-8");
						}
						else
						{
							echo '<i>' . JText::_('COM_JTG_NO_TITLE') . '</i>';
						}
						?> </a>
				</div>
				<div class="list-right">
					<?php
					$stars_int = JtgHelper::getLocatedFloat($best->vote, 0);
					$stars_float = $best->vote;
					$stars_float2 = JtgHelper::getLocatedFloat($best->vote, 2);

					if ( $stars_float == 0 )
					{
						$title = JText::_('COM_JTG_NOT_VOTED');
					}
					elseif ( $best->vote == 1 )
					{
						$title = "1 " . JText::_('COM_JTG_STAR');
					}
					else
					{
						$title = $stars_float2 . " " . JText::_('COM_JTG_STARS');
					}

					if ($this->best[0])
					{
						// Picture
						echo "<div title='" . $title . "'><ul class=\"rating " . $this->best[2][$stars_int] . "\"><li></li></ul></div>";
					}
					else
					{
						// Float
						echo "<a title='" . $title . "'>" . $stars_int . "</a>";
					}
					?>
				</div>
				<div class="no-float"></div>
			</li>
			<?php
				}
			}
			?>
		</ul>
	</div>
</div>
<?php
}

if ($this->rand != 0)
{
	?>
<div class="<?php echo $this->toptracks; ?>">
	<div class="<?php echo $this->toptracks; ?>_title">
		<span class="headline"> <a href="#TT_rand"><?php
		echo JText::_('COM_JTG_RANDOM_TRACKS');
		?> </a>
		</span>
		<ul class="title">
			<li>
				<div class="list-left">
					<b><?php echo JText::_('COM_JTG_TITLE'); ?> </b>
				</div>
				<div class="list-right">
					<b><?php echo JText::_('COM_JTG_CAT'); ?> </b>
				</div>
				<div class="no-float"></div>
			</li>
		</ul>
	</div>
	<div class="<?php echo $this->toptracks; ?>_entry">
		<ul class="entry">
			<?php

			if ( count($this->rand) == 0 )
			{
				echo JText::_('COM_JTG_NOENTRY');
			}
			else
			{
				foreach ($this->rand as $rand)
				{
					$link = JRoute::_('index.php?option=com_jtg&view=track&id=' . $rand->id);
					?>
			<li>
				<div class="list-left">
					<a title="<?php echo $this->boxlinktext[$rand->access]; ?>"
						class="access_<?php echo $rand->access; ?>"
						href="<?php echo $link; ?>"><?php

						if ($rand->title != "")
						{
							echo htmlentities($rand->title, ENT_QUOTES, "UTF-8");
						}
						else
						{
							echo '<i>' . JText::_('COM_JTG_NO_TITLE') . '</i>';
						}
						?> </a>
				</div>
				<div class="list-right">
					<?php
					// Echo $rand->cat;
					echo JtgHelper::parseMoreCats($this->sortedcats, $rand->catid, "box", true);
					?>
				</div>
				<div class="no-float"></div>
			</li>
			<?php
				}
			}
			?>
		</ul>
	</div>
</div>
<?php
}

?>
<div class="no-float">
<?php
	if (count($this->items))
	{
		echo JtgMapHelper::parseOverviewMapJS($this->items,0,$this->showtracks,$this->zoomlevel,JFactory::getApplication()->input->get('lon'),JFactory::getApplication()->input->get('lat'),JFactory::getApplication()->input->getBool('geoloc'));
	}
	echo $this->footer;
?>
</div>
