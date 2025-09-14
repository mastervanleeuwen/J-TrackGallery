<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

    echo $this->lh;
    // Don't show description column when no description are set
    $showdescription = false;
    foreach ($this->cats as $cat) {
        if (!empty($cat->description)) $showdescription = true;
    }
    	    
?>
<table class="table tracktable">
	<thead>
		<tr class="sectiontableheader">
			<th colspan="2" width="100px" style="text-align:center"><?php echo Text::_('COM_JTG_CAT'); ?>
			</th>
			<?php if ($showdescription) 
			    echo '<th>'.Text::_('COM_JTG_DESCRIPTION').'</th>'; ?>
			<th><?php echo Text::_('COM_JTG_NTRACK'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$k = 0;
		$imgdir = Uri::base() . "images/jtrackgallery/cats/";

        for ($i = 0, $n = count($this->cats); $i < $n; $i++)
		{
			$cat = $this->cats[$i];
			$cat->img = null;

			if ($cat->image)
			{
				$cat->img = "&nbsp;<img title=\"" . Text::_($cat->title) . "\" alt=\"" . Text::_($cat->title) . "\" src=\"" . $imgdir . $cat->image . "\" />";
			}
			$link = Route::_('index.php?option=com_jtg&view=cat&id=' . $cat->id);
			?>
		<tr>
			<td width="10%" align="center"><a href="<?php echo $link; ?>">
				<?php echo $cat->img; ?>
			</a></td>
			<td><b>
				<?php if ($cat->ntracks) { ?>
				<a href="<?php echo $link; ?>">
				<?php echo Text::_($cat->treename); ?>
				</a> 
				<?php } else echo Text::_($cat->treename); ?>
			</b></td>
			<?php if ($showdescription) 
			echo '<td> '.Text::_($cat->description).' </td>'; ?>
			<td><?php echo $cat->ntracks; ?><td>
		</tr>
		<?php
		}
		?>
	</tbody>
</table>

<?php
echo $this->footer;
