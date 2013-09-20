<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5
 *
 * 
 * @author     J!Track Gallery, InJO3SM and joomGPStracks teams
 * @package    com_jtg
 * @subpackage backend
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL2
 * @link       http://jtrackgallery.net/
 *
 */

defined('_JEXEC') or die('Restricted access');

// toolbar
JToolBarHelper::title(JText::_('COM_JTG_CATS'), 'categories.png');
JToolBarHelper::back($alt= 'COM_JTG_BACK', $href= 'javascript:history.back();');
JToolBarHelper::spacer();
JToolBarHelper::addNew('newcat', $alt='COM_JTG_NEW_CATEGORY', 'new.png' );
JToolBarHelper::custom( 'managecatpics', 'new-style.png', 'new-style.png', 'COM_JTG_MANAGE_PICS', false);
JToolBarHelper::editList('editcat');
JToolBarHelper::publish();
JToolBarHelper::unpublish();
JToolBarHelper::deleteList('COM_JTG_DELETE_IMAGES');
JToolBarHelper::help( 'cats',true );
$ordering = ($this->lists['order'] == 'ordering');
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">

<table class="adminlist" cellpadding="1">
	<thead>
		<tr>
			<th width="5%" class="title"><?php echo JText::_( 'COM_JTG_NUM' ); ?></th>
			<th width="5%" class="title" nowrap="nowrap"><?php echo JText::_( 'COM_JTG_ID'); ?>
			</th>
			<th width="5%" class="title"><input type="checkbox" name="toggle"
				value="" onclick="checkAll(<?php echo count($this->rows); ?>);" /></th>
			<th width="5%" class="title"><?php echo JText::_( 'COM_JTG_IMAGE' ); ?></th>
			<th width="20%" class="title"><?php echo JText::_( 'COM_JTG_CAT' ); ?></th>
			<th width="40%" class="title"><?php echo JText::_( 'COM_JTG_DESCRIPTION' ); ?></th>
			<?php if ( $ordering !== false ) { ?>
			<th width="10%" class="order"><?php echo JText::_( 'COM_JTG_ORDER'); ?> <?php if ($ordering) echo JHTML::_('grid.order', $this->rows ); ?>
			</th>
			<?php } ?>
			<th width="10%" class="title"><?php echo JText::_( 'COM_JTG_PUBLISHED'); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$k = 0;
	for ($i=0, $n=count( $this->list ); $i < $n; $i++)
	{
		$row = &$this->list[$i];
		$checked 	= JHTML::_('grid.checkedout', $row, $i );
		$published 	= JHTML::_('grid.published', $row, $i );

		?>
		<tr class="<?php echo "row$k"; ?>">
			<td align="center"><?php echo $this->pagination->getRowOffset( $i ); ?></td>
			<td><?php echo $row->id; ?></td>
			<td align="center"><?php echo $checked; ?></td>
			<td align="center"><?php
			if ((isset($this->catpic[$this->list[$i]->id])) AND ( $this->catpic[$this->list[$i]->id] != "" ))
			echo $this->catpic[$this->list[$i]->id];
			?></td>
			<td align="left"><a href="javascript:void(0);"
				onclick="javascript:return listItemTask('cb<?php echo $i; ?>','editcat')">
				<?php echo JText::_($row->treename); ?> </a></td>
			<td><?php echo $row->description; ?></td>
			<?php if ( $ordering !== false ) { ?>
			<td class="order"><span><?php echo $this->pagination->orderUpIcon( $i, true,'orderup', 'Move Up', $ordering );
			?></span> <span><?php echo $this->pagination->orderDownIcon( $i, $n, true, 'orderdown', 'Move Down', $ordering );
			?></span> <?php $disabled = $ordering ? '' : 'disabled="disabled"';
			?> <input type="text" name="order[]" size="5"
				value="<?php echo $row->ordering;
				?>"
				<?php echo $disabled;
				?> class="text_area"
				style="text-align: center" /></td>
				<?php } ?>
			<td align="center"><?php echo $published;?></td>
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
</table>
<input type="hidden" name="option" value="com_jtg" /> <input
	type="hidden" name="task" value="cats" /> <input type="hidden"
	name="boxchecked" value="0" /> <input type="hidden" name="controller"
	value="cats" /> <input type="hidden" name="filter_order"
	value="<?php echo $this->lists['order']; ?>" /> <input type="hidden"
	name="filter_order_Dir"
	value="<?php echo $this->lists['order_Dir']; ?>" /> <?php echo JHTML::_( 'form.token' ); ?>
</form>