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

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;

/**
 * JtgViewConfig class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgViewConfig extends HtmlView
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
		$config = JtgHelper::getConfig();

		$captcha = JtgHelper::checkCaptcha();
		$cactiv = ($captcha > 0) ? '<font color="green">'
			. Text::_('COM_JTG_INSTALLED') . '</font>' : '<font color="red">'
			. Text::_('COM_JTG_NOT_INSTALLED') . '</font>';
		$model = $this->getModel();
		$row = $model->getContent();
		$tmpl = $model->getTemplates();

		// Unit array for lists
		$unit = array();
		array_push($unit, array("unit" => "kilometer"));
		array_push($unit, array("unit" => "miles"));

		// Users array for lists
		$users = array(
				array('id' => 0, 'text' => Text::_('COM_JTG_PUBLIC')),
				array('id' => 1, 'text' => Text::_('COM_JTG_REGISTERED'))
		);

		// Yes/no array for lists
		$inform = array(
				array('id' => 1, 'text' => Text::_('JYES')),
				array('id' => 0, 'text' => Text::_('JNO'))
		);

		// Comments order aray for lists
		$order = array(
				array('order' => 'DESC', 'text' => Text::_('COM_JTG_FIRST_NEWEST')),
				array('order' => 'ASC', 'text' => Text::_('COM_JTG_FIRST_OLDEST'))
		);
		$comments = array(
				array('id' => 0, 'text' => Text::_('COM_JTG_NO_COMMENTS')),
				array('id' => 1, 'text' => Text::_('COM_JTG_INTERN_COMMENTS')),
				array('id' => 3, 'text' => Text::_('COM_JTG_JCOMMENTS'))
		);
		$approach = array(
				array('id' => 'no' , 'text' => Text::_('JNO')),
				array('id' => 'ors' , 'text' => Text::_('COM_JTG_APPR_ORS')),
				array('id' => 'cm' , 'text' => Text::_('COM_JTG_APPR_CM'))

				/*
				array('id' => 'cmkey' , 'text' => Text::_('COM_JTG_APPR_CMK'))
					Key muss noch gespeichert werden
					array('id' => 'easy' , 'text' => Text::_('COM_JTG_APPR_EASY'))
					Problem mit Datenbankspeicherung
				*/
				);

		$gallery = array(
				array('id' => 'none', 'text' => Text::_('JNONE')),
				array('id' => 'straight', 'text' => Text::_('COM_JTG_GAL_STRAIGHT')),
				array('id' => 'jd2', 'text' => Text::_('COM_JTG_GAL_JDGALLERY2')),
				array('id' => 'highslide', 'text' => Text::_('COM_JTG_GAL_HIGHSLIDE')),
				array('id' => 'ext_plugin', 'text' => Text::_('COM_JTG_GAL_EXTERNAL_PLUGIN'))
		);
		$routingiconset = array();
		$imgdir = Uri::root() . "components/com_jtg/assets/images/approach/";
		$importdir = JPATH_SITE . "/components/com_jtg/assets/images/approach/";
		$files = Folder::folders($importdir);

		for ($i = 0;$i < count($files);$i++)
		{
			$nopic = "<font color=\"red\"><font size=\"+2\">X</font> (Icon missing) </font>";
			$string = $files[$i] . "<br />" . Text::_('COM_JTG_PREVIEW') . ":&nbsp;&nbsp;";

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

			$routingiconset[] = HTMLHelper::_('select.option', $files[$i], $string);
		}

		if ($row)
		{
			// If article(s) found in section jtg and category term
			$lists['content']		= HTMLHelper::_('select.genericlist', $row, 'terms_id', 'size="1"', 'id', 'title', $config->terms_id);
		}
		else
		{
			$lists['content']		= "<font color=red>" . Text::_('COM_JTG_TT_TERMS_NOTFOUND') . "</font>";
		}

		$lists['unit']				= HTMLHelper::_('select.genericlist', $unit, 'unit', 'size="1"', 'unit', 'unit', $config->unit);
		$lists['tmpl']				= HTMLHelper::_('select.genericlist', $tmpl, 'template', 'size="1"', 'name', 'name', $config->template);
		$lists['inform']			= HTMLHelper::_('select.genericlist', $inform, 'inform_autor', 'size="1"', 'id', 'text', $config->inform_autor);
		$lists['captcha']			= HTMLHelper::_('select.genericlist', $inform, 'captcha', 'size="1"', 'id', 'text', $config->captcha);
		$lists['usevote']			= HTMLHelper::_('select.genericlist', $inform, 'usevote', 'size="1"', 'id', 'text', $config->usevote);
		$lists['uselevel']		= HTMLHelper::_('select.genericlist', $inform, 'uselevel', 'size="1"', 'id', 'text', $config->uselevel);
		$lists['order']				= HTMLHelper::_('select.genericlist', $order, 'ordering', 'size="1"', 'order', 'text', $config->ordering);
		$lists['comments']			= HTMLHelper::_('select.genericlist', $comments, 'comments', 'size="1"', 'id', 'text', $config->comments);
		$lists['access']			= HTMLHelper::_('select.genericlist', $inform, 'access', 'size="1"', 'id', 'text', $config->access);
		$lists['approach']			= HTMLHelper::_('select.genericlist', $approach, 'approach', 'size="1"', 'id', 'text', $config->approach);
		$lists['routingiconset']	= HTMLHelper::_('select.radiolist', $routingiconset, 'routingiconset', null, 'value', 'text', $config->routingiconset);
		$lists['gallery']			= HTMLHelper::_('select.genericlist', $gallery, 'gallery', 'size="1"', 'id', 'text', $config->gallery);

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
				$translevel[] = $i . " - " . Text::_(trim($level));
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
