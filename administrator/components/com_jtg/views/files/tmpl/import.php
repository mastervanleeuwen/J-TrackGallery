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

defined('_JEXEC') or die('Restricted access');

// Toolbar
JToolBarHelper::title(JText::_('COM_JTG_ADD_FILES'), 'categories.png');
JToolBarHelper::back();
JToolBarHelper::spacer();
$bar = JToolBar::getInstance('toolbar');
$folder = JUri::base() . 'index.php?option=com_jtg&tmpl=component&controller=files&task=upload';
jimport('joomla.filesystem.folder');

// Popup:
JToolBarHelper::addNew('newfiles', JText::_('COM_JTG_RELOAD'));

// JToolBarHelper::cancel('jtg');
JToolBarHelper::save('savefiles', JText::_('COM_JTG_SAVE_NEW_FILE'), 'save.png');
JToolBarHelper::deleteList('COM_JTG_VALIDATE_DELETE_ITEMS', 'removeFromImport');
JToolBarHelper::help('files/import', true);
$document = JFactory::getDocument();
$style = "   .row00 {background-color: #FFFF99;}\n";

if (JVERSION >= 3.0)
{
	$style .= "	select, textarea, input{
	width: auto !important;\n}";
}

$document->addStyleDeclaration($style);

?>
<form action="" method="post" name="adminForm" id="adminForm"
	class="adminForm" enctype="multipart/form-data">
	<?php
	$yesnolist = array(
			array('id' => 0, 'title' => JText::_('JNO')),
			array('id' => 1, 'title' => JText::_('JYES'))
	);
	$tracks = $this->rows;
	$trackfilename = array();

	// Vorhandene Dateinamen in array speichern
	for ($i = 0;$i < count($tracks);$i++)
	{
		$trackfilename[$i] = $tracks[$i]->file;
	}

	$level = array("access" => 0);
	$level = JArrayHelper::toObject($level);
	$row = 0;
	$count = 0;
	$errorposted = false;
	$importdir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/import';
	$filesdir = JPATH_SITE . '/images/jtrackgallery/uploaded_tracks/';
	$filesdir = JFolder::files($filesdir);

	// File types: *.gpx, *.trk, *.kml (not case sensitive)
	$regex = "(.[gG][pP][xX]$|.[tT][rR][kK]$|.[kK][mM][lL]$)";
	$me = JFactory::getUser();
	$files = JFolder::files($importdir, $regex, true, true);

	$model = $this->getModel();
	$terrain = $model->getTerrain("*", true, " WHERE published=1 ");
	$terrainsize = count($terrain);

	if ( $terrainsize > 6 )
	{
		$terrainsize = 6;
	}

	$cats = $model->getCats(0, 'COM_JTG_SELECT', 0, 0);
	$catssize = count($cats);

	if ( $catssize > 6 )
	{
		$catssize = 6;
	}

	$userslist = $model->getUsers(false, $where = "WHERE block = 0");
	$userslistsize = count($userslist);

	if ( $userslistsize > 6 )
	{
		$userslistsize = 6;
	}

	$cfg = JtgHelper::getConfig();
	$levels = explode("\n", $cfg->level);
	$toggle['level'] = "<select name=\"level_all\" size=\"6\" onclick=\"setSelect('level');\">
	<option value=\"0\">" . JText::_('COM_JTG_SELECT') . "</option>";
	$i = 1;

	foreach ($levels AS $level)
	{
		if ( trim($level) != "" )
		{
			$toggle['level'] .= "						<option value=\"$i\">$i - " . JText::_(trim($level)) . "</option>";
			$i++;
		}
	}

	$toggle['level'] .= "					</select>\n";

	$table = ("		<tbody>\n
			<tr class=\"row00\">
			<td ><input type=\"checkbox\" onclick=\"Joomla.checkAll(this)\" title=\"" . JText::_('JGLOBAL_CHECK_ALL')
			. "\" value=\"\" checked=\"checked\" name=\"checkall-toggle\"></td>
			<td colspan=\"2\" align=\"right\">	<b>" . JText::_('COM_JTG_PRESELECTION') . "==></b><br /><br />"
			. JText::_('COM_JTG_PRESELECTION_DESCRIPTION') . "</td>
			<td>" . $toggle['level'] . "</td>
			<td>" . JHtml::_('select.genericlist', $cats, 'catid_all[]', 'size="' . $catssize . '" multiple="multiple" onclick="setSelectMultiple(\'catid\')"', 'id', 'treename')
			. "<br /><small>" . JText::_('COM_JTG_MULTIPLE_CHOICE_POSSIBLE') . "</small></td>
			<td>" . JHtml::_('select.genericlist', $terrain, 'terrain_all[]', 'size="' . $terrainsize . '"  multiple="multiple" onclick="setSelectMultiple(\'terrain\')"', 'id', 'title')
			. "<br /><small>" . JText::_('COM_JTG_MULTIPLE_CHOICE_POSSIBLE') . "</small></td>
			<td>" . JHtml::_('select.genericlist', $userslist, 'uid_all', 'class="inputbox" size="' . $userslistsize . '" onclick="setSelect(\'uid\')"', 'id', 'title', $me->id) . "</td>
			<td>" . JtgHelper::getAccessList(0, "access_all", "onclick=\"setSelect('access')\"") . "</td>
			<td>" . JHtml::_('select.genericlist', $yesnolist, 'hidden_all', 'class="inputbox" size="1" onclick="setSelect(\'hidden\')"', 'id', 'title') . "</td>
			</tr>
			");

	if ( $files !== false )
	{
		foreach ($files AS $file)
		{
			$row = (1 - $row);
			$lists['cats'] = JHtml::_('select.genericlist',
					$cats,
					'catid_' . $count . '[]',
					'multiple="multiple" size="' . $catssize . '"',
					'id', 'treename' );
			$editor = JFactory::getEditor();
			$buttons = array(
					"pagebreak",
					"readmore");
			$lists['access'] = JtgHelper::getAccessList(0, "access_" . $count);
			$lists['uid'] = JHtml::_('select.genericlist', $userslist, 'uid_' . $count, ' size="' . $userslistsize . '"', 'id', 'title', $me->id);

			// 				genericlist($arr, $name, $attribs=null, $key= 'value', $text= 'text', $selected = null, $idtag = false, $translate = false)
			$lists['hidden'] = JHtml::_('select.genericlist', $yesnolist, 'hidden_' . $count, 'class="inputbox" size="2"', 'id', 'title', 0);
			$lists['terrain'] = JHtml::_('select.genericlist',
					$terrain,
					'terrain_' . $count . '[]',
					'multiple="multiple" size="' . $terrainsize . '"',
					'id', 'title');

			jimport('joomla.filesystem.file');
			$extension = JFile::getExt($file);
			$file_tmp = explode('.', $file);
			unset($file_tmp[(count($file_tmp) - 1)]);
			$filename = implode('.', $file_tmp);

			// TODO Verify these lines !!
			$filename = $filename . "." . $extension;
			$filename = str_replace($importdir . '/', '', $filename);
			// All the lines above seem to just get the 'file base name'  is there a utility function for this?
			$filename_wof = explode('/', $filename);
			$filename_wof = $filename_wof[(count($filename_wof) - 1)];

			// $filename = strtolower(JFile::getName($file));

			if (in_array(strtolower($filename_wof), $filesdir) )
			{
				// Track already existing
				$check = 1;
				$filename_exists = "<input type=\"hidden\" name=\"filenameexists_" . $count . "\" value=\"true\">\n";
			}
			else
			{
				$check = $this->checkFilename($file, false);

				if ($check === true)
				{
					$filename_exists = "<input type=\"hidden\" name=\"filenameexists_" . $count . "\" value=\"false\">\n";
				}
			}

			$gpsData = new GpsDataClass("kilometer");

			// TODO strtolower or not??
			$gpsData->loadFileAndData($file, strtolower($filename_wof));

			if ($check === true)
			{
				$check = $gpsData->fileChecked;
			}

			$title = $gpsData->trackname;
			$date = $gpsData->Date;
			$description = $gpsData->description;

			$lists['description'] = $editor->display('desc_' . $count, $description, '100%', '200', '20', '20', $buttons, null, null);

			{
				if ( ( $check != 8 ) AND ( $errorposted == false ) )
				{
					$errorposted = true;
					JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_ERROR_FOUND'), 'Notice');
				}

				if (($check == 1) and ( $check !== true ))
				{
					$file_replace = ("<input type=\"checkbox\" id=\"fr" . $count . "\" value=\"1\" name=\"file_replace_" . $count . "\" onclick=\"Joomla.isChecked(this.checked);\" />\n");
				}
				else
				{
					// Set hidden file_replace <input> to 0)
					$file_replace = ("<input type=\"hidden\" name=\"file_replace_" . $count . "\" value=\"0\" />");
					echo $file_replace;
				}

				if ( $check !== true )
				{
					if ( $check == 1 )
					{
						$tt = JTEXT::sprintf('COM_JTG_TT_ERR_FILEEXIST', $file_replace);
						$color = "green";
					}
					elseif ( $check == 2 )
					{
						$tt = JText::_('COM_JTG_TT_ERR_NODELETE');
						$color = "red";
					}
					elseif ( $check == 3 )
					{
						$tt = JText::_('COM_JTG_TT_ERR_FILENAME_TOO_LONG');
						$color = "brown";
					}
					elseif ( $check == 4 )
					{
						$tt = JText::_('COM_JTG_TT_ERR_BADFILENAME') . " (&)";
						$color = "red";
					}
					elseif ( $check == 5 )
					{
						$tt = JText::_('COM_JTG_TT_ERR_BADFILENAME') . " (#)";
						$color = "red";
					}
					elseif ( $check == 6 )
					{
						$tt = JText::_('COM_JTG_TT_ERR_NOTRACK');
						$color = "lightgrey";
					}
					elseif ( $check == 7 )
					{
						// No longer used !!
						$tt = JText::_('COM_JTG_TT_ERR_NOPOINTINTRACK');
						$color = "lightgrey";
					} elseif ( $check == 8 )
					{
						// No longer used !!
						$tt = JText::_('COM_JTG_TT_ERR_MORETRACKS');
						$color = "blue";
					}

					$table .= "			<tr class=\"row$row " . ($row? "row-odd":"row-even") . "\">\n<td colspan=\"9\">" . JText::_('COM_JTG_GPS_FILE') . ':<b> ' . $filename . '</b>: <b><font color=\"red\">' . $tt . "</font></b><br /></tr>\n";
				}
				else
				{
					$table .= "			<tr class=\"row$row " . ($row? "row-odd":"row-even") . "\">\n<td colspan=\"9\">" . JText::_('COM_JTG_GPS_FILE') . ':<b> ' . $filename . '</b>: ' . JText::_('COM_JTG_TT_FILEOKAY') . "</tr>\n";
				}

				$table .= ("		<tr class=\"row$row " . ($row? "row-odd":"row-even") . "\">\n");

				// Row: Selector + Date
				{
					$table .= '<td nowrap>' . $filename_exists;

					/*
					 * ($check === true) eine Spur mit Punkten an erster Stelle vorhanden
					 * ( $check == 1 )Dateiname existiert
					 * ( $check == 2 )// Kein Löschrecht
					 * ( $check == 3 )// Dateinamenslänge überschritten
					 * ( $check == 4 )// When "&" in file name
					 * ( $check == 5 )// When "#" in file name
					 * ( $check == 6 )// Keine Spur vorhanden
					 * ( $check == 7 )// Spur vorhanden, aber kein Punkt
					 * ( $check == 8 )// Spur vorhanden, aber nicht an erster Stelle. Evtl. mehrere Spuren
					 */
					if (( $check === true )
						OR ( $check == 1 )
						OR ( $check == 3 )
						OR ( $check == 5 )
						OR ( $check == 6 )
						OR ( $check == 7 )
						OR ( $check == 8 ))
					{
						$table .= ("<input type=\"checkbox\" checked=\"checked\" id=\"cb" . $count . "\" value=\"" . $file . "\" name=\"import_" . $count . "\" onclick=\"Joomla.isChecked(this.checked);\" /></td>\n");
					}

					$table .= ("				<td><input id=\"date_" . $count . "\" type=\"text\" name=\"date_" . $count . "\" size=\"10\" value=\"");

					if ($date === false)
					{
						$table .= (date('Y-m-d', time()) . '" /><font color=\"orange\">&nbsp;</font></td>');
					}
					else
					{
						$table .= ($date . "\" /></td>");
					}
				}

				// Row: GPS filename / Title
				{
					$table .= ("				<td>");
					$table .= ("<input type=\"hidden\" name=\"file_" . $count . "\" value=\"" . $file . "\" />\n");
					$table .= "\n				<input id=\"title_" . $count . "\" type=\"text\" name=\"title_" . $count . "\" value=\"" . $title . "\" size=\"30\" /></td>\n";
				}

				// Row: Difficulty level
				{
					$table .= "				<td>\n";
					$table .= "					<select id=\"level_" . $count . "\" name=\"level_" . $count . "\" size=\"6\"\n>
					<option>" . JText::_('COM_JTG_SELECT') . "</option>\n";
					$i = 1;

					foreach ($levels AS $level)
					{
						if ( trim($level) != "" )
						{
							$table .= "<option value=\"$i\">$i - " . JText::_(trim($level)) . "</option>\n";
							$i++;
						}
					}

					$table .= "					</select>
					</td>\n";
				}

				// Row: Categorie
				$table .= ("				<td>" . $lists['cats'] . "</td>\n");

				// Row: Terrain
				$table .= ("				<td>" . $lists['terrain'] . "</td>\n");

				// Row: Author
				$table .= ("				<td>" . $lists['uid'] . "</td>\n");

				// Row: Acces level
				$table .= ("				<td>" . $lists['access'] . "</td>\n");

				// Row: Hidden
				$table .= ("				<td>" . $lists['hidden'] . "</td>\n");

				// Row: NULL
				$table .= ("				\n");

				$table .= ("			</tr>\n<tr class=\"row$row " . ($row? "row-odd":"row-even") . "\">\n");

				// Row: Decription
				$table .= ("				<td>&nbsp;&nbsp;</td><td colspan='8'>" . JText::_('COM_JTG_DESCRIPTION') . ":<br />\n" . $lists['description'] . "</td>\n");

				$table .= ("				\n");
				$table .= ("			</tr>\n");

				$count++;

				if ( $count > 50 )
				{
					JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_TOO_MUCH_TRACKS_TO_IMPORT'), 'Warning');
					break;
				}
			}
		}
	}

	$table_header = ("	<table class=\"adminlist\" cellpadding=\"1\">
			<thead>
			<tr>
			<th class=\"title\" width=\"1\">&nbsp;</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_DATE') . "</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_TITLE') . "</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_LEVEL') . "</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_CAT') . "</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_TERRAIN') . "</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_INFO_AUTHOR') . "</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_ACCESS_LEVEL') . "</th>
			<th class=\"title\" width=\"1\">" . JText::_('COM_JTG_HIDDEN') . "</th>
			</tr>
			</thead>\n");

	$table_footer = ("		</tbody>\n	</table>\n");

	if ( $count == 0 )
	{
		$model = $this->getModel();
		$rows = $model->_fetchJPTfiles();

		if ( (JFolder::exists(JPATH_BASE . '/components/com_joomgpstracks')) AND (count($rows) != 0 ) )
		{
			/* DEPRECATED to be replaced by import from injoosm
			* by default, import from joomgpstracks if no tracks uploaded in JTrackGallery folder
			* Datenimport von joomgpstracks BEGIN
			*/

			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_FOUND_H'));
			echo JText::_('COM_JTG_FOUND_T') . "<br /><br />";
			echo JText::_('COM_JTG_FOUND_L');

			echo " <a href=\"index.php?option=com_jtg&task=importjgt&controller=files\"><img src=\"/administrator/templates/bluestork/images/notice-download.png\" /></a>";

			// Datenimport von joomgpstracks END
		}
		else
		{
			// Nothing in import folder
			JFactory::getApplication()->enqueueMessage(
					JText::_('COM_JTG_IMPORTFOLDEREMPTY') . " (/"
					. 'images/jtrackgallery/uploaded_tracks/import)', 'Warning' );
		}
	}
	else
	{
		echo $table_header . $table . $table_footer;
	}

	echo JHtml::_('form.token');
	$js = "

	function setSelectMultiple(select) {
	var srcListName = select + '_all';
	var form = document['adminForm'];
	var srcList = form[srcListName];
	var values =[];
	var i;
	for (i=0; i<srcList.options.length; i++) {
	values[i] = (srcList.options[i].selected==true);
}
for (i=0; i < " . $count . "; i++) {
setSelectedMultipleValues('adminForm', select + '_' + i , values);
}
}
function setSelectedMultipleValues( frmName, srcListName, values ) {
var form = eval( 'document.' + frmName );
var srcList = eval( 'form.' + srcListName );
var i;
for (i=0; i<srcList.options.length; i++) {
srcList.options[i].selected = values[i];
}
}


function setSelect(select) {

var value = getSelectedValue('adminForm', select + '_all');
for (i=0; i < " . $count . "; i++) {
setSelectedValue('adminForm', select + '_' + i , value);
}
}

function getSelectedValue(frmName, srcListName) {
var form = eval( 'document.' + frmName );
var srcList = eval( 'form.' + srcListName );

i = srcList.selectedIndex;
if (i != null && i > -1) {
return srcList.options[i].value;
}
else
{
return null;
}
}


function setSelectedValue( frmName, srcListName, value ) {
var form = eval( 'document.' + frmName );
var srcList = eval( 'form.' + srcListName );

var srcLen = srcList.length;

for (var i=0; i < srcLen; i++) {
srcList.options[i].selected = false;
if (srcList.options[i].value == value) {
srcList.options[i].selected = true;
}
}
}
";
	$document = JFactory::getDocument();
	$document->addScriptDeclaration($js);
	echo "	<input type=\"hidden\" name=\"option\" value=\"com_jtg\" />
	<input type=\"hidden\" name=\"controller\" value=\"files\" />
	<input type=\"hidden\" name=\"task\" value=\"\" />
	<input type=\"hidden\" name=\"found\" value=\"" . $count . "\" />
	<input type=\"hidden\" name=\"boxchecked\" value=\"0\" />\n";
	echo "</form>\n";
