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

// Load core.js to enable tableordering
JHtml::_('script', 'system/core.js', false, true);

echo $this->lh;

$iconheight = $this->params->get('jtg_param_list_icon_max_height');
$hide_icon_category = (bool) $this->params->get('jtg_param_tracks_list_hide_icon_category');
$hide_icon_istrack = (bool) $this->params->get('jtg_param_tracks_list_hide_icon_istrack');
$hide_icon_isroundtrip = (bool) $this->params->get('jtg_param_tracks_list_hide_icon_isroundtrip');
$hide_icon_is_wp = (bool) $this->params->get('jtg_param_tracks_list_hide_icon_is_wp');
$hide_icon_isgeocache = (bool) $this->params->get('jtg_param_tracks_list_hide_icon_isgeocache');
$height = ($iconheight > 0? ' style="max-height:' . $iconheight . 'px;" ' : ' ');
$levelMin = $this->params->get('jtg_param_level_from');
$levelMax = $this->params->get('jtg_param_level_to');
$showcatcolumn = !$hide_icon_category or !$hide_icon_istrack or !$hide_icon_isroundtrip or !$hide_icon_is_wp or !$hide_icon_isgeocache;
$cfg = JtgHelper::getConfig();
$iconpath = JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";

// no longer needed JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// JTG_FILTER_TODO
//Get trackcategory options
JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');
$trackcategory = JFormHelper::loadFieldType('Trackcategory', false);
$trackcategoryOptions=$trackcategory->getOptions(); // works only if you set your field getOptions on public!!
if (version_compare(JVERSION, '4.0', 'lt')) 
{
	JFactory::getApplication()->getDocument()->addStyleSheet(JUri::root(true) . '/components/com_jtg/assets/template/' .  $cfg->template . '/filter_box_j3.css');
}
?>

<script type="text/javascript">

	 Joomla.tableOrdering = function( order, dir, task )
	{
		var form = document.adminForm;

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		document.adminForm.submit( task );
	}
</script>

<form action="<?php echo $this->action; ?>" method="post"
	name="adminForm" id="adminForm">
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
	<table style="width:100%;">
	    <tr>
			<td style="text-align: right"><?php echo $this->pagination->getResultsCounter(); ?>
			</td>
		</tr>
	</table>
	<div style="overflow-x:auto;">
	<?php if (empty($this->items)) {
		JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_LIST_NO_TRACK'), 'Warning');
		echo '<b>' . JText::_('COM_JTG_LIST_NO_TRACK') . '</b>';
	} else {
	?>
		<table class="tracktable">
		<thead>
			<tr
				class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<th>#</th>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_TITLE'), 'title', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
				<?php if ($showcatcolumn) {?>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_CAT'), 'catid', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
				<?php } ?>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_LEVEL'), 'level', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
				<?php
				if (! $this->params->get("jtg_param_disable_terrains"))
				{
				?>
								<th>
								<?php echo JHtml::_('grid.sort', JText::_('COM_JTG_TERRAIN'), 'terrain', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
								</th>
				<?php
				}

				if (! $this->params->get("jtg_param_tracks_list_hide_users"))
				{
				?>
								<th>
								<?php echo JHtml::_('grid.sort', JText::_('COM_JTG_USER'), 'user', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
								</th>
				<?php
				}

				if (! $this->params->get("jtg_param_tracks_list_hide_hits"))
				{
				?>				<th>
								<?php echo JHtml::_('grid.sort', JText::_('COM_JTG_HITS'), 'hits', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
								</th>
				<?php
				}

				if ($this->cfg->usevote == 1)
				{
				?>
								<th>
								<?php echo JHtml::_('grid.sort', JText::_('COM_JTG_VOTING'), 'vote', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
								</th>
				<?php
				}
				?>
				<th>
				<?php echo JHtml::_('grid.sort', JText::_('COM_JTG_DISTANCE'), 'distance', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr class="sectiontablefooter">
				<td colspan="9" align="center"><div class="pagination"> <?php echo $this->pagination->getPagesLinks();?> </div></td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$k = 0;
			foreach ($this->items as $i => $row)
			{
				if (!$row->title)
				{
					$row->title = "<font class=\"emptyEntry\">" . JText::_('COM_JTG_NO_TITLE') . "</font>";
				}
				$link_only='';
				switch ($row->access)
				{
					case 1:
						$link_only = "&nbsp;<img alt=\"" . JText::_('COM_JTG_REGISTERED') . "\" src=\"" . $iconpath . "registered_only.png\" />";
						break;
					case 2:
						$link_only = "&nbsp;<img alt=\"" . JText::_('COM_JTG_ADMINISTRATORS') . "\" src=\"" . $iconpath . "special_only.png\" />";
						break;
					case 9:
						$link_only = "&nbsp;<img alt=\"" . JText::_('COM_JTG_PRIVATE') . "\" src=\"" . $iconpath . "private_only.png\" />";
						break;
				}
				$link = JRoute::_('index.php?option=com_jtg&view=track&id=' . $row->id, false);
				$profile = JtgHelper::getProfileLink($row->uid, $row->user);
				$cat = JtgHelper::parseMoreCats($this->sortedcats, $row->catid, "list", true, $iconheight);
				$cat = $cat ? $cat: "<img $height src =\"/components/com_jtg/assets/images/cats/symbol_inter.png\" />\n";
				$terrain = JtgHelper::parseMoreTerrains($this->sortedter, $row->terrain, "list", true);
				$hits = JtgHelper::getLocatedFloat($row->hits, 0);
				$layoutHelper = new LayoutHelper;
				$votes = $layoutHelper->parseVoteFloat($row->vote, false);
				$links = null;
				$imagelink = $this->buildImageFiletypes($row->istrack, $row->iswp, $row->isroute, $row->iscache, $row->isroundtrip, $iconheight,
						$hide_icon_istrack, $hide_icon_is_wp, 0, $hide_icon_isgeocache, $hide_icon_isroundtrip);

				$level = JtgHelper::getLevelIcon($row->level, $row->catid, $levelMin, $levelMax, $iconheight);

				if (!$row->distance)
				{
					$row->distance = 0;
				}

				$distance = JtgHelper::getFormattedDistance($row->distance, "-", $this->cfg->unit);

				if ($profile != "")
				{
					$profile .= "&nbsp;";
				}
				else
				{
					$profile .= "<font class=\"emptyEntry\">" . JText::_('COM_JTG_NO_USER') . "</font>&nbsp;";
				}

				$user = JFactory::getUser();
				if ($this->canDo->get('core.edit') ||
					($this->canDo->get('core.edit.own') && ($row->uid==$user->id)))
				{
					$editlink = JRoute::_('index.php?option=com_jtg&view=track&layout=form&id=' . $row->id, false);
					$links = " <a href=\"" . $editlink . "\">" .
					"<img title=\"" . JText::_('JACTION_EDIT') . "\" alt=\"" .
						JText::_('JACTION_EDIT') . "\" src=\"" . JUri::root() . "components/com_jtg/assets/images/edit_f2.png\" width=\"16px\" />" .
					"</a> ";
				}
				if ($this->canDo->get('core.delete') ||
					($this->canDo->get('core.edit.own') && ($row->uid==$user->id)))
				{
					$deletelink = JRoute::_('index.php?option=com_jtg&controller=track&task=delete&id=' . $row->id, false);
					$links .= "<a href=\"" . $deletelink . "\" onclick=\"return confirm('". JText::_('COM_JTG_VALIDATE_DELETE_TRACK') ."')\">" .
						"<img title=\"" . JText::_('JACTION_DELETE') . "\" alt=\"" .
						JText::_('JACTION_DELETE') . "\" src=\"" . JUri::root() . "components/com_jtg/assets/images/cancel_f2.png\" width=\"16px\" />" .
					"</a>";
				}
				?>
			<tr class="sectiontableentry<?php echo $k; ?>">
				<td><?php echo $this->pagination->getRowOffset($i) . $links; ?></td>
				<td><a href="<?php echo $link; ?>">
					<?php echo $row->title; ?> </a><?php echo $link_only?></td>
				<?php if ($showcatcolumn) {?>
					<td>
				<?php echo '<span class="fileis">' . $cat . ' ' . $imagelink . '</span>'; ?></td>
				<?php }?>
				<td><?php echo $level; ?></td>
				<?php
				if (! $this->params->get("jtg_param_disable_terrains"))
				{
				?>
								<td><?php echo $terrain; ?></td>
				<?php
				}

				if (! $this->params->get("jtg_param_tracks_list_hide_users"))
				{
				?>

								<td><?php echo $profile; ?></td>
				<?php
				}

				if (! $this->params->get("jtg_param_tracks_list_hide_hits"))
				{
				?>
								<td><?php echo $hits; ?></td>
				<?php
				}

				if ($this->cfg->usevote == 1)
				{
				?>
								<td><?php echo $votes; ?></td>
				<?php
				}
				?>
				<td><?php echo $distance; ?></td>
			</tr>
			<?php
			$k = 1 - $k;
			}
			?>
		</tbody>
	</table>
	<?php } ?>
	</div>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task" value="" /> <input type="hidden"
		name="filter_order" value="<?php echo $this->lists['order']; ?>" /> <input
		type="hidden" name="filter_order_Dir"
		value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
<?php
echo $this->footer;
