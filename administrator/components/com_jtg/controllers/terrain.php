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

jimport('joomla.application.component.controller');
use Joomla\Utilities\ArrayHelper;

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_jtg/tables');

/**
 * Controller Class terrainegories
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgControllerTerrain extends JtgController
{
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function save()
	{
		$mainframe = JFactory::getApplication();

		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('terrain');
		$model->save();

		// Redirect to terrains overview
		$link = JRoute::_("index.php?option=com_jtg&task=terrain&controller=terrain", false);
		$this->setRedirect($link, JText::_('COM_JTG_TERRAIN_SAVED'));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function update()
	{
		$mainframe = JFactory::getApplication();

		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('terrain');
		$model->save();

		// Redirect to terrains overview
		$link = JRoute::_("index.php?option=com_jtg&task=terrain&controller=terrain", false);
		$this->setRedirect($link, JText::_('COM_JTG_TERRAIN_UPDATED'));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_PUBLISH'), 'Error');
		}

		$model = $this->getModel('terrain');

		if (!$model->publish($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=terrain&controller=terrain', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function unpublish()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('terrain');

		if (!$model->publish($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=terrain&controller=terrain', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function remove()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_DELETE'), 'Error');
		}

		$model = $this->getModel('terrain');

		if (!$model->delete($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=terrain&controller=terrain', false));
	}
}
