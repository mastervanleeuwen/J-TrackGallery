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

use Joomla\CMS\HTML\Helpers\Sidebar;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

// Toolbar
ToolbarHelper::title(Text::_('COM_JTG_CATS'), 'categories.png');
ToolbarHelper::back();
ToolbarHelper::spacer();
ToolbarHelper::addNew('newcat', $alt = 'COM_JTG_NEW_CATEGORY');
ToolbarHelper::custom('managecatpics', 'new-style.png', 'new-style.png', 'COM_JTG_MANAGE_PICS', false);
ToolbarHelper::publishList();
ToolbarHelper::unpublishList();
ToolbarHelper::deleteList('COM_JTG_DELETE_IMAGES');
//ToolbarHelper::help('categories', true);

$ordering = ($this->lists['order'] == 'ordering');

if (version_compare(JVERSION,'4.0','lt'))
{
?>

<div id="j-sidebar-container" class="span2">
<?php echo Sidebar::render(); ?>
</div>
<div id="j-main-container" class="span10">

<?php
}
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">

	<table class="table table-striped">
		<thead>
			<tr>
				<th class="center"><?php echo Text::_('COM_JTG_NUM'); ?></th>
				<th class="center" nowrap="nowrap"><?php echo Text::_('COM_JTG_ID'); ?></th>
				<th class="center"><input type="checkbox"
					onclick="Joomla.checkAll(this)"
					title="<?php echo Text::_('JGLOBAL_CHECK_ALL');?>" value=""
					name="checkall-toggle"></th>
				<th class="center"><?php echo Text::_('COM_JTG_IMAGE'); ?></th>
				<th class="title"><?php echo Text::_('COM_JTG_CAT'); ?></th>
				<th class="title"><?php echo Text::_('COM_JTG_DESCRIPTION'); ?></th>
				<th class="center"><?php echo Text::_('COM_JTG_DEFAULT_MAP'); ?></th>
				<th class="center"><?php echo Text::_('COM_JTG_USEPACE'); ?></th>
				<?php
if ( $ordering !== false )
{
?>
				<th class="order"><?php echo Text::_('COM_JTG_ORDER'); ?>
	<?php
	if ($ordering)
	{
		echo HTMLHelper::_('grid.order', $this->rows);
	}
	?>
				</th>
<?php
}
?>
				<th class="title"><?php echo Text::_('COM_JTG_PUBLISHED'); ?></th>
			</tr>
		</thead>
		<tbody>
<?php
$k = 0;
$n = count($this->list);

for ($i = 0; $i < $n; $i++)
{
	$row = $this->list[$i];
	$checked 	= HTMLHelper::_('grid.checkedout', $row, $i);
	$published 	= HTMLHelper::_('jgrid.published', $row->published, $i);
?>
			<tr class="<?php echo $k? "row$k row-odd":"row$k row-even"; ?>">
				<td class="center"><?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td><?php echo $row->id; ?></td>
				<td class="center"><?php echo $checked; ?></td>
				<td class="center">
<?php
if ((isset($this->catpic[$this->list[$i]->id])) AND ( $this->catpic[$this->list[$i]->id] != "" ))
{
	echo $this->catpic[$this->list[$i]->id];
}
?>
				</td>
				<td class="break-word"><a href="<?php echo Route::_("index.php?option=com_jtg&amp;controller=categories&amp;task=editcat&amp;cid=".$row->id); ?>">
						<?php echo Text::_($row->treename); ?>
				</a></td>
				<td class="break-word"><?php echo Text::_($row->description); ?></td>
				<td class="center"><?php echo $row->default_map? $row->default_map: Text::_('COM_JTG_NONE'); ?></td>
				<td class="center"><?php echo $row->usepace? Text::_('JYES'): Text::_('JNO'); ?></td>
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
		type="hidden" name="task" value="categories" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="categories" /> <input type="hidden" name="filter_order"
		value="<?php echo $this->lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir"
		value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
	&nbsp;
</form>
<?php
if (version_compare(JVERSION,'4.0','lt'))
{
	echo "</div>\n";
}
?>
