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
 * Controller Class Files
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgControllerFiles extends JtgController
{
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function updateGeneratedValues ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('files');

		if (! $model->updateGeneratedValues())
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return void
	 */
	function uploadfiles ()
	{
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$jFileInput = JFactory::getApplication()->input->files;
		$files = $jFileInput->get('files');
		$model = $this->getModel('files');
		$dest = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks' .
				'/import/';

		if (! $model->uploadfiles($files, $dest))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		if (JFactory::getApplication()->input->get('toimport'))
		{
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=newfiles&controller=files', false));
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
		}
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

		$model = $this->getModel('files');

		if (! $model->publish($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function tohide ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('files');

		if (! $model->showhide($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function toshow ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('files');

		if (! $model->showhide($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
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

		$model = $this->getModel('files');

		if (! $model->publish($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function accessregistered ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_PUBLISH'), 'Error');
		}

		$model = $this->getModel('files');

		if (! $model->access($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function accessspecial ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('files');

		if (! $model->access($cid, 2))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function accesspublic ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('files');

		if (! $model->access($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
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

		$model = $this->getModel('files');

		if (! $model->delete($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function removeFromImport ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$found = JFactory::getApplication()->input->get('found');

		$model = $this->getModel('files');

		if (! $model->deleteFromImport($found))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=newfiles&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function savefile ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('files');

		if (! $model->saveFile())
		{
			/*
			echo "<script> alert('" . $model->getError(true) . "');
			window.history.go(-1); </script>\n";
			*/
			echo "<script> alert('Error');</script>";
			/*
			 * $this->setRedirect(
			 * JRoute::_('index.php?option=com_jtg&task=files&controller=files',
			 * false ));
			*/
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
		}
	}

	/**
	 * function_description
	 *
	 * @return void
	 */
	function savefiles ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('files');

		if (! $model->saveFiles())
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return void
	 */
	function updatefile ()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('files');

		if (! $model->updateFile())
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=files&controller=files', false));
	}

	/**
	 * function_description
	 *
	 * @return void
	 */
	function fetchJPTfiles ()
	{
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$model = $this->getModel('files');
		echo $model->_fetchJPTfiles();
	}
}
