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

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
/**
 * JtgControllerFiles class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgControllerTrack extends JtgController
{
	/**
	 * function_description
	 *
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Save GPS file with information fields
	 *
	 * @return return_description
	 */
	function save()
	{
		jimport('joomla.filesystem.file');

		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$file = JFactory::getApplication()->input->files->get('file');

		if (!$file['name'])
		{
			echo "<script> alert('" . JText::_('COM_JTG_FILE_UPLOAD_NO_FILE') . "'); window.history.go(-1); </script>\n";
			exit;
		}

		$model = $this->getModel('track');

		$ext = JFile::getExt($file['name']);

		if ($ext == 'kml' || $ext == 'gpx' || $ext == 'tcx')
		{
			if (!$model->saveFile())
			{
				echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
			}

			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=track&id=' . $id, false), false);
		}
		else
		{
			echo "<script> alert('" . $file['name'] . JText::_('COM_JTG_GPS_FILE_ERROR') . "'); window.history.go(-1); </script>\n";
			exit;
		}
	}

	/**
	 * Upload GPX file; goes back to form view for map rendering
	 *
	 * @return return_description
	 */
	function uploadGPX()
	{
      // Check for request forgeries
      JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
      $file = JFactory::getApplication()->input->files->get('file');

		if (!JFactory::getUser()->authorise('core.create', 'com_jtg')) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=jtg',false), false);
			return;
		}
      if (!$file['name'])
      {
         echo "<script> alert('" . JText::_('COM_JTG_FILE_UPLOAD_NO_FILE') . "'); window.history.go(-1); </script>\n";
         exit;
      }

      $model = $this->getModel('track');

      $ext = JFile::getExt($file['name']);

      if ($ext == 'kml' || $ext == 'gpx' || $ext == 'tcx')
		{
			$id = $model->saveFile();
			if (!$id)
			{  
				echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
			}
			else {
				JFactory::getApplication()->setUserState('com_jtg.newfileid',$id);
			}
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=track&layout=form&id=' . $id, false), false);
      }
      else
      {  
         echo "<script> alert('" . $file['name'] . JText::_('COM_JTG_GPS_FILE_ERROR') . "'); window.history.go(-1); </script>\n";
         exit;
      }
	}
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function vote()
	{
		$input = JFactory::getApplication()->input;
		$id = $input->getInt('id');
		$rate = $input->getInt('rate');
		$model = $this->getModel('track');
		$model->vote($id, $rate);

		$msg = JText::_('COM_JTG_VOTED');
		$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=track&id=' . $id, false), false);
	}

	/**
	 * cancel update of track
	 *
	 * @return return_description
	 */
 	function cancel()
   {
		$input = JFactory::getApplication()->input;
		$id = $input->getInt('id');
		if ($id) {
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=track&id='.$id, false), false);
		}
		else {
			$this->setRedirect(JRoute::_('index.php?option=com_jtg', false), false);
		}
	}
	/**
	 * delete track
	 *
	 * @return return_description
	 */
	function delete()
	{
		$id = JFactory::getApplication()->input->getInt('id');
		$model = $this->getModel("Track");

		if (!$model->deleteFile($id))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=files&layout=user', false), false);
		}
	}

	/**
	 * delete new track
	 * (called from form field when canceling after uploading a file)
	 *
	 * @return return_description
	 */
	function deletenew()
	{
		$id = JFactory::getApplication()->getUserState('com_jtg.newfileid');
		$model = $this->getModel('track');

		if (!$model->deleteFile($id))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=files&layout=user', false), false);
		}
	}
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function update()
	{
		jimport('joomla.filesystem.file');
		$user		= JFactory::getUser();

		if (!$user->get('id'))
		{

			$this->setRedirect(JRoute::_('index.php?option=com_jtg', false), false);
		}

		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$id = JFactory::getApplication()->input->getInt('id');

		$model = $this->getModel('track');
		$errormsg = $model->updateFile($id);

		if ($errormsg !== true)
		{
			echo "<script> alert('Error: \"" . $errormsg . "\"'); window.history.go(-1); </script>\n";
		}
		else
		{
			JFactory::getApplication()->setUserState('com_jtg.newfileid',-1);
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=track&id=' . $id, false), false);
		}
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function addcomment()
	{
		$model = $this->getModel('track');
		$model->addcomment();
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function savecomment()
	{
		$mainframe = JFactory::getApplication();

		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$cfg = JtgHelper::getConfig();
		$id = JFactory::getApplication()->input->getInt('id');

		if ($cfg->captcha == 1)
		{
			$return = false;
			$word = JFactory::getApplication()->input->get('word', false, '', 'CMD');
			$mainframe->triggerEvent('onCaptcha_confirm', array($word, &$return));

			if (!$return)
			{
				echo "<script> alert('" . JText::_('COM_JTG_CAPTCHA_WRONG') . "'); window.history.go(-1); </script>\n";
			}
			else
			{
				$model = $this->getModel('track');

				if (!$model->savecomment($id, $cfg))
				{
					$msg = JText::_('COM_JTG_COMMENT_NOT_SAVED');
				}
				else
				{
					$msg = JText::_('COM_JTG_COMMENT_SAVED');
				}

				$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=track&id=' . $id . '#jtg_param_header_comment', false), $msg);
			}
		}
		else
		{
			$model = $this->getModel('track');

			if (!$model->savecomment($id, $cfg))
			{
				$msg = JText::_('COM_JTG_COMMENT_NOT_SAVED');
			}
			else
			{
				$msg = JText::_('COM_JTG_COMMENT_SAVED');
			}

			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=track&id=' . $id . '#jtg_param_header_comment', false), $msg);
		}
	}
}
