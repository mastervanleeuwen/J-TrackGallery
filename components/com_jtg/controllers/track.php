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

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

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
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$file = Factory::getApplication()->input->files->get('file');

		if (!$file['name'])
		{
			echo "<script> alert('" . Text::_('COM_JTG_FILE_UPLOAD_NO_FILE') . "'); window.history.go(-1); </script>\n";
			exit;
		}

		$model = $this->getModel('track');

		$ext = File::getExt($file['name']);

		if ($ext == 'kml' || $ext == 'gpx' || $ext == 'tcx')
		{
			if (!$model->saveFile())
			{
				echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
			}

			$this->setRedirect(Route::_('index.php?option=com_jtg&view=track&id=' . $id, false), false);
		}
		else
		{
			echo "<script> alert('" . $file['name'] . Text::_('COM_JTG_GPS_FILE_ERROR') . "'); window.history.go(-1); </script>\n";
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
      Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
      $file = Factory::getApplication()->input->files->get('file');

		if (!Factory::getUser()->authorise('core.create', 'com_jtg')) {
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(Route::_('index.php?option=com_jtg&view=jtg',false), false);
			return;
		}
      if (!$file['name'])
      {
         echo "<script> alert('" . Text::_('COM_JTG_FILE_UPLOAD_NO_FILE') . "'); window.history.go(-1); </script>\n";
         exit;
      }

      $model = $this->getModel('track');

      $ext = File::getExt($file['name']);

      if ($ext == 'kml' || $ext == 'gpx' || $ext == 'tcx')
		{
			$id = $model->saveFile();
			if (!$id)
			{  
				echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
			}
			else {
				Factory::getApplication()->setUserState('com_jtg.newfileid',$id);
			}
			$this->setRedirect(Route::_('index.php?option=com_jtg&view=track&layout=form&id=' . $id, false), false);
      }
      else
      {  
         echo "<script> alert('" . $file['name'] . Text::_('COM_JTG_GPS_FILE_ERROR') . "'); window.history.go(-1); </script>\n";
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
		$input = Factory::getApplication()->input;
		$id = $input->getInt('id');
		$rate = $input->getInt('rate');
		$model = $this->getModel('track');
		$model->vote($id, $rate);

		$msg = Text::_('COM_JTG_VOTED');
		$this->setRedirect(Route::_('index.php?option=com_jtg&view=track&id=' . $id, false), false);
	}

	/**
	 * cancel update of track
	 *
	 * @return return_description
	 */
 	function cancel()
   {
		$input = Factory::getApplication()->input;
		$id = $input->getInt('id');
		if ($id) {
			$this->setRedirect(Route::_('index.php?option=com_jtg&view=track&id='.$id, false), false);
		}
		else {
			$this->setRedirect(Route::_('index.php?option=com_jtg', false), false);
		}
	}
	/**
	 * delete track
	 *
	 * @return return_description
	 */
	function delete()
	{
		$id = Factory::getApplication()->input->getInt('id');
		$model = $this->getModel("Track");

		if (!$model->deleteFile($id))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}
		else
		{
			$this->setRedirect(Route::_('index.php?option=com_jtg&view=files&layout=user', false), false);
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
		$id = Factory::getApplication()->getUserState('com_jtg.newfileid');
		$model = $this->getModel('track');

		if (!$model->deleteFile($id))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}
		else
		{
			// TODO: can we return to the page where we came from instead?
			$this->setRedirect(Route::_('index.php?option=com_jtg&view=files&layout=user', false), false);
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
		$user		= Factory::getUser();

		if (!$user->get('id'))
		{

			$this->setRedirect(Route::_('index.php?option=com_jtg', false), false);
		}

		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$id = Factory::getApplication()->input->getInt('id');

		$model = $this->getModel('track');
		$errormsg = $model->updateFile($id);

		if ($errormsg !== true)
		{
			echo "<script> alert('Error: \"" . $errormsg . "\"'); window.history.go(-1); </script>\n";
		}
		else
		{
			Factory::getApplication()->setUserState('com_jtg.newfileid',-1);
			$this->setRedirect(Route::_('index.php?option=com_jtg&view=track&id=' . $id, false), false);
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
		$mainframe = Factory::getApplication();

		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$cfg = JtgHelper::getConfig();
		$id = Factory::getApplication()->input->getInt('id');

		if ($cfg->captcha == 1)
		{
			$return = false;
			$word = Factory::getApplication()->input->get('word', false, '', 'CMD');
			$mainframe->triggerEvent('onCaptcha_confirm', array($word, &$return));

			if (!$return)
			{
				echo "<script> alert('" . Text::_('COM_JTG_CAPTCHA_WRONG') . "'); window.history.go(-1); </script>\n";
			}
			else
			{
				$model = $this->getModel('track');

				if (!$model->savecomment($id, $cfg))
				{
					$msg = Text::_('COM_JTG_COMMENT_NOT_SAVED');
				}
				else
				{
					$msg = Text::_('COM_JTG_COMMENT_SAVED');
				}

				$this->setRedirect(Route::_('index.php?option=com_jtg&view=track&id=' . $id . '#jtg_param_header_comment', false), $msg);
			}
		}
		else
		{
			$model = $this->getModel('track');

			if (!$model->savecomment($id, $cfg))
			{
				$msg = Text::_('COM_JTG_COMMENT_NOT_SAVED');
			}
			else
			{
				$msg = Text::_('COM_JTG_COMMENT_SAVED');
			}

			$this->setRedirect(Route::_('index.php?option=com_jtg&view=track&id=' . $id . '#jtg_param_header_comment', false), $msg);
		}
	}
}
