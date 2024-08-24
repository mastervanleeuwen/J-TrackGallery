<?php

defined('_JEXEC') or die;

class JtgHelperRoute
{
	/*
	*  Used to create links for tag list
	*/

	public static function getFileRoute($id, $catid = 0, $language = 0) {
		$link = 'index.php?option=com_jtg&view=track&id=' . $id;
		if ($language && $language !== '*' && Multilanguage::isEnabled())
		{
			$link .= '&lang=' . $language;
		}
		return $link;
	}
}
