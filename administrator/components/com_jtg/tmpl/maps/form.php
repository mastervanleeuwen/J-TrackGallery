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
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

use Jtg\Component\Jtg\Administrator\Field\MaptypeField;
use Jtg\Component\Jtg\Site\Helpers\JtgHelper;
use Jtg\Component\Jtg\Site\Helpers\JtgMapHelper;

$id = 0;

if (!isset($this->map))
{
	$title = Text::_('COM_JTG_ADD_MAP');
}
else
{
	$title = Text::_('COM_JTG_EDIT_MAP');
	$map = $this->map;
	$id = $map->id;
}

ToolBarHelper::title($title, 'categories.png');
ToolBarHelper::back();
ToolBarHelper::spacer();

if ($id < 1)
{
	ToolBarHelper::save('savemap', $alt = 'COM_JTG_SAVE', 'save.png');
}
else
{
	ToolBarHelper::save('updatemap', $alt = 'COM_JTG_SAVE', 'save.png');
}

//ToolBarHelper::help("maps/newmap", true);
$document = Factory::getDocument();

if ($id)
{
	$cache = Factory::getCache('com_jtg');
	$cfg = JtgHelper::getConfig();
	$model = $this->getModel();

}

$maptype = new MaptypeField();
if (($id) && isset($map->type)) $maptype->SetValue($map->type);
$maptype->__set('name','type');
?>
<form action="" method="post" name="adminForm" id="adminForm"
	class="adminForm" enctype="multipart/form-data">
	<table class="adminlist">
		<thead>
			<tr>
				<th colspan="3" align="center"><?php echo $title; ?></th>
			</tr>
		</thead>
		<tbody>
<?php if ($id)
{
?>
			<tr>
				<td>Id:</td>
				<td><?php echo $id; ?></td>
			</tr>
<?php
}
?>
			<tr>
				<td><?php echo Text::_('COM_JTG_NAME'); ?>:*</td>
				<td><input id="name" type="text" name="name"
					value="<?php echo isset($map->name)? $map->name: (string) $id; ?>"
					size="50" maxlength="50" /> (<?php echo isset($map->name)? Text::_($map->name): (string) $id; ?>)
				</td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_PUBLISHED'); ?>:*</td>
				<td><?php echo $this->list['published']; ?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_MAP_TYPE'); ?>:*</td>
				<td><?php echo HTMLHelper::_('select.genericlist', JtgMapHelper::getMapTypes(), 'type', 'size="1"', 'id', 'name', $map->type); //echo $maptype->renderField(); ?> </td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_OL_PARAMETERS'); ?>:*</td>
						<?php
						if (($id) AND (isset($map->param)))
						{
							$param = htmlentities($map->param);
						}
						else
						{
							$param = '';
						}
						?>
				<td><textarea id="param" name="param"
						cols="100" maxlength="500" rows="8"><?php echo $param; ?></textarea>
				</td>
			</tr>
				<td><?php echo Text::_('API Key'); ?>:</td>
						<?php
						if (($id) AND (isset($map->apikey)))
						{
							$apikey = htmlentities($map->apikey);
						}
						else
						{
							$apikey = '';
						}
						?>
				<td><textarea id="apikey" name="apikey"
						cols="100" maxlength="150"><?php echo $apikey; ?></textarea>
				</td>
			</tr>
			<tr>
			</tr>
			<tr>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_ORDER'); ?>:*</td>
				<td><input id="order" type="text" name="order"
					value="<?php echo (($id AND isset($map->ordering))? $map->ordering: '99'); ?>"
					size="2" maxlength="2">
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="controller" value="maps" /> <input type="hidden"
		name="checked_out" value="0" /> <input type="hidden" name="task"
		value="maps" />
<?php
if ($id)
{
?>
	<input type="hidden" name="id" value="<?php echo $id; ?>" />

<?php
}
?>
</form>
