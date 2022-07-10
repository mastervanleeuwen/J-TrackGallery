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
JToolBarHelper::title(JText::_('COM_JTG_CONFIG'), 'generic.png');
JToolBarHelper::back();
JToolBarHelper::save('saveconfig', $alt = 'COM_JTG_SAVE', 'save.png');
JToolBarHelper::preferences('com_jtg', 600, 800, $alt = 'COM_JTG_MENU_DEFAULT_SETTINGS');
JToolBarHelper::help('config/default', true);
if (JVERSION < 4.0)
{
	JHtml::_('behavior.tooltip');
}
?>
<form action="" method="post" name="adminForm" id="adminForm"
	class="adminForm">
	<?php
	$document = JFactory::getDocument();
	$style = '
	dt.tabs h3
	{
	float:left;
	margin: 0;
	margin-right: 10px;
}
div.current {
clear: both;
}
dl.tabs {
float: left;
margin: 10px 0 -1px 0;
z-index: 50;
}

dl.tabs dt {
float: left;
padding: 4px 10px;
border: 1px solid #ccc;
margin-left: 3px;
background: #e9e9e9;
color: #666;
}

dl.tabs dt.open {
background: #F9F9F9;
border-bottom: 1px solid #f9f9f9;
z-index: 100;
color: #000;
}

div.current {
clear: both;
border: 1px solid #ccc;
padding: 10px 10px;
}

div.current dd {
padding: 0;
margin: 0;
}
dl.tabs h3{
font-size:1.0em;
}
dl#content-pane.tabs {
margin: 1px 0 0 0;
}
';
	$document->addStyleDeclaration($style);
	$options = array(
			'active' => 'mainconfig',
			'onActive' => 'function(title, description){
			description.setStyle("display", "block");
			title.addClass("open").removeClass("closed");
}',
			'onBackground' => 'function(title, description){
			description.setStyle("display", "none");
			title.addClass("closed").removeClass("open");
}',
			'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
			'useCookie' => true, // This must not be a string. Don't use quotes.
	);

	// Configuration BEGIN

	echo JHtmlBootstrap::startTabSet('tabs', $options);
	echo JHtmlBootstrap::addTab('tabs', 'mainconfig', JText::_('COM_JTG_MAINCONF'));
	?>
	<table class="admintable">
		<tbody>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_USE_ACCESS_LEVEL') . '">' . JText::_('COM_JTG_USE_ACCESS_LEVEL') . '</span>'; ?>
				</td>
				<td><?php echo $this->lists['access']; ?></td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_TERMS_IN_USE') . '">' . JText::_('COM_JTG_TERMS_IN_USE') . '</span>'; ?>
				</td>
				<td><select name="terms" size="1">
						<option value="1"
						<?php echo $this->config->terms == "1" ? 'selected=selected': ''; ?>>
							<?php echo JText::_('JYES'); ?>
						</option>
						<option value="0"
						<?php echo $this->config->terms == "0" ? 'selected=selected': ''; ?>>
							<?php echo JText::_('JNO'); ?>
						</option>
				</select>
				</td>
			</tr>
			<?php

			if ($this->config->terms == "1")
			{
				?>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_TERMS') . '">' . JText::_('COM_JTG_TERMS') . '</span>'; ?>
				</td>
				<td><?php echo $this->lists['content']; ?></td>
			</tr>
			<?php
			}
			?>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE') . '::'
					. JText::_('COM_JTG_TT_PROFILEEXT') . '">' . JText::_('COM_JTG_PROFILEEXT') . '</span>'; ?>
				</td>
				<td><select name="profile" size="1">
						<option value="0"
						<?php echo $this->config->profile == "0"? 'selected=selected': ''; ?>>
							<?php echo JText::_('COM_JTG_NO_PROFILE'); ?>
						</option>
						<option value="cb"
						<?php echo $this->config->profile == "cb"? 'selected=selected': ''; ?>>Community
							Builder</option>
						<option value="js"
						<?php echo $this->config->profile == "js"? 'selected=selected': ''; ?>>JomSocial</option>
						<option value="ku"
						<?php echo $this->config->profile == "ku"? 'selected=selected': ''; ?>>Kunena</option>
				</select>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE') . '::'
					. JText::_('COM_JTG_TT_VOTE') . '">' . JText::_('COM_JTG_VOTE') . '</span>'; ?>
				 </td>
				<td><?php echo $this->lists['usevote']; ?></td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE') . '::'
					. JText::_('COM_JTG_TT_APPROACH') . '">' . JText::_('COM_JTG_APPROACH') . '</span>'; ?>
				</td>
				<td><?php echo $this->lists['approach']; ?></td>
			</tr>
		</tbody>
	</table>
	&nbsp;
	<?php
	echo JHtmlBootstrap::endTab();
	// Configuration END

	// Level BEGIN
	echo JHtmlBootstrap::addTab('tabs', 'levelconfig', JText::_('COM_JTG_LEVEL'));
	?>
	<table class="admintable">
		<tbody>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_LEVELCONF') . '">' . JText::_('COM_JTG_LEVEL') . '</span>'; ?>
				</td>
				<td valign="top"><?php echo JText::_('COM_JTG_LEVELCONF_FIRST') . "<br />" . $this->lists['level']; ?>
				</td>
				<td valign="top"><?php echo JText::_('COM_JTG_LEVEL_TRANSLATION') . ":<br />" . $this->lists['translevel']; ?>
				</td>
			</tr>
		</tbody>
	</table>
	&nbsp;
	<?php
	echo '<br /><br />' . JText::_('COM_JTG_LEVELCONF_HELP') . '<br />';
	echo JHtmlBootstrap::endTab();
	// Level END
	echo JHtmlBootstrap::addTab('tabs', 'maps', JText::_('COM_JTG_MAPS'));

	?>
	<table class="admintable">
		<tbody>
			<tr>
				<td><?php echo JText::_('COM_JTG_UNIT'); ?></td>
				<td>&nbsp;&nbsp;<?php echo $this->lists['unit']; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_MAPWIDTH'); ?></td>
				<td>&nbsp;&nbsp;<input type="text" name="map_width"
					value="<?php echo $this->config->map_width; ?>" size="20" />
					<?php echo JText::_('COM_JTG_PX_OR_PERCENT'); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_MAPHEIGHT'); ?></td>
				<td>&nbsp;&nbsp;<input type="text" name="map_height"
					value="<?php echo $this->config->map_height; ?>" size="20" />
					<?php echo JText::_('COM_JTG_PX_OR_PERCENT'); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_MAX_TRKPT_DISPLAY'); ?></td>
				<td>&nbsp;&nbsp;<input type="text" name="maxTrkptDisplay"
					value="<?php echo $this->config->maxTrkptDisplay; ?>" size="20" />
					<?php echo JText::_('COM_JTG_MAX_TRKPT_DISPLAY2'); ?>
				</td>
			</tr>
			<tr>
				<td colspan="2"><b><?php echo JText::_('COM_JTG_CHARTS_PROFILE'); ?>
				</b></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_CHARTWIDTH'); ?></td>
				<td>&nbsp;&nbsp;<input type="text" name="charts_width"
					value="<?php echo $this->config->charts_width; ?>" size="20" />
					<?php echo JText::_('COM_JTG_PX_OR_PERCENT'); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_CHARTHEIGTH'); ?></td>
				<td>&nbsp;&nbsp;<input type="text" name="charts_height"
					value="<?php echo $this->config->charts_height; ?>" size="20" />
					<?php echo JText::_('COM_JTG_PX_OR_PERCENT'); ?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_CHARTBG'); ?></td>
				<td>&nbsp;&nbsp;#<input type="text" name="charts_bg"
					value="<?php echo $this->config->charts_bg; ?>" size="20" />
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_CHARTLINEC'); ?></td>
				<td>&nbsp;&nbsp;#<input type="text" name="charts_linec"
					value="<?php echo $this->config->charts_linec; ?>" size="20" />
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_CHARTLINEC_SPEED'); ?></td>
				<td>&nbsp;&nbsp;#<input type="text" name="charts_linec_speed"
					value="<?php echo $this->config->charts_linec_speed; ?>" size="20" />
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_CHARTLINEC_PACE'); ?></td>
				<td>&nbsp;&nbsp;#<input type="text" name="charts_linec_pace"
					value="<?php echo $this->config->charts_linec_pace; ?>" size="20" />
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_CHARTLINEC_HEARTBEAT'); ?></td>
				<td>&nbsp;&nbsp;#<input type="text" name="charts_linec_heartbeat"
					value="<?php echo $this->config->charts_linec_heartbeat; ?>" size="20" />
				</td>
			</tr>
			</tbody>
	</table>
	&nbsp;
	<?php
	echo JHtmlBootstrap::endTab();
	// Viewing options BEGIN
	echo JHtmlBootstrap::addTab('tabs', 'display', JText::_('COM_JTG_DISPLAY'));

	?>
	<table class="admintable">
		<tbody>
			<tr>
				<td><?php echo JText::_('COM_JTG_TEMPLATE'); ?></td>
				<td><?php echo $this->lists['tmpl']; ?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_JTG_GALLERY'); ?></td>
				<td><?php echo $this->lists['gallery']; ?></td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_GALLERY_CODE_DESC') . '">' . JText::_('COM_JTG_GALLERY_CODE') . '</span>'; ?>
				</td>
				<td><textarea rows="2" cols="70" name="gallery_code"><?php echo $this->config->gallery_code; ?></textarea></td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_IMAGETYPES_DESC') . '">' . JText::_('COM_JTG_IMAGETYPES') . '</span>';?>
				</td>
				<td><input type="text" name="type"
					value="<?php echo $this->config->type; ?>" size="30" /></td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_MAX_IMAGES_DESC') . '">' . JText::_('COM_JTG_MAX_IMAGES') . '</span>';?>
				</td>
				<td><input type="text" name="max_images"
					value="<?php echo $this->config->max_images; ?>" size="30" /></td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_SIZE_DESC') . '">' . JText::_('COM_JTG_IMAGESIZE') . '</span>';?>
				</td>
				<td><input type="text" name="max_size"
					value="<?php echo $this->config->max_size; ?>" size="20" /></td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_GEOIM_HEIGHT_DESC') . '">' . JText::_('COM_JTG_GEOIM_HEIGHT') . '</span>';?>
				</td>
				<td><input type="text" name="max_geoim_height"
					value="<?php echo $this->config->max_geoim_height; ?>" size="20" />
				</td>
			</tr>
			<tr>
				<td>
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_TT_TITLE')
					. '::' . JText::_('COM_JTG_TT_THUMB_HEIGHT_DESC') . '">' . JText::_('COM_JTG_THUMB_HEIGHT') . '</span>';?>
				</td>
				<td><input type="text" name="max_thumb_height"
					value="<?php echo $this->config->max_thumb_height; ?>" size="20" />
				</td>
			</tr>
		</tbody>
	</table>
	&nbsp;
	<?php
	echo JHtmlBootstrap::endTab();
	// Viewing options END

	// Comments BEGIN

	echo JHtmlBootstrap::addTab('tabs', 'comments', JText::_('COM_JTG_COMMENTS'));
	?>
	<table class="admintable">
		<tr>
			<td valign="top">
				<?php echo '<span class="hasTip" title=" ' . JText::_('COM_JTG_TT_TITLE')
				. '::' . JText::_('COM_JTG_TT_ACTIVATE_COMMENTS') . '">' . JText::_('COM_JTG_ACTIVATE_COMMENTS') . '</span>'; ?>
			</td>
			<td><?php echo $this->lists['comments']; ?></td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo '<span class="hasTip" title=" ' . JText::_('COM_JTG_TT_TITLE')
				. '::' . JText::_('COM_JTG_TT_INFORM_AUTOR') . '">' . JText::_('COM_JTG_INFORM_AUTOR') . '</span>'; ?>
			</td>
			<td><?php echo $this->lists['inform']; ?></td>
		</tr>
		<tr>
			<td valign="top">
				<?php echo '<span class="hasTip" title=" ' . JText::_('COM_JTG_TT_TITLE')
				. '::' . JText::_('COM_JTG_TT_CAPTCHA') . '">' . JText::_('COM_JTG_CAPTCHA') . '</span>'; ?>
			</td>
			<td><?php echo $this->lists['captcha']; ?>
				<div>
					<?php echo JText::_('COM_JTG_OST_CAPTCHA') . " " . $this->captcha; ?>
				</div></td>
		</tr>
		<tr>
			<td valign="top"><?php echo JText::_('COM_JTG_ORDERING'); ?></td>
			<td><?php echo $this->lists['order']; ?></td>
		</tr>
	</table>
	&nbsp;
	<?php
	echo JHtmlBootstrap::endTab();
	// Comments END

	// Vote BEGIN
	// Vote END

	// Approach BEGIN
	if ($this->config->approach != "no")
	{
		echo JHtmlBootstrap::addTab('tabs', 'jtg-approach', JText::_('COM_JTG_APPROACH'));
		?>
	<table class="admintable">
		<?php
		if ($this->config->approach == "easy")
		{
			?>
		<tr>
			<td>
				<?php echo '<span class="hasTip" title=" ' . JText::_('COM_JTG_TT_TITLE')
				. '::' . JText::_('COM_JTG_TT_SERVICE_PROVIDER') . '">' . JText::_('COM_JTG_SERVICE_PROVIDER') . '</span>'; ?>
			</td>
			<td><?php
				// TODO: echo $this->lists['serviceprovider'];
				?>
			</td>
		</tr>

		<?php
		}

		if ($this->config->approach == "ors")
		{
			?>
		<tr>
			<td>
				<?php echo '<span class="hasTip" title=" ' . JText::_('COM_JTG_TT_TITLE')
				. '::' . JText::_('COM_JTG_TT_ICONSET') . '">' . JText::_('COM_JTG_ICONSET') . '</span>'; ?>
			</td>
			<td><?php echo $this->lists['routingiconset']; ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php echo JText::_('COM_JTG_POWERED_BY')
			. ": <a href=\"http://openrouteservice.org\">openrouteservice.org</a>
			(<a href=\"http://wiki.openstreetmap.org/wiki/OpenRouteService\">"
			. JText::_('COM_JTG_HELP') . "</a>)\n";
			?>
			</td>
		</tr>
		<?php
		}

		if ( ($this->config->approach == "cm") OR ($this->config->approach == "cmkey") )
		{
			?>
		<tr>
			<td>
				<?php echo '<span class="hasTip" title=" ' . JText::_('COM_JTG_TT_TITLE')
				. '::' . JText::_('COM_JTG_TT_ICONSET') . '">' . JText::_('COM_JTG_ICONSET') . '</span>'; ?>
			</td>
			<td><?php echo $this->lists['routingiconset']; ?></td>
		</tr>
		<tr>
			<td colspan="2"><?php echo JText::_('COM_JTG_POWEREDBY')
			. ": <a href=\"http://cloudmade.com/\">CloudMade</a> (<a href=\"http://wiki.openstreetmap.org/wiki/Cloudmade\">"
			. JText::_('COM_JTG_HELP') . "</a>)\n";
			?>
			</td>
		</tr>
		<?php
		}
		?>
	</table>
	&nbsp;
	<?php
		echo JHtmlBootstrap::endTab();
	}
	// Approach END

	echo JHtmlBootstrap::endTabSet();
	echo JHtml::_('form.token');
	?>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="id" value="1" /> <input type="hidden" name="task"
		value="" />
</form>
