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

// Toolbar
JToolBarHelper::title(Text::_('COM_JTG_EDIT_COMMENT'), 'addedit.png');
JToolBarHelper::back();
JToolBarHelper::spacer();
JToolBarHelper::save('saveComment', $alt = 'COM_JTG_SAVE', 'save.png');
JToolBarHelper::help('comments/form', true);
?>
<form class="adminForm" name="adminForm" id="adminForm" method="post"
	action="">
	<table class="adminlist">
		<thead>
			<tr>
				<th colspan="2"><?php echo Text::_('COM_JTG_EDIT_COMMENT'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php echo Text::_('COM_JTG_USER'); ?></td>
				<td><input type="text" name="user"
					value="<?php echo $this->comment->user; ?>" size="30"
					readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_EMAIL'); ?></td>
				<td><input type="text" name="email"
					value="<?php echo $this->comment->email; ?>" size="30"
					readonly="readonly" />
				</td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_COMMENT_TITLE'); ?></td>
				<td><input type="text" name="title"
					value="<?php echo $this->comment->title; ?>" size="30" />
				</td>
			</tr>
			<tr>
				<td><?php echo Text::_('COM_JTG_COMMENT_TEXT'); ?></td>
				<td><?php echo $this->editor->display('text', $this->comment->text, '500', '200', '100', '50', true, null); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
echo HTMLHelper::_('form.token'); ?>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="controller" value="comments" /> <input
		type="hidden" name="task" value="" /> <input type="hidden" name="id"
		value="<?php echo $this->comment->id; ?>" />
</form>
