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

// Import Joomla! libraries
jimport('joomla.application.component.model');

/**
 * Model Class Configuration
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgModelConfig extends JModelLegacy
{
	/**
	 * function_description
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function saveConfig()
	{
		// Get post data
		$row = JFactory::getApplication()->input->getArray();

		// Store tables if they not exists
		$cfg = JtgHelper::getConfig();
		$createColumns = $this->createColumns($row, "config");

		if ($createColumns !== true)
		{
			return $createColumns;
		}
		// Bereinige $row um OSM-Available Map
		$table = $this->getTable('jtg_config');
		$table->bind($row);

		if (!$table->store())
		{
			return $table->getError();
		}
		// Config saved,

		if ( ($row['max_geoim_height'] <> $cfg->max_geoim_height)
			OR ($row['max_thumb_height'] <> $cfg->max_thumb_height) )
		{
			// Recreate thumbnails if max_height changed
			require_once JPATH_SITE . '/administrator/components/com_jtg/models/thumb_creation.php';
			Com_Jtg_Refresh_thumbnails();
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $row       param_description
	 * @param   unknown_type  $tablekey  param_description
	 *
	 * @return true or errormessage
	 */
	function createColumns($row, $tablekey)
	{
		echo "deprecated: JtgModelConfig::createColumns";

		// Find out exists columns
		$db = JFactory::getDBO();
		$sql = 'Select * from #__jtg_' . $tablekey;
		$db->setQuery($sql);
		$existobj = $db->loadObject();
		$existarr = array();

		// Object to array conversion

		foreach ($existobj AS $table => $value)
		{
			$existarr[$table] = $value;
		}
		// Exclude unnecessary columns
		$ignore = array();

		if ($tablekey == "config")
		{
			$ignore = array('map','option','task','jtg_param_default_map');

			for ($i = 0;$i < 10;$i++)
			{
				$ignore[] = 'jtg_param_allow_map_' . $i;
			}
		}
		// Find out missing columns
		$missingcolumns = array();

		foreach ($row AS $key => $value)
		{
			$istoken = JSession::checkToken();

			if ((!in_array($key, $ignore)) AND (!array_key_exists($key, $existarr)) AND (!$istoken))
			{
				$missingcolumns[] = $key;
			}
		}

		if ( count($missingcolumns) == 0 )
		{
			// All necessary colums exists
			return true;
		}

		// Load install.sql
		$file = JPath::clean(JPATH_ADMINISTRATOR . '/components/com_jtg/sql/install.sql');

		if (!is_file($file))
		{
			return ('File "' . $file . '" not found');
		}

		if (jimport('joomla.filesystem.file'))
		{
			$sqlcontent = file_get_contents($file);
		}

		$sqlcontent = explode("\n", $sqlcontent);
		$content = null;
		$content_switch = false;
		$comma = "";

		// Convert sql-statement from CREATE to ALTER ADD
		foreach ($sqlcontent AS $zeile)
		{
			if ($content_switch)
			{
				$tempcontent = explode('`', $zeile);

				if (count($tempcontent) == 3)
				{
					$tempcontent = $tempcontent[1];
					error_log("zeile $zeile");
					$zeile = str_replace(",", '', $zeile);
					error_log("zeile after replace $zeile");

					if (in_array($tempcontent, $missingcolumns))
					{
						$content .= $comma . "ADD COLUMN " . $zeile;

						if ($comma == "")
						{
							$comma = ",\n";
						}
					}
				}
			}

			if ((preg_match("/#__jtg_" . $tablekey . "/", $zeile))
				AND (preg_match("/CREATE/", $zeile)) AND (!$content_switch))
			{
				$zeile = str_replace("(", "", $zeile);
				$zeile = str_replace("CREATE", "ALTER IGNORE", $zeile);
				$zeile = str_replace("IF NOT EXISTS", null, $zeile);
				$content .= $zeile . "\n";
				$content_switch = true;
			}

			if ((preg_match("/\;/", $zeile)) AND ($content_switch))
			{
				$content .= ";";
				break;
			}
		}

		$db = JFactory::getDBO();
		$db->setQuery($content);
		$db->execute();

		if ($db->getErrorNum())
		{
			return( ($db->stderr()));
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @return Object
	 */
	function getContent()
	{
		$db = JFactory::getDBO();
		$sql = 'Select id from #__categories where title=\'term\'';
		$db->setQuery($sql);
		$catid = $db->loadResult();
		$sql = 'Select id from #__categories where title=\'jtg\'';
		$db->setQuery($sql);
		$secid = $db->loadResult();

		if (($catid == null) OR ($secid == null))
		{
			return false;
		}

		$mainframe = JFactory::getApplication();
		$query = "SELECT id, title FROM #__content WHERE"
		. "\n sectionid='" . $secid . "'"
		. "\n AND catid='" . $catid . "'"
		. "\n AND state='1'";

		$db->setQuery($query);
		$result = $db->loadObjectList();

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
	function getTemplates()
	{
		jimport('joomla.filesystem.folder');
		$templates = JFolder::listFolderTree('../components/com_jtg/assets/template', '', 1);

		return $templates;
	}
}
