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

// Toolbar
JToolBarHelper::title(JText::_('COM_JTG_UPLOAD_CATIMAGE'), 'categories.png');
JToolBarHelper::back();
JToolBarHelper::spacer();
JToolBarHelper::help('cats/managecatpicsform', true);

if (version_compare('JVERSION','4.0','ge'))
{
	JHtml::_('jquery.framework');
	JHtml::script(Juri::base() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
}
else if (version_compare('JVERSION','3.0','ge'))
{
	JHtml::_('jquery.framework');
	JHtml::script(Juri::base() . 'components/com_jtg/assets/js/jquery.MultiFile.js');
	JHTML::_('behavior.framework');
}
else
{
	JHtml::script('jquery.js', 'components/com_jtg/assets/js/', false);
	JHtml::script('jquery.MultiFile.js', 'components/com_jtg/assets/js/', false);
	JHtml::script('mootools.js', '/media/system/js/', false);
}

echo JText::sprintf('COM_JTG_ALLOWED_FILETYPES', $this->types);
?>
<form action="" enctype="multipart/form-data" method="post"
	name="adminForm" id="adminForm">
	<input type="file" name="files" accept="image/*" /><br /> <input
		type='submit' value='<?php echo JText::_('COM_JTG_UPLOAD'); ?>'
		class='submit' onclick="javascript: Joomla.submitbutton('uploadcatimages')" />
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task"
		value="<?php echo JFactory::getApplication()->input->get('task'); ?>" />
	<input type="hidden" name="boxchecked" value="0" /> <input
		type="hidden" name="controller" value="cats" />
	<?php echo JHtml::_('form.token'); ?>
</form>
