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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

ToolbarHelper::title(Text::_('COM_JTG_TRANSLATIONS'), 'categories.png');
ToolbarHelper::back();
ToolbarHelper::save('saveLanguages', $alt = 'COM_JTG_SAVE', 'save.png');
ToolbarHelper::help('translations', true);

jimport('joomla.html.html.tabs');
$document = Factory::getDocument();
$style = '
dt.tabs h3
{
float:left;
margin: 0;
margin-right: 10px;
}
div.current {
clear: both;
}
dl.tabs {
float: left;
margin: 10px 0 -1px 0;
z-index: 50;
}

dl.tabs dt {
float: left;
padding: 4px 10px;
border: 1px solid #ccc;
margin-left: 3px;
background: #e9e9e9;
color: #666;
}

dl.tabs dt.open {
background: #F9F9F9;
border-bottom: 1px solid #f9f9f9;
z-index: 100;
color: #000;
}

div.current {
clear: both;
border: 1px solid #ccc;
padding: 10px;
}

div.current dd {
padding: 0;
margin: 0;
}
dl.tabs h3{
font-size:1.0em;
}
dl#content-pane.tabs {
margin: 1px 0 0 0;
}
';
$document->addStyleDeclaration($style);
$options = array(
		'onActive' => 'function(title, description){
		description.setStyle("display", "block");
		title.addClass("open").removeClass("closed");
}',
		'onBackground' => 'function(title, description){
		description.setStyle("display", "none");
		title.addClass("closed").removeClass("open");
}',
		// 0 starts on the first tab, 1 starts the second, etc...
		'startOffset' => 0,
		// This must not be a string. Don't use quotes.
		'useCookie' => true,
		);

if (version_compare(JVERSION,'3.0','ge'))
{
	$style = "	select, textarea, input{
	width: auto !important;}";
	$document->addStyleDeclaration($style);
}
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
	<?php
	$options = array('active' => current($this->languages)['tag']);
	echo JHtmlBootstrap::startTabSet('tabs', $options);

	foreach ($this->languages as $lang)
	{
		echo JHtmlBootstrap::addTab('tabs', $lang['tag'], $lang['tag']);
		echo '<table><tr><td>' . $lang['header'] . '<br />';
		?>
	<textarea name="<?php echo $lang['tag']; ?>" cols="100"
		rows="<?php echo $lang['rows']; ?>">
		<?php echo $lang['value']; ?>
	</textarea>
	<br />&nbsp;
	<?php
	echo '</td></tr></table>';
	echo JHtmlBootstrap::endTab();
	}

	echo JHtmlBootstrap::endTabSet();

	?>


	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task" value="" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="translations" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php
if (version_compare(JVERSION,'4.0','lt'))
{
   echo "</div>\n";
}
?>
