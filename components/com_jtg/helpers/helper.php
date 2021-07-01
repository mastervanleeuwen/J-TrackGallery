<?php
/**
 * test code
 *
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

/**
 * function jtgdebug($val, $die=false)
 *
 * @param   unknown_type  $val  param_description
 * @param   unknown_type  $die  param_description
 *
 * @return void
 */

function jtgdebug($val, $die = false)
{
	$r = "<pre>";

	if ( is_array($val) )
	{
		foreach ($val AS $k => $v)
		{
			$r .= $k . " = " . print_r($v, true) . "<br />\n";
		}
	}
	else
	{
		$r .= print_r($val) . "<br />\n";
	}

	$r .= "</pre>";

	if ( $die !== false )
	{
		die($r);
	}

	echo $r;
}

/**
 * JtgHelper class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */

	static public function addSubmenu($vName)
	{
		// TODO move addSubmenu and GetConfig function to backend code
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_CONFIGURATION'),
				'index.php?option=com_jtg&task=config&controller=config',
				$vName == 'config'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_GPS_FILES'),
				'index.php?option=com_jtg&task=files&controller=files',
				$vName == 'config'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_MAPS'),
				'index.php?option=com_jtg&task=maps&controller=maps',
				$vName == 'config'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_CATEGORIES'),
				'index.php?option=com_jtg&task=cats&controller=cats',
				$vName == 'config'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_TERRAIN'),
				'index.php?option=com_jtg&task=terrain&controller=terrain',
				$vName == 'config'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_COMMENTS'),
				'index.php?option=com_jtg&task=comments&controller=comments',
				$vName == 'config'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_TRANSLATE'),
				'index.php?option=com_jtg&task=translations&controller=translations',
				$vName == 'config'
		);
		JSubMenuHelper::addEntry(
				JText::_('COM_JTG_INFO'),
				'index.php?option=com_jtg&task=info&controller=info',
				$vName == 'config'
		);

		// Groups and Levels are restricted to core.admin
		// $canDo = self::getActions(); ...
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $tid  param_description
	 *
	 * @return return_description
	 */
	static public function howMuchVote($tid)
	{
		$db = JFactory::getDBO();
		$query = "SELECT COUNT(id) FROM #__jtg_votes"
		. "\n WHERE trackid='" . $tid . "'";
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $surface    param_description
	 * @param   unknown_type  $filetypes  param_description
	 * @param   unknown_type  $track      param_description
	 *
	 * @return return_description
	 */
	static public function giveGeneratedValues($surface, $filetypes, $track)
	{
		switch ($surface)
		{
			case 'backend':
				break;

			case 'frontend':
				break;

			default:
				return "No frontend|backend given!";
				break;
		}

		if ( ( $track->start_n == null ) OR ( $track->start_e == null ) )
		{
			$error = "<font color=red> (" . JText::_('Error') . ")</font> ";
			$north = 0;
			$east = 0;
			$osm = null;
		}
		else
		{
			$error = null;
			$north = $track->start_n;
			$east = $track->start_e;
			$osm = " <a href='http://www.openstreetmap.org/?mlat=" . $north . "&mlon=" . $east . "&zoom=18' target='_blank' >OpenStreetMap</a>";
		}

		$values = JText::_('COM_JTG_COORDS') . $error . ": " . $north . ", " . $east . $osm;
		$distance = (float) $track->distance;

		if ( $distance != 0 )
		{
			$km = self::getLocatedFloat($distance);
			$miles = self::getMiles($distance);
			$miles = self::getLocatedFloat($miles);
			$distance = $km . " Km (" . $miles . " Miles)";
		}
		else
		{
			$distance = 0;
		}

		$distance = JText::_('COM_JTG_DISTANCE') . ": " . $distance;
		$ele_asc = JText::_('COM_JTG_ELEVATION_UP') . ": " . (float) $track->ele_asc;
		$ele_desc = JText::_('COM_JTG_ELEVATION_DOWN') . ": " . (float) $track->ele_desc;
		$voted = self::howMuchVote($track->id);

		if ( ( $voted != 0 ) AND ( (float) $track->vote == 0 ) )
		{
			// When gevoted wurde aber Voting gleich 0
			// If voted but voting = 0
			$error = "<font color=red> (" . JText::_('Error') . "?)</font> ";
		}
		else
		{
			$error = null;
		}

		$voted = JText::sprintf('COM_JTG_MENU_LIMIT_CONSTRUCT_VOTED', $voted) . $error;
		$vote = (float) $track->vote;
		$vote = self::getLocatedFloat($vote);
		$vote = JText::sprintf('COM_JTG_MENU_LIMIT_CONSTRUCT_VOTE', $vote) . $error;
		$button = "<button class=\"button\" type=\"button\" onclick=\"submitbutton('updateGeneratedValues')\">" . JText::_('COM_JTG_REFRESH_DATAS') . "</button>";

		return "<ul><li>"
		. $filetypes
		. "</li><li>"
		. $values
		. "</li><li>"
		. $distance
		. "</li><li>"
		. $ele_asc
		. "</li><li>"
		. $ele_desc
		. "</li><li>"
		. $vote
		. "</li><li>"
		. $voted
		. "</li></ul>"
		. $button;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $file  param_description
	 * @param   unknown_type  $dest  param_description
	 *
	 * @return return_description
	 */
	static public function uploadfile($file, $dest)
	{

		if ( ( $file["error"] != 0 )
			OR ( $file["size"] == 0 ))
		{
			return false;
		}

		jimport('joomla.filesystem.file');
		$filename = JFile::makeSafe($file['name']);
		$randnumber = (50 - strlen($filename));
		$fncount = 0;
		while (true)
		{
			if (!JFile::exists($dest . $filename))
			{
				if (!JFile::upload($file['tmp_name'], $dest . $filename))
				{
					return false;
				}
				else
				{
					chmod($dest . strtolower($filename), 0664);
				}

				break;
			}
			else
			{
				$filename = $fncount . JFile::makeSafe($file['name']);

				// Man weiß ja nie ;)
				if ( $fncount > 10000 )
				{
					JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_ERROR_NO_FREE_FILENAMES') . "(" . JFile::makeSafe($file['name']) . ")", 'Error');
				}

				$fncount++;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $allcats     param_description
	 * @param   integer       $catid       category ID
	 * @param   unknown_type  $format      param_description
	 * @param   unknown_type  $link        param_description
	 * @param   integer       $iconheight  height of icons
	 *
	 * @return return_description
	 */
	static public function parseMoreCats($allcats, $catid, $format = "array", $link = false, $iconheight = 24)
	{
		$baseurl = "index.php?option=com_jtg&view=files&layout=list&cat=";
		$image = JUri::base() . 'images/jtrackgallery/cats/';
		$catids = explode(",", $catid);
		$return = array();
		$height = ($iconheight > 0? ' style="max-height:' . $iconheight . 'px" ' : '');

		switch ($format)
		{
			case "box":
				if ( ( $link === false ) OR ( $catid == "0" ) )
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]->title) )
						{
							$return[] = JText::_($allcats[$catid]->title);
						}
					}
				}
				else
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]->id) )
						{
							$url = JRoute::_($baseurl . $allcats[$catid]->id, true);
							$return[] = "<a href=\"" . $url . "\">" .
									JText::_($allcats[$catid]->title) .
									"</a>";
						}
					}
				}

				$return = implode(", ", $return);
				break;

			case "TrackDetails":
				if ( ( $link === false ) OR ( $catid == "0" ) )
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]->title) )
						{
							$return[] = JText::_($allcats[$catid]->title);
						}
					}
				}
				else
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]->id) )
						{
							$url = JRoute::_($baseurl . $allcats[$catid]->id, true);

							if ( $allcats[$catid]->image != "")
							{
								$return[] = "<a href=\"" . $url . "\">" .
										"<img $height title=\"" . JText::_($allcats[$catid]->title) . "\" alt=\"" . JText::_($allcats[$catid]->title) . "\" src=\"" . $image . $allcats[$catid]->image . "\" />&nbsp;" .
										"</a>";
							}
							else
							{
								$return[] = "<a href=\"" . $url . "\">" .
										JText::_($allcats[$catid]->title) .
										"</a>";
							}
						}
					}
				}

				$return = implode(", ", $return);
				break;

			case "Images":
				if ( ( $link === false ) OR ( $catid == "0" ) )
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]->title) )
						{
							$return[] = JText::_($allcats[$catid]->title);
						}
					}
				}
				else
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]->id) )
						{
							$url = JRoute::_($baseurl . $allcats[$catid]->id, true);

							if ( $allcats[$catid]->image == "" )
							{
								$return[] = "<a href=\"" . $url . "\">" . JText::_($allcats[$catid]->title) . "</a>";
							}
							else
							{
								$return[] = "<a href=\"" . $url . "\"><img $height title=\"" . JText::_($allcats[$catid]->title)
								. "\" alt=\"" . JText::_($allcats[$catid]->title) . "\" src=\"" . $image
								. $allcats[$catid]->image . "\" /></a>";
							}
						}
					}
				}

				$return = implode(", ", $return);
				break;

			case "list":
				if ( ( $link === false ) OR ( $catid == "0" ) )
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]->title) )
						{
							$return[] = JText::_($allcats[$catid]->title);
						}
					}
				}
				else
				{
					foreach ($catids as $catid)
					{
						if ( isset($allcats[$catid]))
						{
							$url = JRoute::_($baseurl . $allcats[$catid]->id, true);

							if ( $allcats[$catid]->image == "" )
							{
								$return[] = "<a href=\"" . $url . "\">" . JText::_($allcats[$catid]->title) . "</a>";
							}
							else
							{
								$return[] = "<a href=\"" . $url . "\"><img $height title=\"" . JText::_($allcats[$catid]->title)
								. "\" alt=\"" . JText::_($allcats[$catid]->title) . "\" src=\""
								. $image . $allcats[$catid]->image . "\" /></a>";
							}
						}
					}
				}

				$return = implode(" ", $return);
				break;

			case "array":
			default:
				foreach ($catids as $catid)
				{
					if ( isset($allcats[$catid]))
					{
						$return[] = JText::_($allcats[$catid]->title);
					}
				}
				break;
		}

		return $return;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $allterrains  param_description
	 * @param   unknown_type  $terrainid    param_description
	 * @param   unknown_type  $format       param_description
	 * @param   unknown_type  $link         param_description
	 *
	 * @return return_description
	 */
	static public function parseMoreTerrains($allterrains, $terrainid, $format = "array", $link = false)
	{
		$baseurl = "index.php?option=com_jtg&view=files&layout=list&terrain=";
		$image = JUri::base() . 'images/jtrackgallery/terrain/';
		$terrainids = explode(",", $terrainid);
		$return = array();

		switch ($format)
		{
			case "list":
				if ( ( $link === false ) OR ( $terrainid == "0" ) )
				{
					foreach ($terrainids as $terrainid)
					{
						$return[] = JText::_($allterrains[$terrainid]->title);
					}
				}
				else
				{
					foreach ($terrainids as $terrainid)
					{
						if ( isset($allterrains[$terrainid]) )
						{
							$url = JRoute::_($baseurl . $allterrains[$terrainid]->id, false);
							$return[] = "<a href=\"" . $url . "\">" .
									JText::_($allterrains[$terrainid]->title) . "</a>";
						}
					}
				}

				$return = implode(", ", $return);
				break;
			case "array":
			default:
				foreach ($terrainids as $terrainid)
				{
					if ( isset($allterrains[$terrainid]) )
					{
						$return[] = JText::_($allterrains[$terrainid]->title);
					}
				}
				break;
		}

		if ( $return == "" )
		{
			$return = "<label title=\"" . JText::_('COM_JTG_TERRAIN_NONE') . "\">-</label>";
		}

		return $return;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	static public function userHasCommentsRights()
	{
		$user_groups = JFactory::getUser()->getAuthorisedGroups();

		// Admin (root) is not allowed excepted if explicitly given the right to manage front-end.
		// If ( JFactory::getUser()->get('isRoot') ) { return true;};

		if (!$user_groups)
		{
			return false;
		}
		// Seems $user_groups is never empty !!
		$cfg_id = unserialize(self::getConfig()->comment_who);

		if (!$cfg_id )
		{
			return false;
		}

		foreach ($cfg_id as $key => $group)
		{
			if (array_search($group, $user_groups) )
			{
				return true;
			}
		}

		return  false;
	}

	/**
	 * function_description
	 * @params int trkuid User ID of track that is being shown (for 'own track' rights)
	 *
	 * @return bool true if user has FrontEnd rights (for uploading tracks)
	 */
	static public function userHasFrontendRights($trkuid = null)
	{
		$user_groups = JFactory::getUser()->getAuthorisedGroups();

		// Admin (root) is not allowed excepted if explicitly given the right to manage front-end.

		// User can edit their own tracks
		if (JFactory::getUser()->id === $trkuid) return true;
		if (!$user_groups)
		{
			return false;
		}
		// Seems $user_groups is never empty !!
		$cfg_id = unserialize(self::getConfig()->gid);

		if (!$cfg_id )
		{
			return false;
		}

		foreach ($cfg_id as $key => $group)
		{
			if (array_search($group, $user_groups) )
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * function_description
	 *
	 * @return bool true if user has FrontEnd rights (for deleting tracks)
	 */
	static public function userHasFrontendDeleteRights($trkuid=null)
	{
		$user_groups = JFactory::getUser()->getAuthorisedGroups();

		// Admin (root) is not allowed excepted if explicitly given the right to delte in front-end.

		if (!$user_groups)
		{
			return false;
		}
		// Seems $user_groups is never empty !!
		$cfg_id = unserialize(self::getConfig()->deletegid);

		if (!$cfg_id )
		{
			return false;
		}

		foreach ($cfg_id as $key => $group)
		{
			if (array_search($group, $user_groups) )
			{
				return true;
			}
		}

		return false;
	}


	/**
	 * function_description
	 *
	 * @param   integer  $accesslevel  param_description
	 * @param   string   $name         the select name
	 * @param   string   $js           javascript string to add to select
	 *
	 * @return string accesslist select
	 */
	static public function getAccessList($accesslevel=0, $name='access' , $js=null)
	{
		$access = array (
				array (
						'id' => 9,
						'text' => JText::_('COM_JTG_PRIVATE')
				),
				array (
						'id' => 0,
						'text' => JText::_('COM_JTG_PUBLIC')
				),
				array (
						'id' => 1,
						'text' => JText::_('COM_JTG_REGISTERED')
				),
				array (
						'id' => 2,
						'text' => JText::_('COM_JTG_ADMINISTRATORS')
				)
		);

		return JHtml::_('select.genericlist', $access, $name, 'class="inputbox" size="4" ' . $js, 'id', 'text', $accesslevel);

	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	static public function giveAccessLevel()
	{
		$user = JFactory::getUser();

		if (!$user->id)
		{
			// Guest
			return 0;
		}
		elseif ( $user->get('isRoot') )
		{
			// Admin
			return 2;
		}
		else
		{
			// Registered ($id>0)
			return 1;
		}
	}

	/**
	 * function_description
	 *
	 * @return object
	 */
	static public function getConfig()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

		$query = "SELECT * FROM #__jtg_config WHERE id='1'";
		$db->setQuery($query);
		$result = $db->loadObject();

		if ($db->getErrorNum())
		{
			echo $db->stderr();

			return false;
		}

		return $result;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	static public function checkCaptcha()
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();

		$query = "SELECT extension_id FROM #__extensions WHERE element='captcha'";
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Fetchs lat/lon from users given ID, otherwise from all users
	 *
	 * @param   unknown_type  $uid      param_description
	 * @param   unknown_type  $exclude  param_description
	 *
	 * @return return_description
	 */
	static public function getLatLon($uid = false, $exclude = false)
	{
		$mainframe = JFactory::getApplication();
		$db = JFactory::getDBO();
		$query = "SELECT u.id,u.name,u.username,u2.jtglat,u2.jtglon,u2.jtgvisible FROM #__users as u left join #__jtg_users as u2 ON u.id=u2.user_id";

		if ($uid !== false)
		{
			$query .= " WHERE u.id='" . $uid . "'";
		}
		elseif ($exclude !== false)
		{
			$query .= " WHERE u.id<>'" . $exclude . "'";
		}

		$db->setQuery($query);
		$object = $db->loadObjectList();

		return $object;
	}

	/**
	 * function_description
	 *
	 * @param   string  $distance  param_description
	 *
	 * @return string
	 */
	static public function getMiles($distance)
	{
		$miles = round($distance * 0.621, 2);

		return $miles;
	}

	/**
	 * Rotate image based on EXIF data; some browswers do not
         * correctly display unrotated figures
	 *
         * @param Imagick object $image input image
         */

static public function autoRotateImage($image) { 
    $orientation = $image->getImageOrientation(); 

    switch($orientation) { 
        case imagick::ORIENTATION_BOTTOMRIGHT: 
            $image->rotateimage("#000", 180); // rotate 180 degrees 
        break; 

        case imagick::ORIENTATION_RIGHTTOP: 
            $image->rotateimage("#000", 90); // rotate 90 degrees CW 
        break; 

        case imagick::ORIENTATION_LEFTBOTTOM: 
            $image->rotateimage("#000", -90); // rotate 90 degrees CCW 
        break; 
    } 

    // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image! 
    $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT); 
} 
 
        /**
         * function_description
         *
         * @param   unknown_type  $coordPart  param_description
         *
         * @return return_description
         */
        static public function gps2Num($coordPart)
        {
                $parts = explode('/', $coordPart);

                if ((count($parts)) <= 0)
                {
                        return 0;
                }

                if ((count($parts)) == 1)
                {
                        return $parts[0];
                }

                return floatval($parts[0]) / floatval($parts[1]);
        }
	
	 /**
         * Pass in GPS.GPSLatitude or GPS.GPSLongitude or something in that format
         *
         * http://stackoverflow.com/questions/2526304/php-extract-gps-exif-data/2572991#2572991
         * Thanks to Gerald Kaszuba http://geraldkaszuba.com/
         *
         * @param   unknown_type  $exifCoord  param_description
         * @param   unknown_type  $hemi       param_description
         *
         * @return number
         */
        static public function getGpsFromExif($exifCoord, $hemi)
        {
                $degrees = count($exifCoord) > 0 ? jtgHelper::gps2Num($exifCoord[0]) : 0;
                $minutes = count($exifCoord) > 1 ? jtgHelper::gps2Num($exifCoord[1]) : 0;
		$seconds = count($exifCoord) > 2 ? jtgHelper::gps2Num($exifCoord[2]) : 0;
                $flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

                return $flip * ($degrees + $minutes / 60 + $seconds / 3600);
        }

	/**
	 * resize if needed and convert to jpg using gd
	 *
	 * @param   string  $file_tmp_name  param_description
	 * @param   string  $ext            param_description
	 * @param   string  $image_dir      param_description
	 * @param   string  $image          param_description
	 *
	 * @return return_description
	 */
	static public function resizeConvertGd($file_tmp_name, $ext, $image_dir, $outfname)
	{
		jimport('joomla.filesystem.file');

		switch (strtolower($ext))
		{
			case 'jpeg':
			case 'pjpeg':
			case 'jpg':
				$src = ImageCreateFromJpeg($file_tmp_name);
				break;

			case 'png':
				$src = ImageCreateFromPng($file_tmp_name);
				break;

			case 'gif':
				$src = ImageCreateFromGif($file_tmp_name);
				break;
		}

		list($width, $height) = getimagesize($file_tmp_name);
		$cfg = self::getConfig();

		// Pixsize in pixel
		$maxsize = (int) $cfg->max_size;
		$resized = false;

		if ( ( $height > $maxsize ) OR ( $width > $maxsize ) )
		{
			if ( $height == $width )
			{
				// Square
				$newheight = $maxsize;
				$newwidth = $maxsize;
			}
			elseif ( $height < $width )
			{
				// Landscape
				$newheight = $maxsize / $width * $height;
				$newwidth = $maxsize;
			}
			else
			{
				// Portrait
				$newheight = $maxsize;
				$newwidth = $width / $height * $newheight;
			}

			$resized = true;
			$newwidth = (int) $newwidth;
			$newheight = (int) $newheight;
		}
		else
		{
			$newwidth = (int) $width;
			$newheight = (int) $height;
		}

		$tmp = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

		$outfullname = $image_dir .'/'. $outfname;
		switch (strtolower($ext))
		{
			case 'jpeg':
			case 'pjpeg':
			case 'jpg':
				if ($resized)
				{
					// Upload the image and convert
					$statusupload = imagejpeg($tmp, $outfullname, 100);
				}
				else
				{
					// Copy the image and convert NOT (for exif-data)
					$statusupload = JFile::copy($file_tmp_name, $outfullname);
				}
				break;

			case 'png':
			case 'gif':
				// Upload the image
				$statusupload = imagejpeg($tmp, $outfullname, 100);
				break;
		}

		imagedestroy($tmp);

		return $statusupload;
	}

	/**
	 * resize if needed and convert to jpg using ImageMagick
	 *
	 * @param   string  $file_tmp_name  param_description
	 * @param   string  $ext            param_description
	 * @param   string  $image_dir      param_description
	 * @param   string  $image          param_description
	 *
	 * @return return_description
	 */
	static public function resizeConvertImk($file_tmp_name, $image_dir, $outfname) {
		$image = new Imagick($file_tmp_name);
		jtgHelper::autoRotateImage($image);
		$height = $image->getImageHeight();
		$width = $image->getImageWidth();
		$cfg = self::getConfig();

		// Pixsize in pixel
		$maxsize = (int) $cfg->max_size;
		$resized = false;

		if ( ( $height > $maxsize ) OR ( $width > $maxsize ) )
		{
			if ( $height == $width )
			{
				// Square
				$newheight = $maxsize;
				$newwidth = $maxsize;
			}
			elseif ( $height < $width )
			{
				// Landscape
				$newheight = $maxsize / $width * $height;
				$newwidth = $maxsize;
			}
			else
			{
				// Portrait
				$newheight = $maxsize;
				$newwidth = $width / $height * $newheight;
			}

			$resized = true;
			$newwidth = (int) $newwidth;
			$newheight = (int) $newheight;
			$image->resizeImage($newwidth, $newheight, imagick::FILTER_LANCZOS, 1);
		}
		else
		{
			$newwidth = (int) $width;
			$newheight = (int) $height;
		}
		$status = $image->writeImage($image_dir.'/'.$outfname);
		$image->destroy();
		return $status;
	}

	/**
	 * creates the images; resize if original is larger than maxsize
	 *
	 * @param   string  $file_tmp_name  param_description
	 * @param   string  $ext            param_description
	 * @param   string  $image_dir      param_description
	 * @param   string  $image          param_description
	 *
	 * @return return_description
	 */
	static public function createimageandthumbs($trackID, $file_tmp_name, $ext, $outfname)
	{

		require_once JPATH_SITE . '/administrator/components/com_jtg/models/thumb_creation.php';
		jimport('joomla.filesystem.file');
		$cfg = self::getConfig();

		$image_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $trackID;
		if (! JFolder::exists($image_dir))
		{  	
			JFolder::create($image_dir, 0777);
		}

		$outfname = str_replace('.' . $ext, '.jpg', $outfname);		
		if (phpversion('imagick')) {
		   $statusupload = jtgHelper::resizeConvertImk($file_tmp_name, $image_dir, $outfname);
		}
		else if (phpversion('gd')) {
		   $statusupload = jtgHelper::resizeConvertGd($file_tmp_name, $ext, $image_dir, $outfname);
		}
		else JFactory::getApplication()->enqueueMessage('ERROR: need ImageMagick or gd extension to handle images','warning');

		if ($statusupload)
		{

		//
		//  Add entry to image database
		//
		$query = "INSERT INTO #__jtg_photos SET" . "\n trackID='" . $trackID ."',\n".
			"filename='".$outfname."'";

		$exif = exif_read_data($file_tmp_name);
		if ( isset($exif['GPSLatitude']))
		{
			$lon = jtgHelper::getGpsFromExif($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
			$lat = jtgHelper::getGpsFromExif($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
			$query .= ",\n lon='".$lon."',\n lat='".$lat."'";
		}
		
		$db = JFactory::getDBO();
		$db->setQuery($query);    
		if (! $db->execute())
		{
			echo $db->stderr();
			$statusdb = false;
		}
		else
		{
			$statusdb = true;
		}

		$statusthumbs = Com_Jtg_Create_thumbnails(
					$image_dir, $outfname,
					$cfg->max_thumb_height, $cfg->max_geoim_height);
		}

		if ($statusupload and $statusdb and $statusthumbs)
		{
			return true;
		}

		return false;
	}

	/**
	 * function_description
	 *
	 * @param   string  $uid       param_description
	 * @param   string  $username  param_description
	 *
	 * @return string
	 */
	static public function getProfileLink($uid, $username)
	{
		$cfg = self::getConfig();

		switch ($cfg->profile)
		{
			case "cb":
				$link = "<a href=" . JRoute::_('index.php?option=com_comprofiler&task=userProfile&user=' . $uid) . " >" . $username . "</a>";

				return $link;
				break;

			case "js":
				$jspath = JPATH_BASE . '/components/com_community';
				include_once $jspath . '/libraries/core.php';
				$link = "<a href=" . CRoute::_('index.php?option=com_community&view=profile&userid=' . $uid) . " >" . $username . "</a>";

				return $link;
				break;

			case "ku":
				$link = "<a href=" . JRoute::_('index.php?option=com_kunena&func=fbprofile&userid=' . $uid) . " >" . $username . "</a>";

				return $link;
				break;

			case "0":
				$link = $username;

				return $link;
				break;
		}
	}

	/**
	 * function_description
	 *
	 * @param   string   $where       input where statement
	 * @param   string   $access      File access level
	 * @param   integer  $otherfiles  0 for non registered, 1 for registered,
	 * 		2 for special users 9 for author (defined in backend)
	 *
	 * @return sql where statement according to access restriction
	 */
	static public function MayIsee($where, $access, $otherfiles)
	{
		$otherfiles = (int) $otherfiles;

		if ( $where != "" )
		{
			$where = $where . " AND ";
		}

		switch ($otherfiles)
		{
			case 0: // No restriction
				return $where . "a.access <= " . $access;
				break;

			case 1: // Registered users
				if ( ( $access == 0 ) OR ( $access == 1 ) )
				{
					return $where . "( a.access = 0 OR a.access = 1 )";
				}
				else
				{
					return $where;
				}
				break;

			case 2: // Special, administrators
				return $where;
				break;
		}
	}

	/**
	 * function_description
	 *
	 * @param   integer  $level       track difficulty level
	 * @param   integer  $catid       track category id
	 * @param   integer  $levelMin    minimum permitted value for level
	 * @param   integer  $levelMax    maximum permitted value for level
	 * @param   integer  $iconheight  height of icons
	 *
	 * @return HTML string with level icon or level text
	 */
	static public function getLevelIcon($level, $catid = 0, $levelMin=1, $levelMax=5, $iconheight = 24)
	{
		$iconspath = JPATH_BASE . '/images/jtrackgallery/difficulty_level/';
		$iconsurl = JUri::root() . 'images/jtrackgallery/difficulty_level/';
		$levelString = $level . '/' . $levelMax;
		$levels = ($levelMax > $levelMin);
		$height = ($iconheight > 0? ' style="max-height:' . $iconheight . 'px;display:inline;" ' : ' style="display:none;" ');

		if ($levels AND JFile::exists($iconspath . $catid . '_' . (string) $level . '.png'))
		{
			// Use $catid_$level.png
			return '<img ' . $height . ' src="' . $iconsurl . $catid . '_' . (string) $level . '.png" alt="' . $levelString . '" title="' . $levelString . '">';
		}
		elseif ( $levels
				AND (JFile::exists($iconspath . $catid . '_l1.png'))
				AND (JFile::exists($iconspath . $catid . '_l2.png'))
				AND (JFile::exists($iconspath . $catid . '_l3.png')) )
		{
			// Use $catid_l1.png $catid_l2.png $catid_l3.png
			$return = '';

			for ($i = $levelMin; $i <= $level; $i++)
			{
				$j = 1 + (int) (($i - $levelMin) / ($levelMax - $levelMin) * 3);
				$j = max(1, $j);
				$j = min(3, $j);
				$return .= '<img ' . $height . ' src="' . $iconsurl . $catid . '_l' . $j . '.png" alt="' . $levelString . '" title="' . $levelString . '">';
			}

			return $return;
		}
		elseif ($levels AND JFile::exists($iconspath . (string) $level . '.png'))
		{
			// Use $level.png
			return '<img ' . $height . ' src="' . $iconsurl . (string) $level . '.png" alt="' . $levelString . '" title="' . $levelString . '">';
		}
		elseif ( $levels
				AND (JFile::exists($iconspath . 'l1.png'))
				AND (JFile::exists($iconspath . 'l2.png'))
				AND (JFile::exists($iconspath . 'l3.png')) )
		{
			// Use l1.png l2.png l3.png
			$return = '';

			for ($i = $levelMin; $i <= $level; $i++)
			{
				$j = 1 + round(($i - $levelMin) / ($levelMax - $levelMin) * 2);
				$return .= '<img ' . $height . ' src="' . $iconsurl . 'l' . $j . '.png" alt="' . $levelString . '" title="' . $levelString . '">';
			}

			return $return;
		}
		else
		{
			return $levelString;
		}
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $float    param_description
	 * @param   unknown_type  $default  param_description
	 * @param   unknown_type  $unit     param_description
	 *
	 * @return return_description
	 */
	static public function getLocatedFloat($float, $default = 0, $unit = null)
	{
		if ( $float == 0 )
		{
			return $default;
		}

		if ( strtolower($unit) == "kilometer" )
		{
			$unit = JText::_('COM_JTG_KILOMETER');
		}

		if ( strtolower($unit) == "miles" )
		{
			$float = self::getMiles($float);
			$unit = JText::_('COM_JTG_MILES');
		}

		$float = (float) $float;

		if ( ( $unit !== null ) AND ( $unit == JText::_('COM_JTG_KILOMETER') ) AND ( $float < 1 ) )
		{
			$float = $float * 1000;
			$unit = "&nbsp;" . JText::_('COM_JTG_METERS');
		}

		if ( $unit !== null )
		{
			$unit = "&nbsp;" . $unit;
		}

		if ( preg_match('/\./', $float) )
		{
			// Has decimal place
			$digit = explode('.', $float);

			// Count of digits after decimal place
			$digits = strlen($digit[1]);
		}
		else
		{
			$digits = 0;
		}

		return number_format(
				$float,
				$digits,
				JText::_('COM_JTG_SEPARATOR_DEC'),
				JText::_('COM_JTG_SEPARATOR_THS')
		) . $unit;
	}
}
