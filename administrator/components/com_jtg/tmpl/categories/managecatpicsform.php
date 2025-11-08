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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

// Toolbar
ToolbarHelper::title(Text::_('COM_JTG_UPLOAD_CATIMAGE'), 'categories.png');
ToolbarHelper::back();
//ToolbarHelper::spacer();
//ToolbarHelper::help('categories/managecatpicsform', true);

if (version_compare(JVERSION,'4.0') >= 0)
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::script(Uri::base() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
}
else if (version_compare(JVERSION,'3.0') >= 0)
{
	HTMLHelper::_('jquery.framework');
	HTMLHelper::script(Uri::base() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
	HTMLHelper::_('behavior.framework');
}

echo Text::sprintf('COM_JTG_ALLOWED_FILETYPES', $this->types);
?>
<form action="" enctype="multipart/form-data" method="post"
	name="adminForm" id="adminForm">
	<input type="file" name="files" accept="image/*" /><br /> <input
		type='submit' value='<?php echo Text::_('COM_JTG_UPLOAD'); ?>'
		class='submit' onclick="javascript: Joomla.submitbutton('uploadcatimages')" />
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task"
		value="<?php echo Factory::getApplication()->input->get('task'); ?>" />
	<input type="hidden" name="boxchecked" value="0" /> <input
		type="hidden" name="controller" value="categories" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
