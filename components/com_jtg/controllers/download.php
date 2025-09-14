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
use Joomla\CMS\Session\Session;

/**
 * JtgControllerDownload class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgControllerDownload extends JtgController
{
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function download()
	{

		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$format = Factory::getApplication()->input->get('format');
		$model = $this->getModel('download');
		$id = Factory::getApplication()->input->get('id');
		$track = $model->getFile($id);
		$trackname = str_replace(' ', '_', $track->title);

		if ($format == 'original')
		{
			jimport('joomla.filesystem.file');
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
}
