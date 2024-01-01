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

$user = JFactory::getUser();
$uid = $user->id;
$show_cat_icon = $this->params->get('jtg_param_use_cats') && ! (bool) $this->params->get('jtg_param_tracks_list_hide_icon_category');

if ($uid != 0)
{
	echo $this->menubar;
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
<?php
if (empty($this->items)) {
	JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_LIST_NO_TRACK'), 'Warning');
	echo '<b>' . JText::_('COM_JTG_LIST_NO_TRACK') . '</b>';
} else 
{

echo $this->parseTemplate("headline", JText::_("COM_JTG_USER_TOTALS"), "summary", null);
$user_summary = $this->getModel()->getTotals($uid);
// TODO: add units for distance
 ?>
<div class="gps-info">
   <table class="gps-info-tab">
	<tr>
		<td><?php echo JText::_('COM_JTG_DISTANCE'); ?>:</td>
		<td><?php echo JtgHelper::getFormattedDistance($user_summary->distance,'-',$this->cfg->unit); ?>
		</td>
	</tr>
	<tr>
		<td><?php echo JText::_('COM_JTG_ELEVATION_UP'); ?>:</td>
		<td><?php
			echo $user_summary->ele_asc;
			echo ' ' . JText::_('COM_JTG_METERS');
			?>
		</td>
	</tr>
	<tr>
		<td><?php echo JText::_('COM_JTG_ELEVATION_DOWN'); ?>:</td>
		<td><?php echo $user_summary->ele_desc; ?>
			<?php echo ' ' . JText::_('COM_JTG_METERS'); ?>
		</td>
	</tr>

	</table>
</div>
<?php echo $this->parseTemplate("headline", JText::_("My tracks"), "tracklist", null); ?>
<form action="<?php echo $this->action; ?>" method="post"
	name="adminForm" id="adminForm">
	<table style="width:100%;">
		<tr>
<!-- fixme:			<td><?php echo JText::_('JGLOBAL_DISPLAY_NUM') . '&nbsp;' . $this->pagination->getLimitBox(); ?>
			</td> -->
			<td style="text-align: right"><?php echo $this->pagination->getResultsCounter(); ?>
			</td>
		</tr>
	</table>
	<div style="overflow-x:auto;">
	<table class="table tracktable">
		<thead>
			<tr
				class="sectiontableheader<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
				<th width="60px">#</th>
				<th><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_TITLE'), 'title', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
<?php
				if ($show_cat_icon)
				{   
?> 
				<th width="80px"><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_CAT'), 'catid', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
<?php			} ?>
				<th width="50px"><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_HITS'), 'hits', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
<?php
				if (! $this->params->get("jtg_param_disable_terrains"))
				{   
?> 
				<th width="80px"><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_TERRAIN'), 'terrain', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
<?php			} 
				if ($this->cfg->usevote == 1)
				{
				?> 
				<th width="20px"><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_VOTING'), 'vote', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
<?php			} ?>
				<th width="20px"><?php echo JHtml::_('grid.sort', JText::_('COM_JTG_DISTANCE'), 'distance', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$edit = JTExt::_('edit');
			$delete = JTExt::_('delete');
			$edit = "title=\"" . $edit . "\" alt=\"" . $edit . "\"";
			$delete = "title=\"" . $delete . "\" alt=\"" . $delete . "\"";
			$k = 0;

			foreach ($this->items as $i => $row)
			{
				$terrain = JtgHelper::parseMoreTerrains($this->sortedter, $row->terrain, "array");
				$terrain = implode(", ", $terrain);

				$distance = JtgHelper::getFormattedDistance($row->distance, "-", $this->cfg->unit);

				$votes = LayoutHelper::parseVoteFloat($row->vote);
				$link = JRoute::_('index.php?option=com_jtg&view=track&id=' . $row->id, false);
				$cats = JtgHelper::parseMoreCats($this->cats, $row->catid, "list", true);
				?>
			<tr>
				<td align="center"><?php echo $this->pagination->getRowOffset($i); ?>
<?php
	if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own'))
	{
?>
					<a
					href="<?php echo JRoute::_("index.php?option=com_jtg&view=track&layout=form&id=".$row->id); ?>">
						<img <?php echo $edit ?>
						src="<?php echo JUri::root() ?>components/com_jtg/assets/images/edit_f2.png" width="16px" />
				</a> 
<?php
	}
	if ($this->canDo->get('core.delete') || $this->canDo->get('core.edit.own'))
	{
?>
<a
					href="index.php?option=com_jtg&controller=track&task=delete&id=<?php echo $row->id; ?>"
					onclick="return confirm('<?php echo JText::_('COM_JTG_VALIDATE_DELETE_TRACK')?>')">
						<img <?php echo $delete ?>
						src="<?php echo JUri::root() ?>components/com_jtg/assets/images/cancel_f2.png" width="16px" />
				</a>
<?php
	}
?>
				</td>
				<td><a href="<?php echo $link; ?>">
					<?php echo $row->title; ?> </a></td>
				<?php if ($show_cat_icon) echo "		<td>".$cats."</td>\n"; ?>
				<td><?php echo $row->hits; ?></td>
				<?php if (! $this->params->get("jtg_param_disable_terrains")) echo "		<td>".$terrain."</td>\n"; ?>
				<?php if ($this->cfg->usevote == 1) echo "<td>".$votes."</td>\n"; ?>
				<td><?php echo $distance; ?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr class="sectiontablefooter">
				<td colspan="7" align="center"><div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?> </div></td>
			</tr>
		</tfoot>
	</table>
	</div>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="filter_order"
		value="<?php echo $this->lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir"
		value="<?php echo $this->lists['order_Dir']; ?>" />
</form>
<?php
}
// Adding the comments
if ($this->cfg->comments == 1)
{
   echo $this->parseTemplate("headline", JText::_('COM_JTG_COMMENTS'), "commentstome");
   $comments = $this->getModel()->getCommentsToTracks();
	// TODO: need to add links to the tracks in the displayed list
	//LayoutHelper::parseComments($comments);
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
		<div class="comment-tracklink"> <a href="<?php echo JRoute::_("index.php?option=com_jtg&view=track&id=".$comment->tid); ?>"><?php echo $comment->tracktitle; ?></a>
		</div>
      <div class="no-float"></div>
   </div>
   <div class="comment-autor">
      <?php echo $comment->user; ?>
      <br />
      <?php
      if (! empty($comment->email) ) {
			// TODO: move parseEMailIcon and HomePageIcon to LayoutHelper
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
   echo $this->parseTemplate("headline", JText::_('COM_JTG_MYCOMMENTS'), "mycomments");
   $mycomments = $this->getModel()->getComments();
	if (!$mycomments)
   {
      echo $this->parseTemplate("description",JText::_('COM_JTG_NO_COMMENTS_DESC'));
   }
   else
   {
      for ($i = 0, $n = count($mycomments); $i < $n; $i++)
      {
         $comment = $mycomments[$i];
// TODO: add track name + link and link
?>
<div class='comment'>
   <div class="comment-header">
      <div class="comment-title">
         <?php echo $i + 1 . ": " . $comment->title; ?>
      </div>
      <div class="date">
         <?php if ($comment->date != null) echo JHtml::_('date', $comment->date, JText::_('COM_JTG_DATE_FORMAT_LC4')); ?>
      </div>
		<div class="comment-tracklink"> <a href="<?php echo JRoute::_("index.php?option=com_jtg&view=track&id=".$comment->tid); ?>"><?php echo $comment->tracktitle; ?></a>
		</div>
      <div class="no-float"></div>
   </div>
   <div class="comment-text">
      <?php echo $comment->text; ?>
   </div>
   <div class="no-float"></div>
</div>
<?php
	}
	}
}
}
else
{
	JResponse::setHeader('HTTP/1.0 403', true);
	JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_ALERT_NOT_AUTHORISED'), 'Error');
}

echo $this->footer;
?>
