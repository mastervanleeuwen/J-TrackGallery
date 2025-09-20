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

jimport('joomla.form.formfield');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');

/**
 * Custom Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	        com_jtg
 * @since		1.6
 */
class JFormFieldTrackcategory extends \JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Trackcategory';

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
		//$options[] = array('value' => "", 'text' => Text::_('COM_JTG_CAT_SELECT')); 

		$db	= Factory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.id As value, a.title As text');
		$query->from('#__jtg_cats AS a');
		$query->order('a.ordering');
		$query->where('published = 1');

		// Get the options.
		$db->setQuery($query);

		$optionsdb = $db->loadObjectList();
		foreach ($optionsdb as $key => &$option) {
			$option->text = Text::_($option->text);
		}
		$options = array_merge($options, $optionsdb);

		return $options;
	}
}
