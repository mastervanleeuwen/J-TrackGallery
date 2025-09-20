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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

use Jtg\Component\Jtg\Site\Helpers\JtgMapHelper;

// Toolbar
ToolbarHelper::title(Text::_('COM_JTG_MAPS'), 'generic.png');
ToolbarHelper::back();
ToolbarHelper::addNew('newmap', 'COM_JTG_NEW_MAP');
ToolbarHelper::editList('editmap');
ToolbarHelper::spacer();
ToolbarHelper::publish();
ToolbarHelper::unpublish();
ToolbarHelper::deleteList();
ToolbarHelper::spacer();
ToolbarHelper::help("maps", true);

jimport('joomla.html.pane');

$ordering = ($this->lists['order'] == 'ordering' );

$link = Route::_('index.php?option=com_jtg&task=maps&controller=maps&layout=default');
if (version_compare(JVERSION,'4.0','lt'))
{
?>

<div id="j-sidebar-container" class="span2">
<?php echo JHtmlSidebar::render(); ?>
</div>

<?php
}
?>

<div id="j-main-container" class="span10">
<form action="<?php echo $link ?>" method="post"
	name="adminForm" id="adminForm" class="adminForm">
	<!-- <table>
	<tr>
		<td align="left" style="width:100%;">
			<?php echo Text::_('COM_JTG_FILTER'); ?>:
			<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();"><?php echo Text::_('COM_JTG_APPLY'); ?></button>
			<button onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();"><?php echo Text::_('COM_JTG_RESET'); ?></button>
		</td>
		<td nowrap="nowrap">
			<?php
				echo $this->state;
			?>
		</td>
	</tr>
</table>-->
	<table class="table table-striped adminlist">
		<thead>
			<tr>
				<th class="title" nowrap="nowrap"><?php
				// 				echo HTMLHelper::_('grid.sort', Text::_('COM_JTG_ID'), 'id', @$this->lists['order_Dir'], @$this->lists['order'], 'maps' );
				echo Text::_('COM_JTG_ID');
				?>
				</th>
				<th class="title"><input type="checkbox"
					onclick="Joomla.checkAll(this)"
					title="<?php echo Text::_('JGLOBAL_CHECK_ALL');?>" value=""
					name="checkall-toggle"></th>
				<th class="title"><?php
				echo Text::_('COM_JTG_NAME');
				?></th>
				<?php if ($ordering)
				{
				?>
				<th class="order"><?php echo Text::_('COM_JTG_ORDER'); ?> <?php
				?>
				</th>
				<th class="order"><?php echo HTMLHelper::_('grid.order',  $this->maps); ?>
				</th>
<?php
}
?>
				<th class="title"><?php
				// 				echo HTMLHelper::_('grid.sort', Text::_('COM_JTG_PUBLISHED'), 'published', @$this->lists['order_Dir'], @$this->lists['order'], 'maps' ); ? >:</th>
				echo Text::_('COM_JTG_PUBLISHED'); ?></th>
				<th class="title"><?php
				echo Text::_('COM_JTG_MAP_TYPE');
				?></th>
				<th class="title"><?php
				echo Text::_('COM_JTG_OL_PARAMETERS');
				?></th>
				<th class="title">API Key</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$k = 0;
			$user = Factory::getUser();
			$maptypes = JtgMapHelper::getMapTypes();
			for ($i = 0, $n = count($this->maps); $i < $n; $i++)
			{
				// $map->published
				$map = $this->maps[$i];
				$published 	= HTMLHelper::_('jgrid.published', $map->published, $i);
				$checked 	= HTMLHelper::_('grid.checkedout', $map, $i);
				$name		= $this->buildEditKlicks(Text::_($map->name), $i, $map->id);
				$map_type = $maptypes[$map->type];
				?>
			<tr
				class="<?php
					echo "row$k ";
					echo $k? 'row-odd':'row-even';
					$k = 1 - $k;
					?>">
				<td class="center"><?php echo $map->id;									?></td>
				<td class="center"><?php echo $checked;									?></td>
				<td class="center"><?php echo $name;	?></td>
<?php
if ($ordering)
{
?>
				<td colspan="2" class="order"><?php echo $this->pagination->orderUpIcon($i, true, 'orderup', 'Move Up', $map->ordering);
				?> <?php echo $this->pagination->orderDownIcon($i, $n, true, 'orderdown', 'Move Down', $map->ordering);
				?> <input type="text" name="order[]" size="2" maxlength="2"
					value="<?php echo $map->ordering;
				?>" class="text_area"
					style="text-align: center; width: 2em; display: inline" />
				</td>
<?php
}
?>
				<td class="center"><?php echo $published; ?></td>
				<td class="center"><?php echo $map_type; ?></td>
				<td class="center"><?php echo $map->param; ?></td>
				<td class="center"><?php echo $map->apikey; ?></td>
			</tr>
			<?php
			}
			?>
		</tbody>
		<!--		<tfoot>
			<tr>
				<td colspan="9">

				</td>
			</tr>
		</tfoot>
-->
	</table>


	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task" value="" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="maps" /> <input type="hidden" name="filter_order"
		value="<?php echo $this->lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir"
		value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
	&nbsp;
</form>
<?php
if (version_compare(JVERSION,'4.0','lt'))
{
	echo "</div>;";
}
?>
