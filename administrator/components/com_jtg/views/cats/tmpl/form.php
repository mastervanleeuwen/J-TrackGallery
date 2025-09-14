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

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('COM_JTG_ADD_CAT'), 'categories.png');
ToolbarHelper::back();
ToolbarHelper::spacer();
ToolbarHelper::save('savecat', $alt = 'COM_JTG_SAVE', 'save.png');
ToolbarHelper::help('cats/form', true);

?>
<form action="" method="post" name="adminForm" id="adminForm"
	class="adminForm" enctype="multipart/form-data">
	<table class="adminlist">
		<thead>
			<tr>
				<th colspan="5" align="center"><?php echo Text::_('COM_JTG_ADD_CAT'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td width="150px"><?php echo Text::_('COM_JTG_TITLE'); ?></td>
				<td><input type="text" name="title" value="" size="30" maxlength="30"/></td>
			</tr>
			<tr>
				<td width="150px"><?php echo Text::_('COM_JTG_PARENT'); ?></td>
				<td><?php echo $this->lists['parent']; ?></td>
			</tr>
			<tr>
				<td width="150px"><?php echo Text::_('COM_JTG_PUBLISHED'); ?></td>
				<td><?php echo $this->lists['block']; ?></td>
			</tr>
			<tr>
				<td width="150px"><?php echo Text::_('COM_JTG_CATS_DEFAULT_MAP'); ?></td>
				<td><?php echo $this->lists['default_map']; ?></td>
			</tr>
			<tr>
				<td width="150px"><?php echo Text::_('COM_JTG_USEPACE'); ?></td>
				<td><?php echo $this->lists['usepace']; ?></td>
			</tr>
			<tr>
				<td width="150px"><?php echo Text::_('COM_JTG_IMAGE'); ?></td>
				<td><input type="radio" name="catpic" value=""
					title="<?php echo Text::_('COM_JTG_NONE'); ?>" checked="checked">
					<?php echo Text::_('COM_JTG_NONE'); ?><br /> <?php

					foreach ($this->images as $img)
					{
						$imageurl = Uri::root() . 'images/jtrackgallery/cats/';
						$pic = "";
						$pic .= "<input type=\"radio\" name=\"catpic\" value=\"" . $img . "\" title=\"" . $img . "\">" .
								"<img src=\"" . $imageurl . $img .
								"\" title=\"" . $img . "\" />\n";
						echo $pic;
					}
					?></td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_DESC_JTEXT_ALLOWED'); ?></td>
				<td><input type="text" name="desc" size="60" maxlength="150" value=""/>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="controller" value="cats" /> <input type="hidden"
		name="task" value="" />
</form>
