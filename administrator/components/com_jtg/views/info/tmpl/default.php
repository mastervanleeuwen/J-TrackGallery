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
ToolbarHelper::title(Text::_('COM_JTG_INFO'), 'generic.png');
ToolbarHelper::back();

$link = Uri::root() . "components/com_jtg/assets/images/logo_JTG.png";
$db = Factory::getDbo();
$query = $db->getQuery(true);
$query->select('manifest_cache');
$query->from($db->quoteName('#__extensions'));
$query->where('element = "com_jtg"');
$db->setQuery($query);

$manifest = json_decode($db->loadResult(), true);
$version = (string) $manifest['version'];

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

<div style="margin: auto">
	<div style="float: left; margin-left: 30px">
		<table>
			<tbody>
				<tr>
					<td colspan="2"><img src="<?php echo $link;?>"
						alt="J!Track Gallery" /></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_DESCRIPTION');?>:</td>
					<td><?php echo Text::_('COM_JTG_INFO_TXT');?></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_CURRENT_INSTALLED_VERSION');?>:</td>
					<td><?php echo $version?></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_LATEST_VERSION');?>:</td>
					<td><?php echo Text::_('COM_JTG_LATEST_VERSION_AT');?></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_DEVELOPPERS');?>:</td>
					<td><a href="<?php echo Text::_('COM_JTG_DEVELOPPERS_WEBSITE');?>">
							<?php echo Text::_('COM_JTG_DEVELOPPERS_LIST');?>
					</a></td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_CHANGELOG_PAGE');?>:</td>
					<td><a href="https://mastervanleeuwen.github.io/J-TrackGallery/releasenotes/" target="_blank">https://mastervanleeuwen.github.io/J-TrackGallery/releasenotes</a>
					</td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_DEMO_PAGE');?>:</td>
					<td><a href="https://jtrackgalleryj4.gta-trek.eu/" target="_blank">https://jtrackgalleryj4.gta-trek.eu/</a>
					</td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_PROJECT_PAGE');?>:</td>
					<td><a href="https://mastervanleeuwen.github.io/J-TrackGallery/" target="_blank">https://mastervanleeuwen.github.io/J-TrackGallery/</a>
					</td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_SUPPORT');?>:</td>
					<td><a href="https://github.com/mastervanleeuwen/J-TrackGallery/issues" target="_blank">https://github.com/mastervanleeuwen/J-TrackGallery/issues</a>
					</td>
				</tr>
				<tr>
					<td><?php echo Text::_('COM_JTG_LICENSE');?>:</td>
					<td><a href="http://www.gnu.org/licenses/gpl-3.0.html"
						target="_blank">GNU/GPLv3</a></td>
				</tr>
			</tbody>
		</table>

	</div>
	<div style="clear: both"></div>
</div>
<?php
echo HTMLHelper::_('form.token');
?>
<input type="hidden"
	name="option" value="com_jtg" />
<input type="hidden" name="id" value="1" />
<input type="hidden" name="task" value="" />
&nbsp;
</form>
</div>
