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
 * Controller Class Categories
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgControllerCats extends JtgController
{
	/**
	 * View method for JTG
	 *
	 * This function override joomla.application.component.controller
	 * View Cache not yet implemented in JTrackGallery
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		parent::display();
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function uploadcatimages ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('cat');
		$success = $model->saveCatImage();

		// Redirect to cats overview
		$link = JRoute::_("index.php?option=com_jtg&controller=cats&task=managecatpics", false);

		if ($success)
		{
			$this->setRedirect($link, JText::_('COM_JTG_CATPIC_SAVED'));
		}
		else
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_CATPIC_NOTSAVED'), 'Warning');
			$this->setRedirect($link);
		}
	}

	/**
	 * function_description
	 *
	 * @uses JtgModelCat::saveCat
	 * @return redirect
	 */
	function savecat ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('cat');
		$success = $model->saveCat();

		// Redirect to cats overview
		$link = JRoute::_("index.php?option=com_jtg&task=cats&controller=cats", false);

		if ($success)
		{
			$this->setRedirect($link, JText::_('COM_JTG_CAT_SAVED'));
		}
		else
		{
			$this->setRedirect($link, JText::_('COM_JTG_CAT_NOT_SAVED'));
		}
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderup ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('cat');
		$model->move(- 1);

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=cats&controller=cats', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderdown ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('cat');
		$model->move(1);

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=cats&controller=cats', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function saveorder ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		$order = JFactory::getApplication()->input->get('order', array(), 'array');
		ArrayHelper::toInteger($cid);
		ArrayHelper::toInteger($order);

		$model = $this->getModel('cat');
		$model->saveorder($order, $cid);

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=cats&controller=cats', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function publish ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_PUBLISH'), 'Error');
		}

		$model = $this->getModel('cat');

		if (! $model->publish($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=cats&controller=cats', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function unpublish ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('cat');

		if (! $model->publish($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=cats&controller=cats', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function removepic ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_DELETE'), 'Error');
		}

		$model = $this->getModel('cat');

		if (! $model->deleteCatImage($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=cats&controller=cats&task=managecatpics', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function remove ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_DELETE'), 'Error');
		}

		$model = $this->getModel('cat');

		if (! $model->delete($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=cats&controller=cats', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function updatecat ()
	{
		// Check the token
		JSession::checkToken() or die('JINVALID_TOKEN');

		$model = $this->getModel('cat');
		$success = $model->updateCat();

		// Redirect to cats overview
		$link = JRoute::_("index.php?option=com_jtg&task=cats&controller=cats", false);

		if ($success)
		{
			$this->setRedirect($link, JText::_('COM_JTG_CAT_SAVED'));
		}
		else
		{
			$this->setRedirect($link, JText::_('COM_JTG_CAT_NOT_SAVED'));
		}
	}
}
