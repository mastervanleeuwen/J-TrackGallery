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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Toolbar
ToolbarHelper::title(Text::_('COM_JTG_MANAGE_PICS'), 'categories.png');
ToolbarHelper::back();
ToolbarHelper::spacer();
ToolbarHelper::addNew('newcatpic', 'COM_JTG_NEW_CATEGORY_ICON');
ToolbarHelper::deleteList('COM_JTG_DELETE_IMAGES', 'removepic');
//ToolbarHelper::help('categories/managecatpics', true);

?>
<form action="" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th width="5%" class="title"><?php echo Text::_('COM_JTG_NUM'); ?>
				</th>
				<th width="5%" class="title"><input type="checkbox"
					onclick="Joomla.checkAll(this)"
					title="<?php echo Text::_('JGLOBAL_CHECK_ALL');?>" value=""
					name="checkall-toggle"></th>
				<th width="10%" class="title"><?php echo Text::_('COM_JTG_NAME'); ?>
				</th>
				<th width="5%" class="title"><?php echo Text::_('COM_JTG_IMAGE'); ?>
				</th>
				<th class="title"></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$k = 0;

			for ($i = 0, $n = count($this->rows); $i < $n; $i++)
			{
				$row = $this->rows[$i];
				$checked 	= HTMLHelper::_('grid.checkedout', $row, $i);
				?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center"><?php echo $i; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				<td align="right">
					<!--				<a href="javascript:void(0);" onclick="javascript:return listItemTask('cb<?php echo $i; ?>','editcatpic')">-->
					<?php echo $row->file; ?> <!--				</a>-->
				</td>
				<!--			<td align="left"><?php echo $row->ext; ?></td>-->
				<td align="left"><?php echo $row->image; ?></td>
				<td></td>
			</tr>
			<?php
			$k = 1 - $k;
			}
			?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task"
		value="<?php echo Factory::getApplication()->input->get('task'); ?>" />
	<input type="hidden" name="boxchecked" value="0" /> <input
		type="hidden" name="controller" value="categories" />
	<?php echo HTMLHelper::_('form.token'); ?>
	&nbsp;
</form>

