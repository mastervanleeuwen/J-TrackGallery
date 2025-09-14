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

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');

/**
 * Custom Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	        com_my
 * @since		1.6
 */
class JFormFieldTracklevel extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Tracklevel';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	public function getOptions()
	{
		// Initialize variables.
		$options = array();

		$cfg = JtgHelper::getConfig();
		$levels = explode("\n", $cfg->level);
		array_unshift($levels, 'COM_JTG_LEVEL');
		$i = 0;

		foreach ($levels as $level)
		{
			if (trim($level) != "")
			{
				$label = Text::_(trim($level));
				if ($i != 0) $label = $i.' - '.$label;
				$options[] = array('value' => $i, 'text' => $label);
				$i++;
			}
		}

		return $options;
	}
}
