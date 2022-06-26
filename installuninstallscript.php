<?php


/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 * @package     Com_Jtg
 * @subpackage  Backend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @author      Christophe Seguinot <christophe@jtrackgallery.net>
 * @author      Pfister Michael, JoomGPStracks <info@mp-development.de>
 * @author      Christian Knorr, InJooOSM  <christianknorr@users.sourceforge.net>
 * @copyright   2015 J!TrackGallery, InJooosm and joomGPStracks teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        http://jtrackgallery.net/
 *
 */

/* This file is based on Joomla script.php and corresponding com_flexicontent install script
Developper !!
Use file /installuninstall.php
Don't use /administrator/componentes/com_jtg//installuninstall.php which is copied by joomla during install
*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Installer class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class Com_JtgInstallerScript
{
	/**
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 *
	 * @param   string  $type    the type of change (install, update or discover_install, not uninstall).
	 * @param   object  $parent  the class calling this method.
	 *
	 * @return return_description
	 */
	public function preflight($type, $parent)
	{
		// Try to increment some limits
		// Execution time 5 minutes
		@set_time_limit(240);

		// Continue execution if client disconnects
		ignore_user_abort(true);

		// Load english language file for 'com_jtg' component then override with current language file
		JFactory::getLanguage()->load('com_jtg', JPATH_ADMINISTRATOR . '/components/com_jtg/language', 'en-GB', true);
		JFactory::getLanguage()->load('com_jtg', JPATH_ADMINISTRATOR . '/components/com_jtg/language', null, true);
		$jversion = new JVersion;

		// Installing component manifest file version
		$this->release = $parent->getManifest()->version;

		// File version of existing manifest file
		$this->release_existing = $this->getParam('version');

		// Manifest file minimum Joomla version
		$this->minimum_joomla_release = $parent->getManifest()->attributes()->version;
		echo '<p> -- ' . JText::sprintf('COM_JTG_PREFLIGHT', $this->release) . '</p>';

		if ($this->release_existing)
		{
			echo '<br /> &nbsp; ' . JText::sprintf('COM_JTG_PREFLIGHT_UPDATING', $this->release_existing, $this->release);
		}
		else
		{
			echo '<br /> &nbsp; ' . JText::sprintf('COM_JTG_PREFLIGHT_INSTALLING', $this->release);
		}

		echo '<br /> &nbsp; ' . JText::sprintf('COM_JTG_PREFLIGHT_MIN_JOOMLA', $this->minimum_joomla_release, $jversion->getShortVersion());

		// Abort if the current Joomla release is older
		if (version_compare($jversion->getShortVersion(), $this->minimum_joomla_release, 'lt'))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JTG_PREFLIGHT_MIN_JOOMLA_ABORT', $this->minimum_joomla_release), 'Warning');

			return false;
		}
		// Abort if the component being installed is not newer than the currently installed version
		if ( $type == 'update' )
		{
			$cache = JFactory::getCache('com_jtg');
			$cache->clean();

			$oldRelease = $this->getParam('version');

			if ( version_compare($this->release, $oldRelease, 'lt') )
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JTG_PREFLIGHT_JTG_WRONG_VERSION', $oldRelease, $this->release), 'Warning');

				return false;

				// TODO this aborts the install process but generates and error in Joomla !!!
			}
		}

		return true;
	}

	/**
	 * install runs after the database scripts are executed.
	 *
	 * If the extension is new, the install method is run.
	 * If install returns false, Joomla will abort the install and undo everything already done.
	 *
	 * @param   object  $parent  the class calling this method.
	 *
	 * @return return_description
	 */
	public function install( $parent )
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$doc = JFactory::getDocument();

		?>
	<br />
	<img
		src="<?php echo JUri::root() . 'components/com_jtg/assets/images/logo_JTG.png'; ?>"
		alt="J!Track Gallery" />
	<br />
	<table class="adminlist" border="1" style="width:100%;">
		<tbody>
			<tr>
				<td><?php JText::_('COM_JTG_INSTALL_LICENCE') ?></td>
			</tr>
			<tr>
				<td><?php JText::_('COM_JTG_CREATE_FOLDERS') ?></td>
			</tr>
		</tbody>
	</table>
	<?php
	$folders_to_create = array (
			"images/jtrackgallery",
			"images/jtrackgallery/cats",
			"images/jtrackgallery/terrain",
			"images/jtrackgallery/language",
			"images/jtrackgallery/maps",
			"images/jtrackgallery/uploaded_tracks",
			"images/jtrackgallery/uploaded_tracks/import"
	);

	$folders_to_chmod = array (
			"images/jtrackgallery/cats",
			"images/jtrackgallery/language",
			"images/jtrackgallery/uploaded_tracks",
			"images/jtrackgallery/uploaded_tracks/import"
	);

	echo '<table><tr><td colspan="3"><b>' . JText::_('COM_JTG_FILES_FOLDERS_TO_CREATE') . '</td></tr>';
	echo '<tr><td>' . JText::_('COM_JTG_FILE') . '/' . JText::_('COM_JTG_FOLDER') . '</td><td>' . JText::_('COM_JTG_NAME') . '</td><td>' . JText::_('COM_JTG_STATE') . '</td></tr>';

	foreach ( $folders_to_create AS $folder )
	{
		if (JFolder::exists(JPATH_SITE . '/' . $folder))
		{
			echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="green">' .
					JText::_('COM_JTG_ALREADY_EXISTS') . '</font></td></tr>';
		}
		elseif (JFolder::create(JPATH_SITE . '/' . $folder))
		{
			echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="green">' .
					JText::_('COM_JTG_CREATED') . '</font></td></tr>';
		}
		else
		{
			echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="red">' .
					JText::_('COM_JTG_NOT_CREATED') . '</font></td></tr>';
		}
	}

	foreach ( $folders_to_chmod AS $folder )
	{
		if ( JPath::canChmod(JPATH_SITE . '/' . $folder) AND (chmod(JPATH_SITE . '/' . $folder, 0777)))
		{
			echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="green">' .
					JText::_('COM_JTG_CHMODDED') . '</font></td></tr>';
		}
		else
		{
			echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="red">' .
					JText::_('COM_JTG_NOT_CHMODDED') . '</font></td></tr>';
		}
	}

	// Copy Cats image
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/images/cats';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/cats';
	$files = JFolder::files($src_folder_to_copy);

	// Copy file by file without erasing existing files
	foreach ($files as $file)
	{
		if (!JFile::exists($dest_folder_to_copy . '/' . $file) )
		{
			JFile::copy($src_folder_to_copy . '/' . $file, $dest_folder_to_copy . '/' . $file);
		}
	}

	// Copy additional.ini language files without erasing existing files
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/language';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/language';
	$folders = JFolder::folders($src_folder_to_copy, false);

	// For each language folder
	foreach ($folders as $folder)
	{
		$src_folder_to_copy2 = $src_folder_to_copy . '/' . $folder;
		$dest_folder_to_copy2 = $dest_folder_to_copy . '/' . $folder;
		$files = JFolder::files($src_folder_to_copy2);
		JFolder::create($dest_folder_to_copy2);

		foreach ($files as $file)
		{
			if (!JFile::exists($dest_folder_to_copy2 . '/' . $file) )
			{
				JFile::copy($src_folder_to_copy2 . '/' . $file, $dest_folder_to_copy2 . '/' . $file);
			}
		}
	}

	// Copy example tracks without erasing existing files
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/uploaded_tracks';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks';
	$files = JFolder::files($src_folder_to_copy);

	// Copy file by file without erasing existing files
	foreach ($files as $file)
	{
		if (!JFile::exists($dest_folder_to_copy . '/' . $file) )
		{
			JFile::copy($src_folder_to_copy . '/' . $file, $dest_folder_to_copy . '/' . $file);
		}
	}

	// Copy example image gallery
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/uploaded_tracks_images';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images';

	$folders = JFolder::folders($src_folder_to_copy, 'track_*', false);

	// For each uploaded_tracks_images folder
	foreach ($folders as $folder)
	{
		JFolder::copy($src_folder_to_copy . '/' . $folder, $dest_folder_to_copy . '/' . $folder, $force = false);
	}

	// Copy difficulty level icons
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/images/difficulty_level';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/difficulty_level';
	JFolder::copy($src_folder_to_copy, $dest_folder_to_copy, $force = false);

	echo '<tr><td colspan="3">' . JText::sprintf('COM_JTG_INSTALLED_VERSION', $this->release) . '</td></tr>';

	// Update tracks user ID to current user id
	$db = JFactory::getDBO();
	$application = JFactory::getApplication();
	$uid = JFactory::getUser()->id;
	$query = $db->getQuery(true);
	$query->update($db->quoteName('#__jtg_files'))->set($db->quoteName('uid').'='.$db->quote($uid))->where('1'); // Todo: check this is done below as well
	$db->setQuery($query);
	$db->execute();

	// You can have the backend jump directly to the newly installed component configuration page

	// $parent->getParent()->setRedirectURL('index.php?option=com_jtg');

	echo '<tr><td colspan="3">';
	echo '<font color="red" size="+1">' . JText::_('COM_JTG_HINTS') . '</font>';
	echo JText::_('COM_JTG_HINTS_DETAILS');
	echo '</td></tr>';
	echo '</table>';


	return true;
	}

	/**
	 * update runs after the database scripts are executed.
	 *
	 * If the extension exists, then the update method is run.
	 * If this returns false, Joomla will abort the update and undo everything already done.
	 *
	 * @param   object  $parent  the class calling this method.
	 *
	 * @return return_description
	 */
	public function update( $parent )
	{
		// This is executed after upgrade.sql
		// Upgrading from $oldRelease to $this->release
		$oldRelease = $this->getParam('version');

		// Bug in 0.9.22 1054 Unknown column 'usepace' in 'field list' SQL=INSERT INTO xxxx_jtg_cats

		$db = JFactory::getDBO();
		$columns = $db->getTableColumns('#__jtg_cats');
		if(!isset($columns['usepace'])){
			// The usepace row does not exits
			$db->setQuery("ALTER TABLE '#__jtg_cats' ADD 'usepace' TINYINT(1) NOT NULL DEFAULT '0' AFTER 'ordering'; ");
			$db->execute();
		}
		// If installed version is equal to then 0.9.21 ==> remove plugin plg_jtrackgallery_maps v0.1
		$plg_folder = JPATH_SITE . '/plugins/content/plg_jtrackgallery_maps/';
		if (JFolder::exists($plg_folder))
		{
			// Remove old plugin version
			JFolder::delete($plg_folder);
			$db = JFactory::getDBO();
			$application = JFactory::getApplication();
			$uid = JFactory::getUser($row->uid);
			$db->setQuery("DELETE FROM '#__extensions' WHERE element ='plg_jtrackgallery_maps'");
			$db->execute();
		}

		/* J!Trackgallery >= 0.9.31: remove jtg/default view
		 *  Funtionality now provided by files/map
		 */

		$folder = JPATH_SITE . '/components/com_jtg/views/files/tmpl';
		JFile::delete($folder.'/map.php'); // Function checks whether file exists
		JFile::delete($folder.'/map.xml');
		JFile::delete($folder.'/file.php'); // Moved to new track view
		JFile::delete($folder.'/file.xml');
		JFile::delete($folder.'/form.php');
		JFile::delete($folder.'/form.xml');
		JFile::delete($folder.'/user.php');
		JFile::delete($folder.'/user.xml');
		JFile::delete(JPATH_SITE.'/components/com_jtg/controllers/files.php');
		// openlayers code moved from assets to media
		JFile::delete(JPATH_SITE.'components/com_jtg/assets/js/ol.js');
		JFile::delete(JPATH_SITE.'components/com_jtg/assets/js/ol.js.map');
		JFile::delete(JPATH_SITE.'components/com_jtg/assets/template/default/ol.css');
		JFile::delete(JPATH_SITE.'components/com_jtg/assets/template/default/ol.css.map');
		/*
		 * Move existing old image gallery
		 * from /images/jtrackgallery/track_xx (version<= 0.9.9)
		 * to /images/jtrackgallery/uploaded_tracks_images/track_xx (version> 0.9.9)
		*/

		$folders = JFolder::folders(JPATH_SITE . '/images/jtrackgallery', '^track*', false);

		// Move entire folder (track_xx) if destination folder don't exist
		foreach ($folders as $folder)
		{
			if (!JFolder::exists(JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/' . $folder))
			{
				JFolder::move(JPATH_SITE . '/images/jtrackgallery/' . $folder, JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/' . $folder);
			}
		}

		/*
		 * If it does not exists:
		 * Create difficulty level icons folder and copy icons in it
		 */
		$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/difficulty_level';

		if (!JFolder::exists($dest_folder_to_copy))
		{
			JFolder::copy(JPATH_SITE . '/components/com_jtg/assets/images/difficulty_level', $dest_folder_to_copy, '', $force = false);
		}

		echo '<p>' . JText::sprintf('COM_JTG_UPDATED', $this->release) . '</p>';

      // Check photo database; import photos if empty

      $query = 'SELECT COUNT(*) FROM #__jtg_photos';
      $db->setQuery($query);
      $db->execute();
      $nphotos = $db->loadResult();
	if ( $nphotos == 0 )
	{
			require_once JPATH_SITE . '/components/com_jtg/helpers/helper.php';

			$statusdb = true;

      	$img_dir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images';

      $query = 'SELECT id FROM #__jtg_files';
      $db->setQuery($query);
      $db->execute();
	$ids = $db->loadColumn();

	foreach ( $ids as $key => $id )
	      {
			//
			//  Add entry to image database
  		    	//
	        	if ( JFolder::exists($img_dir.'/track_'.$id) ) {
					$cur_img_dir = $img_dir.'/track_'.$id;
			  		$filenames = JFolder::files($cur_img_dir);
					foreach ( $filenames as $fname ) 
					{
						// Check extension; change to lowercase .jpg
						$ext = JFile::getExt($fname);
						if ($ext !== "jpg")
						{
							if (in_array(strtolower($ext),array('jpg','jpeg')))
							{
								$newfname = JFile::stripExt($fname).'.jpg';
								JFile::move($fname, $newfname, $cur_img_dir);
								$fname = $newfname;
							}
  		                else $fname = false; // Not a valid image file
						}
						// insert in database
						if ($fname)
						{
			        		$query = "INSERT INTO #__jtg_photos SET" . "\n trackID='" . $id ."',\n".
                         "filename='".$fname."'";

							$exif = exif_read_data($cur_img_dir.'/'.$fname);
			   	   	if ( isset($exif['GPSLatitude']))
			      		{
			         		$lon = jtgHelper::getGpsFromExif($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
			         		$lat = jtgHelper::getGpsFromExif($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
		   		      	$query .= ",\n lon='".$lon."',\n lat='".$lat."'";
  		 	   			}

							$db->setQuery($query);  
							if (! $db->execute())
							{
								echo $db->stderr();
								$statusdb = false;
							}
							$nphotos++;
						}
					}
				}
			}
			if ( $statusdb && $nphotos != 0) {
				echo "Inserted $nphotos photos into database<br>";
			}
		}
		// You can have the backend jump directly to the newly updated component configuration page
		// $parent->getParent()->setRedirectURL('index.php?option=com_jtg');

		// 0.9.32 folder for map images
		JFolder::create(JPATH_SITE . '/images/jtrackgallery/maps');
		return true;
	}

	/**
	 * postflight is run after the extension is registered in the database.
	 *
	 * @param   string  $type    the type of change (install, update or discover_install, not uninstall).
	 * @param   object  $parent  the class calling this method.
	 *
	 * @return return_description
	 */
	public function postflight($type, $parent)
	{
		$db = JFactory::getDBO();
		$application = JFactory::getApplication();

		// Check if com_jtg is installed,
		$query = 'SELECT COUNT(*) FROM #__extensions WHERE name = "com_jtg"';
		$db->setQuery($query);
		$db->execute();
		$componentJtgIsInstalled = $db->loadResult();

		if (( $type == 'install' ) and (! $componentJtgIsInstalled) )
		{
			// This a NON successfull install:
			// TODO truncate all com_jtg tables
			$application->enqueueMessage('SHOULD TRUNCATE ALL TABLES');

			// DROP TABLE '#__jtg_cats', '#__jtg_cats2', '#__jtg_comments', '#__jtg_config', '#__jtg_files', '#__jtg_files2', '#__jtg_maps', '#__jtg_terrains', '#__jtg_users', '#__jtg_votes';
		}

		if (( $type == 'install' ) and ($componentJtgIsInstalled !== null) )
		{
			// This is a successful install (not an upgrade):
			// affect sample tracks to current (admin) user
			$user = JFactory::getUser();
			$uid = $user->id;

			$query = 'UPDATE #__jtg_files SET uid =' . $uid . ' WHERE 1';
			$db->setQuery($query);
			$db->execute();

			// TODO Save default params directly in table
			$query = 'UPDATE #__extensions SET params = ';
			$query .= '\' {
			"jtg_param_display_jtg_credits":"1",
			"jtg_param_newest":"10",
			"jtg_param_mostklicks":"10",
			"jtg_param_best":"0",
			"jtg_param_rand":"0",
			"jtg_param_otherfiles":"0",
			"jtg_param_lh":"1",
			"jtg_param_disable_terrains":"0",
			"jtg_param_disable_map_animated_cursor":"0",
			"jtg_param_vote_show_stars":"0",
			"jtg_param_show_layerswitcher":"1",
			"jtg_param_show_speedchart":"1",
			"jtg_param_elevation_filter_min_ascent":"10",
			"jtg_param_show_heightchart":"1",
			"jtg_param_hide_track_info":"0",
			"jtg_param_show_durationcalc":"1",
			"jtg_param_add_startmarker":"1",
			"jtg_param_show_panzoombar":"1",
			"jtg_param_show_mouselocation":"1",
			"jtg_param_show_scale":"1",
			"jtg_param_allow_mousemove":"1",
			"jtg_param_allow_keymove":"0",
			"jtg_param_offer_download_original":"1",
			"jtg_param_offer_download_gpx":"1",
			"jtg_param_offer_download_kml":"1",
			"jtg_param_offer_download_tcx":"0",
			"jtg_param_tracks":"0",
			"jtg_param_level_from":"1",
			"jtg_param_level_to":"5",
			"jtg_param_vote_from":"0",
			"jtg_param_vote_to":"10",
			"jtg_overview_geoloc":"0",
			"map_zoom":"6",
			"dpcallocs_overview":"0",
			"jtg_param_list_icon_max_height":"24"
		}\'';
			$query .= ' WHERE name = "com_jtg" AND type = "Component"';
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * uninstall runs before any other action is taken (file removal or database processing).
	 *
	 * @param   object  $parent  is the class calling this method
	 *
	 * @return always true
	 */
	public function uninstall( $parent )
	{
		// Set a simple message

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$application = JFactory::getApplication();
		$application->enqueueMessage(JText::_('COM_JTG_THANK_YOU_FOR_USING'));
		echo '<p>' . JText::_('COM_JTG_UNINSTALLING') . '</p>';
		$folders_to_delete = array ('images/jtrackgallery');

		$files_to_delete = array ();

		echo '<table><tr><td colspan="3"><b>' . JText::_('COM_JTG_FILES_FOLDERS_TO_DELETE') . '</td></tr>';
		echo '<tr><td>' . JText::_('COM_JTG_FILE') . '/' . JText::_('COM_JTG_FOLDER') . '</td><td>' . JText::_('COM_JTG_NAME') . '</td><td>' . JText::_('COM_JTG_STATE') . '</td></tr>';

		foreach ( $files_to_delete AS $file )
		{
			if (!JFile::exists($file))
			{
				echo '<tr><td>' . JText::_('COM_JTG_FILE') . '</td><td>' . $file . '</td><td><font color="green">' . JText::_('COM_JTG_NOT_EXISTS') . ' </font>  </td></tr>';
			}
			elseif (JFile::delete($file))
			{
				echo '<tr><td>' . JText::_('COM_JTG_FILE') . '</td><td>' . $file . '</td><td><font color="green">' . JText::_('COM_JTG_DELETED') . ' </font> </td></tr>';
			}
			else
			{
				echo '<tr><td>' . JText::_('COM_JTG_FILE') . '</td><td>' . $file . '</td><td><font color="red">' . JText::_('COM_JTG_ERROR') . '/' . JText::_('COM_JTG_NOT_DELETED') . '</font> </td></tr>';
			}
		}

		foreach ( $folders_to_delete AS $folder )
		{
			if (!JFolder::exists(JPATH_SITE . '/' . $folder))
			{
				echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' . $folder . '</td><td><font color="green">' . JText::_('COM_JTG_NOT_EXISTS') . '</font> </td></tr>';
			}
			elseif (JFolder::delete(JPATH_SITE . '/' . $folder))
			{
				echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' . $folder . '</td><td><font color="green">' . JText::_('COM_JTG_DELETED') . '</font> </td></tr>';
			}
			else
			{
				echo '<tr><td>' . JText::_('COM_JTG_FOLDER') . '</td><td>' . $folder . '</td><font color="red">' . JText::_('COM_JTG_ERROR') . JText::_('COM_JTG_NOT_DELETED') . '</font> </td></tr>';
			}
		}

		echo '</table>';

		return true;
	}

	/**
	 * get a variable from the manifest file (actually, from the manifest cache).
	 *
	 * @param   string  $name  parameter name
	 *
	 * @return parameter value
	 */
	public function getParam( $name )
	{
		$db = JFactory::getDbo();
		$db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_jtg"');
		$manifest = json_decode($db->loadResult(), true);
		if (!is_null($manifest)) return $manifest[ $name ];
		return false;
	}

	/**
	 * function_description
	 *
	 * sets parameter values in the component's row of the extension table
	 *
	 * @param   array  $param_array  parameters array
	 *
	 * @return void
	 */
	public function setParams($param_array)
	{
		if ( count($param_array) > 0 )
		{
			// Read the existing component value(s)
			$db = JFactory::getDbo();
			$db->setQuery('SELECT params FROM #__extensions WHERE name = "com_jtg"');
			$params = json_decode($db->loadResult(), true);

			// Add the new variable(s) to the existing one(s)
			foreach ( $param_array as $name => $value )
			{
				$params[ (string) $name ] = (string) $value;
			}
			// Store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params);
			$db->setQuery('UPDATE #__extensions SET params = ' .
					$db->quote($paramsString) .
					' WHERE name = "com_jtg"' );
			$db->execute();
		}
	}
}
