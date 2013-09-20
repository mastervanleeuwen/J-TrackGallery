<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5
 *
 * 
 * @author     J!Track Gallery, InJO3SM and joomGPStracks teams
 * @package    com_jtg
 * @subpackage backend
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL2
 * @link       http://jtrackgallery.net/
 *
 */

defined('_JEXEC') or die('Restricted access');

// toolbar
JToolBarHelper::title(JText::_('COM_JTG_POST_INSTALL'), 'generic.png');
JToolBarHelper::back($alt= 'COM_JTG_BACK', $href= 'javascript:history.back();');
// JToolBarHelper::save('saveconfig', $alt='COM_JTG_SAVE', 'save.png' );

// jimport('joomla.html.pane');
// JHTML::_('behavior.tooltip');

$link = ".." . DS . "components" . DS . "com_jtg" . DS . "assets" . DS . "images" . DS . "logo_JTG.png";
$version = $this->getVersion();

// parse some Javascript used to install J!Track Gallery
$doc =& JFactory::getDocument();

$result = '<td colspan="2"><font color=green>'.JText::_('COM_JTG_INSTALL_MAPS_CREATED').'</font></td>';
$this->parsejs($doc,"maps","maps_en","map_result",$result);

$result = '<td colspan="2"><font color=green>'.JText::_('COM_JTG_INSTALL_CATEGORIES_CREATED').'</font></td>';
$this->parsejs($doc,"cats","cats_en","cat_result",$result);

$result = '<td colspan="2"><font color=green>'.JText::_('COM_JTG_INSTALL_TERRAINS_CREATED').'</font></td>';
$this->parsejs($doc,"terrains","terrains_en","terrain_result",$result);

JHTML::_('behavior.mootools'); 

?>

	
	
    <div style="margin: auto">
<div style="float:left; margin-left:30px">
<table class="adminlist" border="1" width="100%">
	<tbody>
		<tr id="post_installation"><td>
			</td><?php echo(JText::_('COM_JTG_POST_INSTALL')); ?><td>
			<font color=red ><?php echo(JText::_('COM_JTG_PRESS_BUTTON')); ?></font>					
			</td></tr>
		<tr><td>
			<?php echo(JText::_('COM_JTG_SAMPLE_MAPS_INSTALL')); ?>
			</td><td id="map_result"><a href="#" id="maps_en"><input type="button" 
			value="<?php echo(JText::_('COM_JTG_SAMPLE_MAPS_BUTTON')); ?>" name="button" class="button" /></a>
		</td></tr>
		<tr><td>
			<?php echo(JText::_('COM_JTG_SAMPLE_CATEGORIES_INSTALL')); ?>
			</td><td id="cat_result"><a href="#" id="cats_en"><input type="button" 
			value="<?php echo(JText::_('COM_JTG_SAMPLE_CATEGORIES_BUTTON')); ?>" name="button" class="button" /></a>
		</td></tr>
		<tr><td>
			<?php echo(JText::_('COM_JTG_TERRAINS_INSTALL')); ?>
			</td><td id="terrain_result"><a href="#" id="terrains_en"><input type="button" 
			value="<?php echo(JText::_('COM_JTG_TERRAINS_BUTTON')); ?>" name="button" class="button" /></a>
		</td></tr>
		<tr><td colspan="2">
			<font color="red" size="+1"><?php echo(JText::_('COM_JTG_HINTS')); ?></font>
			<?php echo(JText::_('COM_JTG_HINTS_DETAILS')); ?>
		</td></tr>

	</tbody>
</table>

</div>
<div style="clear:both"></div>
    </div>

<?php

    echo JHTML::_( 'form.token' );
    ?>
    <input type="hidden" name="option" value="com_jtg" />
    <input type="hidden" name="id" value="1" />
    <input type="hidden" name="task" value="" />
</form>