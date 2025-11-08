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
namespace Jtg\Component\Jtg\Site\Controller;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;

/**
 * JtgControllerFiles class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class TrackController extends BaseController
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
	public function display ($cachable = false, $urlparams = [])
	{
		// Update the hit count for the file
		if ($input->get('view') == 'track')
		{
			$model = $this->getModel('track');
			$model->hit();
		}
		parent::display($cachable, $urlparams);
	}
	
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function download()
	{

		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$format = Factory::getApplication()->input->get('format');
		$model = $this->getModel();
		$id = Factory::getApplication()->input->get('id');
		$track = $model->getFile($id);
		$trackname = str_replace(' ', '_', $track->title);

		if ($format == 'original')
		{
			$output_format = File::getext($track->file);
			$trackname = str_replace('.' . $output_format, '', $track->file);
		}
		else
		{
			$output_format = $format;

		}

		if ($format == "kml")
		{
			$mime = "application/vnd.google-earth.kml+xml";
		}
		else
		{
			$mime = "application/octet-stream";
		}

		header("Pragma: public");
		header("Content-Type: " . $mime . "; charset=UTF-8");
		header("Content-Disposition: attachment; filename=\"" . $trackname . "." . strtolower($output_format) . "\"");
		//header("Content-Transfer-Encoding:binary"); // Causes an error with speeady loading on Google Chrome
		header("Cache-Control: post-check=0, pre-check=0");
		echo $model->download($id, $format, $track);
		exit; // Needed for iOS
	}

	/**
	 * function_description
	 *
	 * @param   integer  $id  param_description
	 *
	 * @return object
	 */
	function getFile($id)
	{
		$app = Factory::getApplication();
		$db = $this->getDbo();

		$query = "SELECT a.*, b.title AS cat, b.image AS image, c.name AS user"
		. "\n FROM #__jtg_files AS a"
		. "\n LEFT JOIN #__jtg_cats AS b ON a.catid=b.id"
		. "\n LEFT JOIN #__users AS c ON a.uid=c.id"
		. "\n WHERE a.id='" . $id . "'";

		$db->setQuery($query);
		$track = $db->loadObject();

		return $track;
	}
	/**
	 * Save GPS file with information fields
	 *
	 * @return return_description
	 */
	function save()
	{
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
		$user		= Factory::getUser();

		if (!$user->get('id'))
		{

			$this->setRedirect(Route::_('index.php?option=com_jtg', false), false);
		}

		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));
		$id = Factory::getApplication()->input->getInt('id');

		$model = $this->getModel(); // removed 'track' argument
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
