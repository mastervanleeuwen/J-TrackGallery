<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

define('_PARSETEMPLATE_SUBHEADLINE_OPEN', true);

use Joomla\CMS\Uri\Uri;

function ParseTemplate_Subheadline_open($linkname)
{
	$link = Uri::getInstance()->toString() . "#" . $linkname;
	$link = str_replace("&", "&amp;", $link);
	return "<h2 class=\"gps-subheadline\"><a class=\"anchor\" name=\"" . $linkname . "\" href=\"" . $link . "\">";
}
?>
