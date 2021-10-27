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
JToolBarHelper::title(JText::_('COM_JTG_COMMENTS'), 'categories.png');
JToolBarHelper::back();
JToolBarHelper::spacer();
JToolBarHelper::editList('editComment');
JToolBarHelper::publish();
JToolBarHelper::unpublish();
JToolBarHelper::deleteList();
JToolBarHelper::help('comments', true);

// Code support for joomla version greater than 3.0
if (JVERSION >= 4.0)
{
	JHtmlBootstrap::tooltip('.hasTooltip');
}
else
{
	JHtml::_('behavior.tooltip');
}

$n = count($this->rows);

if ($n > 0)
{
	// Display comments
	?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th class="title"><?php echo JText::_('COM_JTG_NUM'); ?></th>
				<th class="title"><input type="checkbox"
					onclick="Joomla.checkAll(this)"
					title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" value=""
					name="checkall-toggle"></th>
				<th class="title"><?php echo JText::_('COM_JTG_USER'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_TRACK'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_COMMENT_TITLE'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_DATE'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_PUBLISHED'); ?></th>
				<th class="title" nowrap="nowrap"><?php echo JText::_('COM_JTG_ID'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="8"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$k = 0;

			for ($i = 0, $n = count($this->rows); $i < $n; $i++)
			{
				$row = $this->rows[$i];

				$link 	= JRoute::_('index.php?option=com_jtg&task=editComment&controller=comments&id=' . $row->id, false);

				// Eh?? terrain??
				$row->checked_out = null;
				$checked 	= JHtml::_('grid.checkedout', $row, $i);
				$published 	= JHtml::_('jgrid.published', $row->published, $i);

				?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center"><?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td align="center"><?php echo $checked; ?></td>
				<td align="center"><?php echo $row->user; ?></td>
				<td align="center"><?php echo $row->track; ?></td>
				<td align="center">
					<?php echo '<span class="hasTip" title="' . JText::_('COM_JTG_COMMENT_TEXT') . '::' . htmlentities($row->text, ENT_QUOTES, 'UTF-8') . '">' . htmlentities($row->title, ENT_QUOTES, 'UTF-8'); ?>
				</span></td>
				<td align="center"><?php echo $row->date; ?></td>
				<td align="center"><?php echo $published;?></td>
				<td><?php echo $row->id; ?></td>
			</tr>
			<?php
			$k = 1 - $k;
			}
			?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task" value="" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="comments" />
	<?php echo JHtml::_('form.token'); ?>
	&nbsp;
</form>
<?php
}
else
{
	echo '<br /><br /><b>' . JText::_('COM_JTG_NO_COMMENTS') . '</b>';
}
