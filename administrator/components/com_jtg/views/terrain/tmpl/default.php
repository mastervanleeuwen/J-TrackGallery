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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Toolbar
ToolbarHelper::title(Text::_('COM_JTG_TERRAIN'), 'categories.png');
ToolbarHelper::back();
ToolbarHelper::spacer();
ToolbarHelper::addNew('newterrain', 'COM_JTG_NEW_TERRAIN');
ToolbarHelper::editList('editterrain');
ToolbarHelper::publish();
ToolbarHelper::unpublish();
ToolbarHelper::deleteList();
ToolbarHelper::help('terrain', true);

if (version_compare(JVERSION,'4.0','lt'))
{
?>

<div id="j-sidebar-container" class="span2">
<?php echo JHtmlSidebar::render(); ?>
</div>
<div id="j-main-container" class="span10">

<?php
}
?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th class="title"><?php echo Text::_('COM_JTG_NUM'); ?></th>
				<th class="title"><input type="checkbox"
					onclick="Joomla.checkAll(this)"
					title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" value=""
					name="checkall-toggle"></th>
				<th class="title"><?php echo Text::_('COM_JTG_TITLE'); ?></th>
				<th class="title"><?php echo Text::_('COM_JTG_TRANSLATION'); ?></th>
				<th class="title"><?php echo Text::_('COM_JTG_PUBLISHED'); ?></th>
				<th class="title" nowrap="nowrap"><?php echo Text::_('COM_JTG_ID'); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="5"><?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$k = 0;

			for ($i = 0, $n = count($this->rows); $i < $n; $i++)
			{
				$row = $this->rows[$i];

				$link 	= JRoute::_('index.php?option=com_jtg&task=editterrain&controller=terrain&id=' . $row->id);

				$checked 	= HTMLHelper::_('grid.checkedout', $row, $i);
				$published 	= HTMLHelper::_('jgrid.published', $row->published, $i);

				?>
			<tr class="<?php echo "row$k ";
				echo $k? 'row-odd':'row-even'; ?>">
				<td align="center"><?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td align="center"><?php echo $checked; ?></td>
				<td align="center"><a href="javascript:void(0);"
					onclick="javascript:return Joomla.listItemTask('cb<?php echo $i; ?>','editterrain')">
					<?php echo $row->title; ?>
				</a></td>
				<td align="center"><a href="javascript:void(0);"
					onclick="javascript:return Joomla.listItemTask('cb<?php echo $i; ?>','editterrain')">
					<?php echo Text::_($row->title); ?></a></td>
				<td align="center"><?php echo $published;?></td>
				<td><?php echo $row->id; ?></td>
			</tr>
			<?php
			$k = 1 - $k;
			}
			?>
		</tbody>
	</table>
	<br />
	<?php echo Text::_('COM_JTG_TERRAINS_HELP'); ?>

	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task" value="" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="terrain" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php
if (version_compare(JVERSION,'4.0','lt'))
{
	echo "</div>\n";
}
?>
