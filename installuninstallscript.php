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

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;

/**
 * Installer class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class com_jtgInstallerScript extends InstallerScript
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
	var $release = '';
	var $generateAliases = false;

	public function preflight($type, $parent)
	{
		// Try to increment some limits
		// Execution time 5 minutes
		@set_time_limit(240);

		// Continue execution if client disconnects
		ignore_user_abort(true);

		// Load english language file for 'com_jtg' component then override with current language file
		Factory::getLanguage()->load('com_jtg', JPATH_ADMINISTRATOR . '/components/com_jtg/language', 'en-GB', true);
		Factory::getLanguage()->load('com_jtg', JPATH_ADMINISTRATOR . '/components/com_jtg/language', null, true);
		$jversion = new Version;

		// Installing component manifest file version
		$this->release = $parent->getManifest()->version;

		// Manifest file minimum Joomla version
		$minimum_joomla_release = $parent->getManifest()->attributes()->version;
		echo '<p> -- ' . Text::sprintf('COM_JTG_PREFLIGHT', $this->release) . '</p>';

		if ($cur_release = $this->getParam('version'))
		{
			echo '<br /> &nbsp; ' . Text::sprintf('COM_JTG_PREFLIGHT_UPDATING', $this->getParam('version'), $cur_release);
		}
		else
		{
			echo '<br /> &nbsp; ' . Text::sprintf('COM_JTG_PREFLIGHT_INSTALLING', $this->release);
		}

		echo '<br /> &nbsp; ' . Text::sprintf('COM_JTG_PREFLIGHT_MIN_JOOMLA', $minimum_joomla_release, $jversion->getShortVersion());

		// Abort if the current Joomla release is older
		if (version_compare($jversion->getShortVersion(), $minimum_joomla_release, 'lt'))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_JTG_PREFLIGHT_MIN_JOOMLA_ABORT', $minimum_joomla_release), 'Warning');

			return false;
		}
		// Abort if the component being installed is not newer than the currently installed version
		if ( $type == 'update' )
		{
			$cache = Factory::getCache('com_jtg');
			$cache->clean();

			$oldRelease = $this->getParam('version');

			if ( version_compare($this->release, $oldRelease, 'lt') )
			{
				Factory::getApplication()->enqueueMessage(Text::sprintf('COM_JTG_PREFLIGHT_JTG_WRONG_VERSION', $oldRelease, $this->release), 'Warning');

				return false;

				// TODO this aborts the install process but generates and error in Joomla !!!
			}
			$db = Factory::getDbo();
			$columns = $db->getTableColumns('#__jtg_files');
			if ($columns && !isset($columns['alias']))
			{
				$this->generateAliases = true;
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
		$doc = Factory::getDocument();

		?>
	<br />
	<img
		src="<?php echo Uri::root() . 'components/com_jtg/assets/images/logo_JTG.png'; ?>"
		alt="J!Track Gallery" />
	<br />
	<table class="adminlist" border="1" style="width:100%;">
		<tbody>
			<tr>
				<td><?php Text::_('COM_JTG_INSTALL_LICENCE') ?></td>
			</tr>
			<tr>
				<td><?php Text::_('COM_JTG_CREATE_FOLDERS') ?></td>
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

	echo '<table><tr><td colspan="3"><b>' . Text::_('COM_JTG_FILES_FOLDERS_TO_CREATE') . '</td></tr>';
	echo '<tr><td>' . Text::_('COM_JTG_FILE') . '/' . Text::_('COM_JTG_FOLDER') . '</td><td>' . Text::_('COM_JTG_NAME') . '</td><td>' . Text::_('COM_JTG_STATE') . '</td></tr>';

	foreach ( $folders_to_create AS $folder )
	{
		if (Folder::exists(JPATH_SITE . '/' . $folder))
		{
			echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="green">' .
					Text::_('COM_JTG_ALREADY_EXISTS') . '</font></td></tr>';
		}
		elseif (Folder::create(JPATH_SITE . '/' . $folder))
		{
			echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="green">' .
					Text::_('COM_JTG_CREATED') . '</font></td></tr>';
		}
		else
		{
			echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="red">' .
					Text::_('COM_JTG_NOT_CREATED') . '</font></td></tr>';
		}
	}

	foreach ( $folders_to_chmod AS $folder )
	{
		if ( JPath::canChmod(JPATH_SITE . '/' . $folder) AND (chmod(JPATH_SITE . '/' . $folder, 0777)))
		{
			echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="green">' .
					Text::_('COM_JTG_CHMODDED') . '</font></td></tr>';
		}
		else
		{
			echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' .
					$folder . '</td><td><font color="red">' .
					Text::_('COM_JTG_NOT_CHMODDED') . '</font></td></tr>';
		}
	}

	// Copy Cats image
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/images/cats';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/cats';
	$files = Folder::files($src_folder_to_copy);

	// Copy file by file without erasing existing files
	foreach ($files as $file)
	{
		if (!File::exists($dest_folder_to_copy . '/' . $file) )
		{
			File::copy($src_folder_to_copy . '/' . $file, $dest_folder_to_copy . '/' . $file);
		}
	}

	// Copy additional.ini language files without erasing existing files
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/language';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/language';
	$folders = Folder::folders($src_folder_to_copy, false);

	// For each language folder
	foreach ($folders as $folder)
	{
		$src_folder_to_copy2 = $src_folder_to_copy . '/' . $folder;
		$dest_folder_to_copy2 = $dest_folder_to_copy . '/' . $folder;
		$files = Folder::files($src_folder_to_copy2);
		Folder::create($dest_folder_to_copy2);

		foreach ($files as $file)
		{
			if (!File::exists($dest_folder_to_copy2 . '/' . $file) )
			{
				File::copy($src_folder_to_copy2 . '/' . $file, $dest_folder_to_copy2 . '/' . $file);
			}
		}
	}

	// Copy example tracks without erasing existing files
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/uploaded_tracks';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks';
	$files = Folder::files($src_folder_to_copy);

	// Copy file by file without erasing existing files
	foreach ($files as $file)
	{
		if (!File::exists($dest_folder_to_copy . '/' . $file) )
		{
			File::copy($src_folder_to_copy . '/' . $file, $dest_folder_to_copy . '/' . $file);
		}
	}

	// Copy example image gallery
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/uploaded_tracks_images';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images';

	$folders = Folder::folders($src_folder_to_copy, 'track_*', false);

	// For each uploaded_tracks_images folder
	foreach ($folders as $folder)
	{
		Folder::copy($src_folder_to_copy . '/' . $folder, $dest_folder_to_copy . '/' . $folder, $force = false);
	}

	// Copy difficulty level icons
	$src_folder_to_copy = JPATH_SITE . '/components/com_jtg/assets/images/difficulty_level';
	$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/difficulty_level';
	Folder::copy($src_folder_to_copy, $dest_folder_to_copy, $force = false);

	echo '<tr><td colspan="3">' . Text::sprintf('COM_JTG_INSTALLED_VERSION', $this->release) . '</td></tr>';

	// Update tracks user ID to current user id
	$db = Factory::getDbo();
	$application = Factory::getApplication();
	$uid = Factory::getUser()->id;
	$query = $db->getQuery(true);
	$query->update($db->quoteName('#__jtg_files'))->set($db->quoteName('uid').'='.$db->quote($uid))->where('1'); // Todo: check this is done below as well
	$db->setQuery($query);
	$db->execute();

	// You can have the backend jump directly to the newly installed component configuration page

	// $parent->getParent()->setRedirectURL('index.php?option=com_jtg');

	echo '<tr><td colspan="3">';
	echo '<font color="red" size="+1">' . Text::_('COM_JTG_HINTS') . '</font>';
	echo Text::_('COM_JTG_HINTS_DETAILS');
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

		$db = Factory::getDbo();
		$columns = $db->getTableColumns('#__jtg_cats');
		if(!isset($columns['usepace'])){
			// The usepace row does not exits
			$db->setQuery("ALTER TABLE '#__jtg_cats' ADD 'usepace' TINYINT(1) NOT NULL DEFAULT '0' AFTER 'ordering'; ");
			$db->execute();
		}
		// If installed version is equal to then 0.9.21 ==> remove plugin plg_jtrackgallery_maps v0.1
		$plg_folder = JPATH_SITE . '/plugins/content/plg_jtrackgallery_maps/';
		if (Folder::exists($plg_folder))
		{
			// Remove old plugin version
			Folder::delete($plg_folder);
			$db = Factory::getDbo();
			$application = Factory::getApplication();
			$uid = Factory::getUser($row->uid);
			$db->setQuery("DELETE FROM '#__extensions' WHERE element ='plg_jtrackgallery_maps'");
			$db->execute();
		}

		/* J!Trackgallery >= 0.9.31: remove jtg/default view
		 *  Funtionality now provided by files/map
		 */

		$folder = JPATH_SITE . '/components/com_jtg/views/files/tmpl';
		File::delete($folder.'/map.php'); // Function checks whether file exists
		File::delete($folder.'/map.xml');
		File::delete($folder.'/file.php'); // Moved to new track view
		File::delete($folder.'/file.xml');
		File::delete($folder.'/form.php');
		File::delete($folder.'/form.xml');
		File::delete($folder.'/user.php');
		File::delete($folder.'/user.xml');
		File::delete(JPATH_SITE.'/components/com_jtg/controllers/files.php');
		// openlayers code moved from assets to media
		File::delete(JPATH_SITE.'components/com_jtg/assets/js/ol.js');
		File::delete(JPATH_SITE.'components/com_jtg/assets/js/ol.js.map');
		File::delete(JPATH_SITE.'components/com_jtg/assets/template/default/ol.css');
		File::delete(JPATH_SITE.'components/com_jtg/assets/template/default/ol.css.map');
		/*
		 * Move existing old image gallery
		 * from /images/jtrackgallery/track_xx (version<= 0.9.9)
		 * to /images/jtrackgallery/uploaded_tracks_images/track_xx (version> 0.9.9)
		*/

		$folders = Folder::folders(JPATH_SITE . '/images/jtrackgallery', '^track*', false);

		// Move entire folder (track_xx) if destination folder don't exist
		foreach ($folders as $folder)
		{
			if (!Folder::exists(JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/' . $folder))
			{
				Folder::move(JPATH_SITE . '/images/jtrackgallery/' . $folder, JPATH_SITE . '/images/jtrackgallery/uploaded_tracks_images/' . $folder);
			}
		}

		/*
		 * If it does not exists:
		 * Create difficulty level icons folder and copy icons in it
		 */
		$dest_folder_to_copy = JPATH_SITE . '/images/jtrackgallery/difficulty_level';

		if (!Folder::exists($dest_folder_to_copy))
		{
			Folder::copy(JPATH_SITE . '/components/com_jtg/assets/images/difficulty_level', $dest_folder_to_copy, '', $force = false);
		}

		echo '<p>' . Text::sprintf('COM_JTG_UPDATED', $this->release) . '</p>';

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
	        	if ( Folder::exists($img_dir.'/track_'.$id) ) {
					$cur_img_dir = $img_dir.'/track_'.$id;
			  		$filenames = Folder::files($cur_img_dir);
					foreach ( $filenames as $fname ) 
					{
						// Check extension; change to lowercase .jpg
						$ext = File::getExt($fname);
						if ($ext !== "jpg")
						{
							if (in_array(strtolower($ext),array('jpg','jpeg')))
							{
								$newfname = File::stripExt($fname).'.jpg';
								File::move($fname, $newfname, $cur_img_dir);
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
			         		$lon = JtgHelper::getGpsFromExif($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
			         		$lat = JtgHelper::getGpsFromExif($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
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
		Folder::create(JPATH_SITE . '/images/jtrackgallery/maps');

		if ($this->generateAliases) {
			echo "Track aliases generated from titles";
			// Would like to use Table interface, but cannot get an instance in Joomla 3.9.10

      	$query = 'SELECT id FROM #__jtg_files';
      	$db->setQuery($query);
      	$db->execute();
			$ids = $db->loadColumn();

			foreach ( $ids as $key => $id )
			{
      		$query = 'SELECT title FROM #__jtg_files WHERE id='.$id;
      		$db->setQuery($query);
      		$db->execute();
				$title = $db->loadResult();
				$alias = OutputFilter::stringURLSafe(trim($title));
				if (!empty($alias))
				{
					$query = $db->getQuery(true);
      			$query->select('COUNT(*)')
      				->from($db->quoteName('#__jtg_files'))
      				->where($db->quoteName('alias') . ' = '. $db->quote($alias));
      			$db->setQuery($query);
      			if ($db->loadResult() > 0) $alias = $id.'-'.$alias;
				}
				else
				{
					$alias = $id;
				}
      		$query = 'UPDATE #__jtg_files SET alias='.$db->quote($alias).' WHERE id='.$id;
      		$db->setQuery($query);
      		//$db->execute();
	      	if (!$db->execute())
   	   	{
					$app->enqueueMessag("Storing track ".$id.$db->stderr(),'error');
         		return false;
	      	}
			}
		}
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
		$db = Factory::getDbo();
		$application = Factory::getApplication();

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
			$user = Factory::getUser();
			$uid = $user->id;

			$query = 'UPDATE #__jtg_files SET uid =' . $uid . ' WHERE 1';
			$db->setQuery($query);
			$db->execute();

			// TODO Save default params directly in table
			$query = 'UPDATE #__extensions SET params = ';
			$query .= '\' {
			"jtg_param_default_cat":"10",
			"jtg_param_use_cats":"1",
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
			"jtg_param_info_fields":["dist","ele","time","speed"],
			"jtg_param_show_durationcalc":"1",
			"jtg_param_add_startmarker":"1",
			"jtg_param_show_panzoombar":"1",
			"jtg_param_show_mouselocation":"1",
			"jtg_param_show_scale":"1",
			"jtg_param_allow_mousemove":"1",
			"jtg_param_allow_keymove":"0",
			"jtg_param_show_plugin_button":"1",
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
		// TODO: update some params if old version did not have them

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

		$application = Factory::getApplication();
		$application->enqueueMessage(Text::_('COM_JTG_THANK_YOU_FOR_USING'));
		echo '<p>' . Text::_('COM_JTG_UNINSTALLING') . '</p>';
		$folders_to_delete = array ('images/jtrackgallery');

		$files_to_delete = array ();

		echo '<table><tr><td colspan="3"><b>' . Text::_('COM_JTG_FILES_FOLDERS_TO_DELETE') . '</td></tr>';
		echo '<tr><td>' . Text::_('COM_JTG_FILE') . '/' . Text::_('COM_JTG_FOLDER') . '</td><td>' . Text::_('COM_JTG_NAME') . '</td><td>' . Text::_('COM_JTG_STATE') . '</td></tr>';

		foreach ( $files_to_delete AS $file )
		{
			if (!File::exists($file))
			{
				echo '<tr><td>' . Text::_('COM_JTG_FILE') . '</td><td>' . $file . '</td><td><font color="green">' . Text::_('COM_JTG_NOT_EXISTS') . ' </font>  </td></tr>';
			}
			elseif (File::delete($file))
			{
				echo '<tr><td>' . Text::_('COM_JTG_FILE') . '</td><td>' . $file . '</td><td><font color="green">' . Text::_('COM_JTG_DELETED') . ' </font> </td></tr>';
			}
			else
			{
				echo '<tr><td>' . Text::_('COM_JTG_FILE') . '</td><td>' . $file . '</td><td><font color="red">' . Text::_('COM_JTG_ERROR') . '/' . Text::_('COM_JTG_NOT_DELETED') . '</font> </td></tr>';
			}
		}

		foreach ( $folders_to_delete AS $folder )
		{
			if (!Folder::exists(JPATH_SITE . '/' . $folder))
			{
				echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' . $folder . '</td><td><font color="green">' . Text::_('COM_JTG_NOT_EXISTS') . '</font> </td></tr>';
			}
			elseif (Folder::delete(JPATH_SITE . '/' . $folder))
			{
				echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' . $folder . '</td><td><font color="green">' . Text::_('COM_JTG_DELETED') . '</font> </td></tr>';
			}
			else
			{
				echo '<tr><td>' . Text::_('COM_JTG_FOLDER') . '</td><td>' . $folder . '</td><font color="red">' . Text::_('COM_JTG_ERROR') . Text::_('COM_JTG_NOT_DELETED') . '</font> </td></tr>';
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
	 * TODO: can this be removed?
	 */
	public function getParam( $name, $id = 0 )
	{
		$db = Factory::getDbo();
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
	 * 
	 * TODO: can this be removed?
	 */
	public function setParams($param_array = null, $type = 'edit', $id = 0)
	{
		if ( count($param_array) > 0 )
		{
			// Read the existing component value(s)
			$db = Factory::getDbo();
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
