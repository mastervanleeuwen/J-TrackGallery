<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @author      Christian Knorr, InJooOSM  <christianknorr@users.sourceforge.net>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        https://mastervanleeuwen.github.io/J-TrackGallery/
 *
 */
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
use Joomla\CMS\Factory;
use Joomla\CMS\Editor\Editor;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * JtgModelTrack class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */


class JtgModelTrack extends FormModel
{

	public function getForm($data = array(), $loadData = true)
	{
		return $this->loadForm('com_jtg.track','track',array('load_data' => $loadData)); 
	}

	public function getTable($name = 'jtg_files', $prefix = 'Table', $options = [])
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $selected  param_description
	 *
	 * @return return_description
	 */
	function getLevelSelect ($selected)
	{
		$return = "<select name=\"level\" class=\"form-select\">\n";
		$cfg = JtgHelper::getConfig();
		$levels = explode("\n", $cfg->level);
		array_unshift($levels, 'dummy');
		$i = 0;

		foreach ($levels as $level)
		{
			if (trim($level) != "")
			{
				$return .= ("					<option value=\"" . $i . "\"");

				if ($i == $selected)
				{
					$return .= (" selected=\"selected\"");
				}

				$return .= (">");

				if ($i == 0)
				{
					$return .= JText::_('COM_JTG_SELECT');
				}
				else
				{
					$return .= $i . " - " . JText::_(trim($level));
				}

				$return .= ("</option>\n");
				$i ++;
			}
		}

		return $return . "				</select>\n";
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $selected  param_description
	 *
	 * @return array
	 */
	function getLevel ($selected)
	{
		$return = "\n";
		$cfg = JtgHelper::getConfig();
		$levels = explode("\n", $cfg->level);
		array_unshift($levels, 'dummy');
		$i = 0;

		foreach ($levels as $level)
		{
			if (trim($level) != "")
			{
				if ($i == $selected)
				{
					$selectedlevel = $i;
					$selectedtext = $level;
				}

				$i ++;
			}
		}

		$return .= $selectedlevel . "/" . ($i - 1) . " - " . JText::_(trim($selectedtext));

		return $return;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function getCats ()
	{
		$mainframe = JFactory::getApplication();
		$db = $this->getDbo();

		$query = "SELECT * FROM #__jtg_cats WHERE published=1 ORDER BY ordering ASC";

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$limit = count($rows);
		$children = array();

		foreach ($rows as $v)
		{
			$v->title = JText::_($v->title);
			$pt = $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}

		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, $maxlevel = 9999, $level = 0, $type = 0);
		$list = array_slice($list, 0, $limit);
		$cats = array();
		$nullcat = array(
				'id' => 0,
				'title' => JText::_('JNONE'),
				'name' => JText::_('JNONE'),
				'image' => ""
		);
		$cats[0] = $nullcat;

		foreach ($list as $cat)
		{
			if ($cat->treename == $cat->title)
			{
				$title = $cat->title;
			}
			else
			{
				$title = $cat->treename;
			}

			$arr = array(
					'id' => $cat->id,
					'title' => $title,
					'name' => JText::_($cat->title),
					'image' => $cat->image
			);
			$cats[$cat->id] = $arr;
		}

		return $cats;
	}

	/**
	 * function_description
	 *
	 * @return integer id of new file
	 */
	function saveFile ()
	{
		$app = Factory::getApplication();
		$user = Factory::getUser();

		if (!$user->authorise('core.create', 'com_jtg')) {
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jtg&view=jtg',false), false);
			return false;
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$params = JComponentHelper::getParams('com_jtg');

		$db = $this->getDbo();

		$data['uid'] = $user->id;

		// Get the post data
		$input = JFactory::getApplication()->input;
		$catid = $input->get('catid', null, 'array');
		if ($catid) {
			$data['catid'] = $catid ? implode(',', $catid) : '';
		}
		else {
			$data['catid'] = $params->get('jtg_param_default_cat');
		}
		$data['level'] = $input->get('level', 0, 'integer');
		$data['title'] = $input->get('title', '', 'string');
		$terrain = $input->get('terrain', null, 'array');
		$data['terrain'] = $terrain ? implode(',', $terrain) : '';
		$data['description'] = $input->get('description', '', 'raw');
		$file = $input->files->get('file');
		$data['date'] = date("Y-m-d");
		$images = $input->files->get('images');
		$data['access'] = $input->getInt('access', 0);
		$data['hidden'] = $input->getInt('hidden', 0);
		$data['published'] = $input->getInt('published', 0);

		// Upload the file
		$upload_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';
		$filename = strtolower(JFile::makeSafe($file['name']));
		if (strlen($filename) > 127)
		{
			$name = pathinfo($filename, PATHINFO_FILENAME);
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$name = substr($name,0,126-strlen($ext));
			$filename = $name.'.'.$ext;
		}

		$newfile = $upload_dir . $filename;

		if (JFile::exists($newfile))
		{
			$alert_text = JText::sprintf("COM_JTG_FILE_ALREADY_EXISTS", $filename);
			$app->enqueueMessage($alert_text,'warning');
			return false;
		}

		if (! JFile::upload($file['tmp_name'], $newfile))
		{
			$app->enqueueMessage('COM_JTG_UPLOAD_FAILS','error');
			return false;
		}
		chmod($newfile, 0777);

		// Get the start coordinates..

		// Default unit
		$gpsData = new GpsDataClass($newfile, $filename);
		if (strlen($data['title'])==0) {
			$data['title'] = trim($gpsData->trackname);
		}
		if (strlen($data['title'])==0) {
			$data['title'] = JFile::stripExt($file['name']);
		}
		$errors = $gpsData->displayErrors();

		if ($errors)
		{
			$map = "";
			$coords = "";
			$distance_float = 0;
			$data['distance'] = 0;

			// Try to delete the file
			if (JFile::exists($upload_dir . $filename))
			{
				JFile::delete($upload_dir . $filename);
			}

			$app->enqueueMessage(JText::_('COM_JTG_NO_SUPPORT') .' '. $errors);
			return false;
		}

		$data['file'] = $filename;
		$iconCoords = $gpsData->getIconCoords($params['jtg_param_icon_loc']);
		$data['icon_n'] = $iconCoords[1];
		$data['icon_e'] = $iconCoords[0];
		$data['start_n'] = $gpsData->start[1];
		$data['start_e'] = $gpsData->start[0];
		$coords = $gpsData->allCoords;
		$data['istrack'] = (int) $gpsData->isTrack;
		$data['iswp'] = (int) $gpsData->isWaypoint;
		$data['isroute'] = (int) $gpsData->isRoute;
		$data['iscache'] = (int) $gpsData->isCache;
		$data['distance'] = $gpsData->distance;
		$cfg = JtgHelper::getConfig();
		if (strtoupper($cfg->unit)=='MILES') $data['distance'] /= 0.621;
		$data['ele_asc'] = round($gpsData->totalAscent, 0);
		$data['ele_desc'] = round($gpsData->totalDescent, 0) ;

		$trackTable = $this->getTable('jtg_files', 'Table');
		$trackTable->bind($data);
		$trackTable->newTags = $input->get('tags');
		if (!$trackTable->store()) {
			$app->enqueueMessage("Error storing new file ".$trackTable->getError(),'Error');
		}

		$query = "SELECT id FROM #__jtg_files WHERE file='" . $filename . "'";

		$db->setQuery($query);
		$rows = $db->loadObject();

		// Images upload part
		$cfg = JtgHelper::getConfig();
		$types = explode(',', $cfg->type);

		if (!is_null($images) && count($images) > 0)
		{
			foreach ($images as $image)
			{
				if ($image['name'] != "")
				{
					$imgfilename = JFile::makesafe($image['name']);
					$ext = JFile::getExt($imgfilename);

					if (in_array(strtolower($ext), $types))
					{
						JtgHelper::createimageandthumbs($rows->id,$image['tmp_name'], $ext, $imgfilename);
					}
				}
			}
		}

		// Notify configured user by e-mail
		$params = $app->getParams();
		if ((int) $params->get('upload_notify_uid'))
		{
			$mailer = JFactory::getMailer();
			$user = JUser::getInstance((int)  $params->get('upload_notify_uid'));
			$recipient = $user->email;
			if (strlen($recipient))
			{
				$mailer->addRecipient($recipient);
				$link = JRoute::_(JUri::base() . "index.php?option=com_jtg&view=track&layout=default&id=" . $rows->id);
				$msg = JText::_('COM_JTG_NEW_TRACK_MAIL_MSG');
				$config = JFactory::getConfig();
         	$sitename = $config->get('sitename');
				$body = sprintf($msg, $sitename, $link);
				$mailer->setSubject(JText::_('COM_JTG_NEW_TRACK_MAIL_SUBJECT'));
				$mailer->setBody($body);
				$mailer->isHtml(true);
				$senderr = $mailer->Send();
				if ( ! $senderr )
				{
					$app->enqueueMessage('Error sending notification email: ' . $senderr->__toString());
				}
			}
		}
		return $rows->id;
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function hit ()
	{
		$mainframe = JFactory::getApplication();

		$id = $mainframe->input->getInt('id');

		if ($id)
		{
			$tracks = $this->getTable('jtg_files', 'Table');
			$tracks->hit($id);

			return true;
		}

		return false;
	}

	/**
	 * function_description
	 *
	 * @param   integer  $id  file id
	 *
	 * @return return_description
	 */
	function getFile ($id)
	{
		$db = $this->getDbo();

		$query = "SELECT a.*, b.title AS cat, b.image AS image, c.name AS user" . "\n FROM #__jtg_files AS a" .
				"\n LEFT JOIN #__jtg_cats AS b ON a.catid=b.id" . "\n LEFT JOIN #__users AS c ON a.uid=c.id" . "\n WHERE a.id=" . $id;

		$db->setQuery($query);
		$result = $db->loadObject();
		$this->track = $result; // Cache track for use with edit/delete etc

		return $result;
	}

	// TODO: rename getFile above to getItem
	function getItem($id = NULL) {
		return $this->getFile($id);
	}

	/**
	 * function_description
	 *
	 * @param   integer  $id  track id
	 *
	 * @return return_description
	 */
	function getVotes ($id)
	{
		$class = array(
				'nostar',
				'onestar',
				'twostar',
				'threestar',
				'fourstar',
				'fivestar',
				'sixstar',
				'sevenstar',
				'eightstar',
				'ninestar',
				'tenstar'
		);

		$db = $this->getDbo();

		// Count votings
		$query = "SELECT COUNT(*) FROM #__jtg_votes" . "\n WHERE trackid='" . $id . "'";

		$db->setQuery($query);
		$count = (int) $db->loadResult();

		// Sum rating
		$query = "SELECT SUM(rating) FROM #__jtg_votes" . "\n WHERE trackid='" . $id . "'";
		$db->setQuery($query);
		$givenvotes = (int) $db->loadResult();

		// Fetch rating
		$rate = null;

		if ($count != 0)
		{
			while ($rate === null)
			{
				$query = "SELECT vote FROM #__jtg_files" . "\n WHERE id='" . $id . "'";

				$db->setQuery($query);
				$rate = $db->loadResult();

				if ($rate === null || $rate < 0 || $rate > 10)
				{
					$newvote = (float) (round(($givenvotes / $count), 3));
					if ($newvote < 0 || $newvote > 10) {
						error_log("com_jtg: rating for track $id out of range ( $newvote )");
						$newvote = 0;
					}
					$query = "UPDATE #__jtg_files SET" . " vote='" . $newvote . "'" . " WHERE id='" . $id . "'";
					$db->setQuery($query);

					if (! $db->execute())
					{
						echo $db->stderr();

						return false;
					}
				}
			}
		}
		else
		{
			// Save voting: 0
			$query = "UPDATE #__jtg_files SET" . " vote='0'" . " WHERE id='" . $id . "'";
			$db->setQuery($query);

			if (! $db->execute())
			{
				echo $db->stderr();

				return false;
			}
		}

		$object = array(
				"count" => $count,
				"rate" => (float) $rate,
				"sum" => (int) $givenvotes,
				"class" => $class[(int) round($rate, 0)]
		);

		return $object;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $id    param_description
	 * @param   unknown_type  $rate  param_description
	 *
	 * @return return_description
	 */
	function vote ($id, $rate)
	{
		if ($id && $rate >= 0 && $rate <= 10)
		{
			$givevotes = $this->getVotes($id);

			$db = $this->getDbo();

			$query = "INSERT INTO #__jtg_votes SET" . "\n trackid='" . $id . "'," . "\n rating='" . $rate . "'";
			$db->setQuery($query);

			if (! $db->execute())
			{
				echo $db->stderr();

				return false;
			}

			// Count
			$count = (int) $givevotes['count'];
			$sum = (int) $givevotes['sum'];

			$newvote = (float) (round((($sum + $rate) / ($count + 1)), 3));

			$query = "UPDATE #__jtg_files SET" . " vote='" . $newvote . "'" . " WHERE id='" . $id . "'";
			$db->setQuery($query);

			if (! $db->execute())
			{
				echo $db->stderr();

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $id  param_description
	 *
	 * @return return_description
	 */
	function deleteFile ($id)
	{
		$app = Factory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		$user = Factory::getUser();
      if ( !$user->authorise('core.delete', 'com_jtg') ) 
		{
			if (!isset($this->track) || $this->track->id != $id) $this->getFile($id);
			if (!($user->authorise('core.edit.own') && $this->track->uid == $user->id) &&
				!($app->getUserState('com_jtg.newfileid') == $id))
     		{
        		$app->redirect(JRoute::_('index.php?option=com_jtg&view=files&layout=user', false),
				JText::_('COM_JTG_ALERT_NOT_AUTHORISED'), 'Error');
        		return false;
			}
      }

		$db = $this->getDbo();
		$query = "SELECT * FROM #__jtg_files WHERE id='" . $id . "'";
		$this->_db->setQuery($query);
		$file = $this->_db->loadObject();

		// Folder and Pictures within delete
		$folder = JPATH_SITE . "/images/jtrackgallery/uploaded_tracks_images/" . 'track_' . $id;

		if (JFolder::exists($folder))
		{
			JFolder::delete($folder);
		}

		$img_path = JPATH_SITE . 'images/jtrackgallery/uploaded_tracks_images/track_' . $id;
		if (JFolder::exists($img_path))
		{
			JFolder::delete($img_path);
		}

		// File (gpx?) delete
		$filename = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/' . $file->file;

		if (JFile::exists($filename))
		{
			JFile::delete($filename);
		}
		// Delete from DB
		if (!$this->getTable()->delete($id))
		{
			return false;
		}

		$query = "DELETE FROM #__jtg_photos WHERE trackID='" . $id . "'";
		if (!$db->execute())
		{
			return false;
		}

		$query = "DELETE FROM #__jtg_comments WHERE tid='" . $id . "'";
		if (!$db->execute())
		{
			return false;
		}
		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $id  param_description
	 *
	 * @return return_description
	 */
	function getImageFiles ($id)
	{
		$img_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id;

		if (! JFolder::exists($img_dir))
		{
			return null;
		}

		$images = JFolder::files($img_dir);

		return $images;
	}

	/*
	 * get list of images for a track from database
 	 *
    * @param integer  $id Track id
    *
    * @return object
    */
   function getImages($id)
   {
       $db = $this->getDbo();
       $query = "SELECT * FROM #__jtg_photos"
         . "\n WHERE trackID='" . $id . "'";
       $db->setQuery($query);
       $result = $db->loadObjectList();

       return $result;
	}


	/**
	 * function_description
	 *
	 * @param   unknown_type  $id  param_description
	 *
	 * @return return_description
	 */
	function updateFile ($id)
	{
		$app = Factory::getApplication();
		$user = Factory::getUser();
		if (!$user->authorise('core.edit', 'com_jtg') &&
          !($user->authorise('core.create', 'com_jtg') && $app->getUserState('com_jtg.newfileid') == $id))
		{
			if (!isset($this->track) || $this->track->id != $id) $this->getFile($id);
			if (!($user->authorise('core.edit.own', 'com_jtg') && $user->id == $this->track->uid))
	      {
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
      	   $this->setRedirect(JRoute::_('index.php?option=com_jtg&view=jtg',false), false);
				return "Action not permitted";
			}
		}

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$db = $this->getDbo();
		$user = JFactory::getUser();

		$data['id'] = $id;
		// Get the post data
		$input = $app->input; 
		$catid = $input->get('catid', null, 'array');
		$catid = $catid ? implode(',', $catid) :  '';
		$data['catid'] = $catid;
		$data['level'] = $input->get('level', 0, 'integer');
		$data['title'] = $input->get('title', '', 'string');

		$terrain = $input->get('terrain', null, 'array');

		if ($terrain)
		{
			$data['terrain'] = $terrain ? implode(',', $terrain) : '';
		}
		else
		{
			$data['terrain'] = '';
		}

		/* Joomla Jinput strips html tags!!
		 http://stackoverflow.com/questions/19426943/joomlas-jinput-strips-html-with-every-filter
		*/
		$data['description'] = $input->get('description', '', 'raw');

		$data['access'] = $input->getInt('access', 0);
		$data['hidden'] = $input->getInt('hidden', 0);
		$data['published'] = $input->getInt('published', 0);

		$data['default_map'] = $input->getInt('default_map');
		$data['distance'] = $input->getFloat('distance');
		$data['ele_asc'] = $input->getInt('ascent');
		$data['ele_desc'] = $input->getInt('descent');

		$mappreview = $input->get('mappreview','','BASE64');
		if (!empty($mappreview)) {
			$previewdata = base64_decode($mappreview);
			$imgpath = JPATH_SITE . '/images/jtrackgallery/maps/track_' . $id . '.png';
			file_put_contents($imgpath, $previewdata);
		}

		$imagelist = $this->getImages($id);
		$imgpath = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/track_' . $id . '/';
		foreach ($imagelist as $image)
		{
			$delimage = $input->get('deleteimage_' . $image->id);
         if ($delimage !== null)
         {
            JFile::delete($imgpath . $delimage);
            JFile::delete($imgpath . 'thumbs/' . 'thumb0_' . $delimage);
            JFile::delete($imgpath . 'thumbs/' . 'thumb1_' . $delimage);
            JFile::delete($imgpath . 'thumbs/' . 'thumb2_' . $delimage);
            $query = "DELETE FROM #__jtg_photos\n WHERE id='".$image->id."'";
            $db->setQuery($query);
            $db->execute();
         }
  			// Set image title
         $img_title = $input->get('img_title_' . $image->id, '', 'string');
         if ($img_title !== null and $img_title != $image->title) {
             $query = "UPDATE #__jtg_photos SET title=".$db->quote($img_title)." WHERE id='".$image->id."'";
             $db->setQuery($query);
             $db->execute();
			}
			$img_lat = $input->getFloat('img_lat_' . $image->id);
			$img_lon = $input->getFloat('img_long_' . $image->id);
			if (!is_null($img_lat) && !is_null($img_lon)) {
				if (number_format($img_lat,5) != number_format($image->lat,5) ||
					 number_format($img_lon,5) != number_format($image->lon,5)) {
					$query = "UPDATE #__jtg_photos SET lon=".$db->quote(number_format($img_lon,6)).", lat=".$db->quote(number_format($img_lat,6))." WHERE id='".$image->id."'";
					$db->setQuery($query);
					$db->execute();
				}
			}
      }

		// Images upload part
		$newimages = $input->files->get('images');
		$cfg = JtgHelper::getConfig();
		$types = explode(',', $cfg->type);
		if ($newimages)
		{
			foreach ($newimages as $newimage)
			{
				$filename = JFile::makesafe($newimage['name']);
				$ext = JFile::getExt($filename);

				if (in_array(strtolower($ext), $types))
				{
					JtgHelper::createimageandthumbs($id,$newimage['tmp_name'], $ext, $filename);
				}
			}
		}

		$trackTable = $this->getTable('jtg_files', 'Table');
		$trackTable->bind($data);
		$trackTable->newTags = $input->get('tags');
		$trackTable->store();

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   string  $where  where query string
	 *
	 * @return array
	 */
	function getTerrain ($where = null)
	{
		$db = $this->getDbo();

		// $query = "SELECT * FROM #__jtg_terrains ORDER BY ordering,title ASC";
		$query = "SELECT * FROM #__jtg_terrains " . $where . " ORDER BY title ASC";

		$db->setQuery($query);
		$row = $db->loadObjectList();
		$terrain = array();

		if ($row)
		{
			foreach ($row as $v)
			{
				$v->title = JText::_($v->title);
				$terrain[] = $v;
			}
		}

		return $terrain;
	}

	/**
	 * function_description
	 *
	 * @param   integer  $id     param_description
	 * @param   string   $order  param_description
	 *
	 * @return array
	 */
	function getComments ($id, $order)
	{
		$db = $this->getDbo();
		$query = "SELECT * FROM #__jtg_comments WHERE" . "\n tid=" . $id . "\n AND published=1" . "\n ORDER BY date " . $order;
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}

	/**
	 * function_description
	 *
	 * @param   object  $cfg  param_description
	 *
	 * @return return_description
	 */
	function addcomment ($cfg)
	{
		if (version_compare(JVERSION,'4.0','lt'))
		{
			JHtml::_('behavior.formvalidation');
			$editor = JFactory::getConfig()->get('editor');
		}
		else {
			HTMLHelper::_('behavior.formvalidator');
			$editor = Factory::getApplication()->getConfig()->get('editor');
		}
		$editor = Editor::getInstance($editor);
		$user = JFactory::getUser();
		$id = JFactory::getApplication()->input->getInt('id'); // Check whether getComment works here
		?>

		<script type="text/javascript">
		Joomla.myValidate = function(f) {
				if (document.formvalidator.isValid(f)) {
						f.check.value='<?php echo JSession::getFormToken(); ?>';//send token
						return true;
				}
				else {
						alert('<?php echo JText::_('COM_JTG_FILLOUT'); ?>');
				}
				return false;
		}
		</script>

<form class='form-validate' id='adminform' name='adminform'
	action='index.php?option=com_jtg' method='post'
	onSubmit='return myValidate(this);'>
	<table class='comment-form'>
		<thead>
			<tr>
				<th colspan='2'><b><?php echo JText::_('COM_JTG_WRITE_COMMENT'); ?>
				</b></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><label class='form-label' for='name'><?php echo JText::_('COM_JTG_NAME'); ?>*</label>
				</td>
				<td><input type='text' name='name' id='name' size='20'
					value='<?php echo $user->get('username'); ?>' class='required form-text'
					maxlength='50' /></td>
			</tr>
			<tr>
				<td>
					<label class='form-label' for='show-email'><?php echo JText::_('COM_JTG_SHOW_EMAIL'); ?></label>
				</td>
				<td>
					<input type='checkbox' name='show-email' onchange="document.getElementById('email').disabled=!this.checked;">
				</td>
			</tr>
			<tr>
				<td><label class='form-label' for='email'><?php echo JText::_('COM_JTG_EMAIL'); ?></label>
				</td>
				<td>
					<input type='text' name='email' id='email' size='30' disabled 
					value='<?php echo $user->get('email'); ?>'
					class='validate-email form-text' maxlength='50' /></td>
			</tr>
			<tr>
				<td><label class='form-label' for='homepage'><?php echo JText::_('COM_JTG_INFO_AUTHOR_WWW'); ?>
				</label></td>
				<td><input type='text' name='homepage' id='homepage' class='form-text' size='30'
					maxlength='50' /></td>
			</tr>
			<tr>
				<td><label class='form-label' for='title'><?php echo JText::_('COM_JTG_COMMENT_TITLE'); ?>*</label>
				</td>
				<td><input type='text' name='title' id='title' size='40' value=''
					class='required form-text' maxlength='80' /></td>
			</tr>
			<tr>
				<td colspan='2'><label class='form-label' for='text'><?php echo JText::_('COM_JTG_COMMENT_TEXT'); ?>*</label>
					<?php echo $editor->display('text', '', '100%', '200', '80', '8', false, null, null);?>
				</td>
			</tr>
<?php if ($cfg->captcha == 1)
{
?>
			<tr>
				<td><img
					src='<?php echo JRoute::_("index.php?option=com_jtg&task=displayimg", false); ?>'>
				</td>
				<td><input type="text" name="word" value="" size="10"
					class="required" /> <?php echo JText::_('COM_JTG_CAPTCHA_INFO'); ?>
				</td>
			</tr>
<?php
}
?>
			<tr>
				<td colspan='2' align='right'><input type='submit'
					value='<?php echo JText::_('COM_JTG_SEND')?>' name='submit'
					class='btn btn-primary' /></td>
			</tr>
		</tbody>
	</table>
	<?php echo JHtml::_('form.token') . "\n"; ?>
	<input type='hidden' name='controller' value='track' /> <input
		type='hidden' name='task' value='savecomment' /> <input type='hidden'
		name='id' value='<?php echo $id; ?>' />
</form>
<?php
	}

	/**
	 * function_description
	 *
	 * @param   integer  $id   comment id
	 * @param   object   $cfg  jtg config
	 *
	 * @return boolean
	 */
	function savecomment ($id, $cfg)
	{
		$app = JFactory::getApplication();

		$user = Factory::getUser();
		$uid = $user->id;
		if (!$user->authorise('jtg.comment','com_jtg')) {
         $app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			return false;
		}
		$input = $app->input;
		$name = $input->get('name', '', 'string');
		$email = $input->get('email', '', 'Raw');
		$homepage = $input->get('homepage', '', 'raw');
		$title = $input->get('title', '', 'string');
		$text = $input->get('text', '', 'raw');

		if ($text == "")
		{
			return false;
		}

		$db = $this->getDbo();
		$query = "INSERT INTO #__jtg_comments SET" . "\n tid='" . $id . "'," . 
				"\n uid=" . $uid . "," .
				"\n user=" . $db->quote($name) . "," . "\n email=" . $db->quote($email) . "," .
				"\n homepage=" . $db->quote($homepage) . "," . "\n title=" . $db->quote($title) . "," . "\n text=" . $db->quote($text) . "," . "\n published=1";

		$db->setQuery($query);
		if (!$db->execute())  // TODO: change to try -- catch
		{
			echo $db->stderr();
			return false;
		}

		// Send autor email if set
		if ($cfg->inform_autor == 1)
		{
			$mailer = JFactory::getMailer();
			$config = JFactory::getConfig();
			$link = JRoute::_(JUri::base() . "index.php?option=com_jtg&view=track&layout=default&id=" . $id);
			$msg = JText::_('COM_JTG_CMAIL_MSG');
         $sitename = $config->get('sitename');
			$body = sprintf($msg, $sitename, $link);
			$mailer->setSubject(JText::_('COM_JTG_CMAIL_SUBJECT'));
			$mailer->setBody($body);
			$mailer->isHtml(true);

			// Optional file attached
			$attachfile = JPATH_COMPONENT . '/assets/document.pdf';
			if (JFile::exists($attachfile)) $mailer->addAttachment($attachfile);

			$author = $this->getAuthorData($id); 
			$email = $author->email; 
			$mailer->addRecipient($email);
			$send = $mailer->Send();

			if ($send !== true)
			{
				echo 'Error sending email: ' . $send->__toString();
			}
		}

		return true;
	}

	/**
	 * Retrieve name, e-mail address of track author
	 *
	 * @param   int  $id track id
	 *
	 * @return  array with search result
	 */
	function getAuthorData ($id)
	{
		$db = $this->getDbo();
		$query = "SELECT a.uid, b.name, b.email FROM #__jtg_files AS a" . "\n LEFT JOIN #__users AS b ON a.uid=b.id" . "\n WHERE a.id=" . $id;

		$db->setQuery($query);
		$user = $db->loadObject();

		return $user;
	}

	/**
	 * Gives back lat/lon from start (if given) and endpoint to make an
	 * approachlink
	 * Homepage: http://openrouteservice.org/
	 * WIKI: http://wiki.openstreetmap.org/wiki/OpenRouteService
	 *
	 * @param   unknown_type  $to_lat  param_description
	 * @param   unknown_type  $to_lon  param_description
	 * @param   unknown_type  $lang    param_description
	 *
	 * @return array
	 */
	function approachors ($to_lat, $to_lon, $lang)
	{
		$user = JFactory::getUser();
		$latlon = JtgHelper::getLatLon($user->id);
		$link = "http://openrouteservice.org/?";

		if (isset($latlon[0]))
		{
			$middle_lon = ((float) $to_lon + (float) $latlon[0]->jtglon) / 2;
			$middle_lat = ((float) $to_lat + (float) $latlon[0]->jtglat) / 2;
			$link .= "start=" . $latlon[0]->jtglon . "," . $latlon[0]->jtglat . "&amp;end=" . $to_lon . "," . $to_lat . "&amp;lat=" . $middle_lat .
			"&amp;lon=" . $middle_lon;
		}
		else
		{
			$link .= "end=" . $to_lon . "," . $to_lat;
		}

		return $link . "&amp;lang=" . $lang . "&amp;pref=";
	}

	/**
	 * Gives back lat/lon from start (if given) and endpoint to make an
	 * approachlink
	 * Homepage: http://maps.cloudmade.com/
	 * WIKI: http://wiki.openstreetmap.org/wiki/CloudMade
	 *
	 * @param   string  $to_lat  param_description
	 * @param   string  $to_lon  param_description
	 * @param   string  $lang    param_description
	 *
	 * @return array
	 */
	function approachcm ($to_lat, $to_lon, $lang)
	{
		$link = "http://maps.cloudmade.com/?";
		$user = JFactory::getUser();
		$latlon = JtgHelper::getLatLon($user->id);

		if (isset($latlon[0]))
		{
			if ($latlon[0]->jtglat)
			{
				$from_lat = $latlon[0]->jtglat;
			}

			if ($latlon[0]->jtglon)
			{
				$from_lon = $latlon[0]->jtglon;
			}
		}

		if (isset($from_lon) && isset($from_lat))
		{
			$middle_lon = ((float) $to_lon + (float) $from_lon) / 2;
			$middle_lat = ((float) $to_lat + (float) $from_lat) / 2;
			$link .= "lat=" . $middle_lat . "&amp;";
			$link .= "lng=" . $middle_lon . "&amp;";
			$link .= "directions=" . $from_lat . "," . $from_lon;
			$link .= "," . $to_lat . "," . $to_lon . "&amp;zoom=16";
		}
		else
		{
			$link .= "directions=" . $to_lat . "," . $to_lon . "&amp;";
			$link .= "lat=" . $to_lat . "&amp;";
			$link .= "lng=" . $to_lon . "&amp;";
			$link .= "zoom=15";
		}

		return $link . "&amp;styleId=1&amp;opened_tab=1&amp;travel=";
	}

	/**
	 * Gives back lat/lon from start (if given) and endpoint to make an
	 * approachlink
	 * Homepage: http://maps.cloudmade.com/
	 * WIKI: http://wiki.openstreetmap.org/wiki/CloudMade
	 *
	 * @param   string  $to_lat  latitude
	 * @param   string  $to_lon  longitudex
	 * @param   string  $lang    user language tag
	 *
	 * @return array
	 */
	function approachcmkey ($to_lat, $to_lon, $lang)
	{
		$key = "651006379c18424d8b5104ed4b7dc210";
		$link = "http://navigation.cloudmade.com/" . $key . "/api/0.3/";
		$user = JFactory::getUser();
		$latlon = JtgHelper::getLatLon($user->id);

		if (isset($latlon[0]))
		{
			if ($latlon[0]->jtglat)
			{
				$from_lat = $latlon[0]->jtglat;
			}

			if ($latlon[0]->jtglon)
			{
				$from_lon = $latlon[0]->jtglon;
			}
		}

		if (isset($from_lon) && isset($from_lat))
		{
			$middle_lon = ((float) $to_lon + (float) $from_lon) / 2;
			$middle_lat = ((float) $to_lat + (float) $from_lat) / 2;
			$link .= "directions=" . $from_lat . "," . $from_lon . "," . $to_lat . "," . $to_lon . "&amp;" . "lat=" . $middle_lat . "&amp;" . "lng=" .
					$middle_lon;
		}
		else
		{
			$link .= "directions=" . $to_lat . "," . $to_lon . "lat=" . $to_lat . "&amp;" . "lng=" . $to_lon . "&amp;zoom=15";
		}

		return $link . "&amp;zoom=15&amp;travel=";
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $www  param_description
	 *
	 * @return return_description
	 */
	function parseHomepageIcon ($www)
	{
		if ((! preg_match('/http\:\/\//', $www)) and (! preg_match('/https\:\/\//', $www)))
		{
			$www = "http://" . $www;
		}

		$cfg = JtgHelper::getConfig();
		$return = "<a target=\"_blank\" href=\"" . $www . "\"><img src=\"" . JUri::base() . "components/com_jtg/assets/template/" . $cfg->template .
		"/images/weblink.png\" /></a>";

		return $return;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $mail  param_description
	 *
	 * @return return_description
	 */
	function parseEMailIcon ($mail)
	{
		$cfg = JtgHelper::getConfig();
		// TODO: make this work; the cloaking function quotes the link, only works for text-links
		//$link = "<img src=\"" . JUri::base() . "components/com_jtg/assets/template/" . $cfg->template . "/images/emailButton.png\" />";
		//$link = JHtml::image(JUri::base() . "components/com_jtg/assets/template/" . $cfg->template . "/images/emailButton.png","email");
      $link = "email";

		$return = JHtml::_('email.cloak', $mail, true, $link, 0);

		return $return;
	}

	/**
	 * get a list for default maps
	 *
	 * @param   unknown_type  $exclusion  param_description
	 *
	 * @return unknown
	 */
	function getDefaultMaps()
	{
		$db = $this->getDbo();

		$query = "SELECT id,name FROM #__jtg_maps WHERE published=1
				AND NOT (param LIKE \"%isBaseLayer: false%\" OR param LIKE \"%isBaseLayer:false%\")";
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$newresult = array();


		foreach ($result as $k => $v)
		{
			$newresult[$k] = $v;
			$newresult[$k]->name = JText::_($newresult[$k]->name);
		}

		return $newresult;
	}

	/**
	 * get a list for default overlays
	 *
	 * @param   unknown_type  $exclusion  param_description
	 *
	 * @return unknown
	 */
	function getDefaultOverlays()
	{
		$db = $this->getDbo();

		$query = "SELECT id,name FROM #__jtg_maps WHERE published=1
				AND (param LIKE \"%isBaseLayer: false%\" OR param LIKE \"%isBaseLayer:false%\")";
		$db->setQuery($query);
		$result = $db->loadObjectList();
		$newresult = array();


		foreach ($result as $k => $v)
		{
			$newresult[$k] = $v;
			$newresult[$k]->name = JText::_($newresult[$k]->name);
		}

		return $newresult;

	}

	 /**
    * sort categories
    *
    * @param  boolean  $sort sort by id instead of title
    * @param  integer $catid select only this category
    *
    * @return sorted rows
    */
	// TODO: this is now a static function in JtgModelFiles and JtgModelJtg; decide where it goes
   static public function getCatsData($sort=false, $catid=null)
   {
      $db = $this->getDbo();

      $query = "SELECT * FROM #__jtg_cats WHERE published = 1";
      if ( !is_null($catid) )
         $query .= " AND id =".$db->quote($catid);
      $query .= "\n ORDER BY title ASC";

      $db->setQuery($query);
      $rows = $db->loadObjectList();

      if ( $sort === false )
      {
         return $rows;
      }
      else
      {
         $nullcat = array(
               "id"        => 0,
               "parent"    => 0,
               "title"        => "<label title=\"" . JText::_('COM_JTG_CAT_NONE') . "\">-</label>",
               "description"  => null,
               "image"        => null,
               "ordering"     => 0,
               "published"    => 1,
               "checked_out"  => 0
         );
         $nullcat = (object) $nullcat;
   
	      $sortedrow = array();

         foreach ( $rows AS $cat )
         {
            $sortedrow[$cat->id] = $cat;
         }

         $sortedrow[0] = $nullcat;

         return $sortedrow;
      }
   }

}
