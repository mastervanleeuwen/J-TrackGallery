<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
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

define('_PARSETEMPLATE_HEADLINE_OPEN', true);

use Joomla\CMS\Uri\Uri;

/**
 * function_description
 *
 * @param   unknown_type  $linkname     param_description
 * @param   boolean       $printbutton  true if a print button must be added there
 *
 * @return string
 */
function ParseTemplate_Headline_open($linkname, $printbutton = false)
{
	$link = Uri::getInstance()->toString() . "#" . $linkname;
	$link = str_replace("&", "&amp;", $link);

	if ($printbutton)
	{
		// Printing: 'print=1' will only be present in the url of the modal window, not in the presentation of the page
		if (JFactory::getApplication()->input->getInt('print') == 1)
		{
			$printlink = "<a class =\"anchor\" rel=\"nofollow\" title=\"" . JText::_('COM_JTG_CLIC_FOR_PRINTING') .
				"\" href= \"javascript:window.print()\" ><img src=\"".JUri::Root(true)."/components/com_jtg/assets/images/printButton.png\"/>";
			$navlink = "";
		}
		else
		{
			$printhref = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';
			$printhref = "window.open(this.href,'win2','" . $printhref . "'); return false;";
			$printhref = 'href="'.JRoute::_("index.php?option=com_jtg&view=track&id=" .
					JFactory::getApplication()->input->get('id') .
					"&tmpl=component&print=1").'" onclick="'.$printhref.'"';
			$printlink = "<a class =\"anchor\" style=\"display:inline; float:right;width:30px;\" title=\"" .
				JText::_('COM_JTG_PREPARE_FOR_PRINTING') .
				"\" $printhref ><img src=\"".JUri::root(true)."/components/com_jtg/assets/images/printButton.png\"/></a>";
			$navlink = "<a class=\"anchor\" name=\"" . $linkname . "\" href=\"" . $link . "\">";
		}
	}
	else
	{
		$printlink = "";
		$navlink = "<a class=\"anchor\" name=\"" . $linkname . "\" href=\"" . $link . "\">";
	}

	// Headline_close will add "</a></div>"
	return "<div class=\"gps-headline\">$printlink \n $navlink";
}
