<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
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
JToolBarHelper::title(JText::_('COM_JTG_CATS'), 'categories.png');
JToolBarHelper::back();
JToolBarHelper::spacer();
JToolBarHelper::addNew('newcat', $alt = 'COM_JTG_NEW_CATEGORY');
JToolBarHelper::custom('managecatpics', 'new-style.png', 'new-style.png', 'COM_JTG_MANAGE_PICS', false);
JToolBarHelper::editList('editcat');
JToolBarHelper::publish();
JToolBarHelper::unpublish();
JToolBarHelper::deleteList('COM_JTG_DELETE_IMAGES');
JToolBarHelper::help('cats', true);
$ordering = ($this->lists['order'] == 'ordering');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table class="adminlist">
		<thead>
			<tr>
				<th class="title"><?php echo JText::_('COM_JTG_NUM'); ?></th>
				<th class="title" nowrap="nowrap"><?php echo JText::_('COM_JTG_ID'); ?></th>
				<th class="title"><input type="checkbox"
					onclick="Joomla.checkAll(this)"
					title="<?php echo JText::_('JGLOBAL_CHECK_ALL');?>" value=""
					name="checkall-toggle"></th>
				<th class="title"><?php echo JText::_('COM_JTG_IMAGE'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_CAT'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_DESCRIPTION'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_DEFAULT_MAP'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_USEPACE'); ?></th>
				<?php
if ( $ordering !== false )
{
?>
				<th class="order"><?php echo JText::_('COM_JTG_ORDER'); ?>
	<?php
	if ($ordering)
	{
		echo JHtml::_('grid.order', $this->rows);
	}
	?>
				</th>
<?php
}
?>
				<th class="title"><?php echo JText::_('COM_JTG_PUBLISHED'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$k = 0;
$n = count($this->list);

for ($i = 0; $i < $n; $i++)
{
	$row = $this->list[$i];
	$checked 	= JHtml::_('grid.checkedout', $row, $i);
	$published 	= JHtml::_('jgrid.published', $row->published, $i);
?>
			<tr class="<?php echo $k? "row$k row-odd":"row$k row-even"; ?>">
				<td align="center"><?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td><?php echo $row->id; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				<td align="center">
<?php
if ((isset($this->catpic[$this->list[$i]->id])) AND ( $this->catpic[$this->list[$i]->id] != "" ))
{
	echo $this->catpic[$this->list[$i]->id];
}
?>
				</td>
				<td align="left"><a href="/administrator/index.php?option=com_jtg&amp;controller=cats&amp;task=editcat&amp;cid=<?php echo $row->id; ?>">
						<?php echo JText::_($row->treename); ?>
				</a></td>
				<td><?php echo JText::_($row->description); ?></td>
				<td><?php echo $row->default_map? $row->default_map: ''; ?></td>
				<td><?php echo $row->usepace? JText::_('JYES'): JText::_('JNO'); ?></td>
	<?php
	if ( $ordering !== false )
	{
	?>
				<td class="order"><span><?php echo $this->pagination->orderUpIcon($i, true, 'orderup', 'Move Up', $ordering);
				?> </span> <span><?php echo $this->pagination->orderDownIcon($i, $n, true, 'orderdown', 'Move Down', $ordering);
				?> </span> <?php $disabled = $ordering ? '' : 'disabled="disabled"';
				?> <input type="text" name="order[]" size="1" value="<?php echo $row->ordering;?>"
				<?php echo $disabled; ?> class="text_area"
					style="text-align: center; width: 3em; display: inline" /></td>
	<?php
	}
	?>
				<td align="center"><?php echo $published;?></td>
			</tr>
	<?php
	$k = 1 - $k;
}?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task" value="cats" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="cats" /> <input type="hidden" name="filter_order"
		value="<?php echo $this->lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir"
		value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	&nbsp;
</form>
