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

// Import Joomla! libraries
jimport('joomla.application.component.view');
/**
 * JtgViewConfig class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgViewConfig extends JViewLegacy
{
	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	public function display($tpl = null)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$config = JtgHelper::getConfig();

		$captcha = JtgHelper::checkCaptcha();
		$cactiv = ($captcha > 0) ? '<font color="green">'
			. JText::_('COM_JTG_INSTALLED') . '</font>' : '<font color="red">'
			. JText::_('COM_JTG_NOT_INSTALLED') . '</font>';
		$model = $this->getModel();
		$row = $model->getContent();
		$tmpl = $model->getTemplates();

		// Unit array for lists
		$unit = array();
		array_push($unit, array("unit" => "kilometer"));
		array_push($unit, array("unit" => "miles"));

		// Users array for lists
		$users = array(
				array('id' => 0, 'text' => JText::_('COM_JTG_PUBLIC')),
				array('id' => 1, 'text' => JText::_('COM_JTG_REGISTERED'))
		);

		// Yes/no array for lists
		$inform = array(
				array('id' => 1, 'text' => JText::_('JYES')),
				array('id' => 0, 'text' => JText::_('JNO'))
		);

		// Comments order aray for lists
		$order = array(
				array('order' => 'DESC', 'text' => JText::_('COM_JTG_FIRST_NEWEST')),
				array('order' => 'ASC', 'text' => JText::_('COM_JTG_FIRST_OLDEST'))
		);
		$comments = array(
				array('id' => 0, 'text' => JText::_('COM_JTG_NO_COMMENTS')),
				array('id' => 1, 'text' => JText::_('COM_JTG_INTERN_COMMENTS')),
				array('id' => 3, 'text' => JText::_('COM_JTG_JCOMMENTS'))
		);
		$approach = array(
				array('id' => 'no' , 'text' => JText::_('JNO')),
				array('id' => 'ors' , 'text' => JText::_('COM_JTG_APPR_ORS')),
				array('id' => 'cm' , 'text' => JText::_('COM_JTG_APPR_CM'))

				/*
				array('id' => 'cmkey' , 'text' => JText::_('COM_JTG_APPR_CMK'))
					Key muss noch gespeichert werden
					array('id' => 'easy' , 'text' => JText::_('COM_JTG_APPR_EASY'))
					Problem mit Datenbankspeicherung
				*/
				);

		$gallery = array(
				array('id' => 'none', 'text' => JText::_('JNONE')),
				array('id' => 'straight', 'text' => JText::_('COM_JTG_GAL_STRAIGHT')),
				array('id' => 'jd2', 'text' => JText::_('COM_JTG_GAL_JDGALLERY2')),
				array('id' => 'highslide', 'text' => JText::_('COM_JTG_GAL_HIGHSLIDE')),
				array('id' => 'ext_plugin', 'text' => JText::_('COM_JTG_GAL_EXTERNAL_PLUGIN'))
		);
		$routingiconset = array();
		$imgdir = JUri::root() . "components/com_jtg/assets/images/approach/";
		$importdir = JPATH_SITE . "/components/com_jtg/assets/images/approach/";
		$files = JFolder::folders($importdir);

		for ($i = 0;$i < count($files);$i++)
		{
			$nopic = "<font color=\"red\"><font size=\"+2\">X</font> (Icon missing) </font>";
			$string = $files[$i] . "<br />" . JText::_('COM_JTG_PREVIEW') . ":&nbsp;&nbsp;";

			if (is_file($importdir . $files[$i] . "/car.png"))
			{
				$string .= "<img src=\"" . $imgdir . $files[$i] . "/car.png\" alt=\"car.png\" title=\"car.png\" /> ";
			}
			else
			{
				$string .= $nopic;
			}

			if (is_file($importdir . $files[$i] . "/bike.png"))
			{
				$string .= "<img src=\"" . $imgdir . $files[$i] . "/bike.png\" alt=\"bike.png\" title=\"bike.png\" /> ";
			}
			else
			{
				$string .= $nopic;
			}

			if (is_file($importdir . $files[$i] . "/foot.png"))
			{
				$string .= "<img src=\"" . $imgdir . $files[$i] . "/foot.png\" alt=\"foot.png\" title=\"foot.png\" />";
			}
			else
			{
				$string .= $nopic;
			}

			if ($i < count($files) - 1)
			{
				$string .= "<br /><br /><br />";
			}

			$routingiconset[] = JHtml::_('select.option', $files[$i], $string);
		}

		if ($row)
		{
			// If article(s) found in section jtg and category term
			$lists['content']		= JHtml::_('select.genericlist', $row, 'terms_id', 'size="1"', 'id', 'title', $config->terms_id);
		}
		else
		{
			$lists['content']		= "<font color=red>" . JText::_('COM_JTG_TT_TERMS_NOTFOUND') . "</font>";
		}

		$lists['unit']				= JHtml::_('select.genericlist', $unit, 'unit', 'size="1"', 'unit', 'unit', $config->unit);
		$lists['tmpl']				= JHtml::_('select.genericlist', $tmpl, 'template', 'size="1"', 'name', 'name', $config->template);
		$lists['inform']			= JHtml::_('select.genericlist', $inform, 'inform_autor', 'size="1"', 'id', 'text', $config->inform_autor);
		$lists['captcha']			= JHtml::_('select.genericlist', $inform, 'captcha', 'size="1"', 'id', 'text', $config->captcha);
		$lists['usevote']			= JHtml::_('select.genericlist', $inform, 'usevote', 'size="1"', 'id', 'text', $config->usevote);
		$lists['order']				= JHtml::_('select.genericlist', $order, 'ordering', 'size="1"', 'order', 'text', $config->ordering);
		$lists['comments']			= JHtml::_('select.genericlist', $comments, 'comments', 'size="1"', 'id', 'text', $config->comments);
		$lists['access']			= JHtml::_('select.genericlist', $inform, 'access', 'size="1"', 'id', 'text', $config->access);
		$lists['approach']			= JHtml::_('select.genericlist', $approach, 'approach', 'size="1"', 'id', 'text', $config->approach);
		$lists['routingiconset']	= JHtml::_('select.radiolist', $routingiconset, 'routingiconset', null, 'value', 'text', $config->routingiconset);
		$lists['gallery']			= JHtml::_('select.genericlist', $gallery, 'gallery', 'size="1"', 'id', 'text', $config->gallery);

		if ( $config->level == "" )
		{
			$rows = 6;
		}
		else
		{
			$rows = explode("\n", $config->level);
			$rows = (int) count($rows) + 2;
		}

		$translevel = array();
		$levels = $config->level;
		$levels = explode("\n", $levels);
		$i = 1;

		foreach ($levels as $level)
		{
			if ( trim($level) != "" )
			{
				$translevel[] = $i . " - " . JText::_(trim($level));
				$i++;
			}
		}

		$lists['translevel']		= "<textarea disabled=\"disabled\" cols=\"50\" rows=\"" . $rows . "\" >" . implode("\n", $translevel) . "</textarea>";
		$lists['level']				= "<textarea name=\"level\" cols=\"50\" rows=\"" . $rows . "\" >" . $config->level . "</textarea>";

		$this->config = $config;
		$this->lists = $lists;
		$this->captcha = $cactiv;
		parent::display($tpl);
	}
}
