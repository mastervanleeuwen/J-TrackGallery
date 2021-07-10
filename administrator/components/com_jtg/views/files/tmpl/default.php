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
JToolBarHelper::title(JText::_('COM_JTG_GPS_FILES'), 'categories.png');
JToolBarHelper::back();
JToolBarHelper::spacer();
$bar = JToolBar::getInstance('toolbar');
$folder = JUri::base() . 'index.php?option=com_jtg&tmpl=component&controller=files&task=upload';
$bar->appendButton('Popup', 'upload', 'COM_JTG_UPLOAD_TRACKS', $folder, 550, 400);

// Normal Window:
JToolBarHelper::addNew('newfiles', $alt = 'COM_JTG_IMPORT_FILES');
JToolBarHelper::editList('editfile');
JToolBarHelper::publish();
JToolBarHelper::unpublish();
JToolBarHelper::custom('toshow', 'toshow', null, $alt = 'COM_JTG_TOSHOW_SMALL');
JToolBarHelper::custom('tohide', 'tohide', null, $alt = 'COM_JTG_TOHIDE_SMALL');
JToolBarHelper::deleteList('COM_JTG_VALIDATE_DELETE_ITEMS');
JToolBarHelper::help('files/default', true);

$ordering = ($this->lists['order'] == 'ordering');
$document = JFactory::getDocument();
$document->addStyleSheet(JUri::base(true) . '/components/com_jtg/template.css');

?>
<form action="" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td align="left" style="width:100%;"><?php echo JText::_('COM_JTG_FILTER'); ?>:
				<input type="text" name="search" id="search"
				value="<?php echo $this->lists['search'];?>" class="text_area"
				onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();">
					<?php echo JText::_('COM_JTG_APPLY'); ?>
				</button>
				<button
					onclick="document.getElementById('search').value='';this.form.getElementById('filter_state').value='';this.form.submit();">
					<?php echo JText::_('COM_JTG_RESET'); ?>
				</button>
			</td>
			<td nowrap="nowrap"></td>
		</tr>
	</table>
	<table class="adminlist">
		<tfoot>
			<tr>
				<td colspan="14"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php
			$k = 0;
			$missingcat = array();
			$missingterrain = false;
			$cfg = $this->cfg;
			$iconpath	= JUri::root() . "components/com_jtg/assets/template/" . $cfg->template . "/images/";

			for ($i = 0, $n = count($this->rows); $i < $n; $i++)
			{
				$row = $this->rows[$i];
				$row->groupname = $this->buildRowGroupname($row->access);

				switch ($row->access)
				{
					case 9:
						$access = "<font color='orange'>" . JText::_('COM_JTG_PRIVATE') . "</font>";
						break;

					case 0:
						$access = JText::_('COM_JTG_PUBLIC');
						break;

					case 1:
						$access = JText::_('COM_JTG_REGISTERED');
						break;

					case 2:
						$access = JText::_('COM_JTG_ADMINISTRATORS');
						break;
				}

				$checked 	= JHtml::_('grid.checkedout', $row, $i);
				$published 	= JHtml::_('grid.published', $row, $i);
				$user		= JFactory::getUser($row->uid);
				$imagelink	= $this->buildImageFiletypes($row->istrack, $row->iswp, $row->isroute, $row->iscache);

				$title		= $this->buildEditKlicks(($row->title? $row->title:JText::_('COM_JTG_NO_TITLE')), $i);
				$hidden		= $row->hidden;
				$hiddenlink	= $this->buildHiddenImage($iconpath, $hidden, $i);
				$catids		= explode(",", $row->catid);

				if ( ( $catids === $row->catid ) OR ( $row->catid == "0" ) OR ( $row->catid == "" ) )
				{
					$cats = "<font class=\"emptyEntry\">" . JText::_('COM_JTG_NOTHING') . "</font>";
				}
				else
				{
					$cats = "";
					$l = 0;

					foreach ($catids AS $catid)
					{
						$cattree = $this->parseCatTree($this->cats, $catid);

						if ( count($cattree["missing"]) != 0 )
						{
							foreach ($cattree["missing"] as $miss)
							{
								if ( !isset($missingcat[$miss]) )
								{
									$missingcat[$miss] = $miss;
								}
							}
						}

						$cats .= $cattree["tree"] . ",<br/>";
						$l++;
					}

					// TODO: Improve next "if" ... parse not needed (use cattree !!!
					if ( $l == 1 )
					{
						// Only List if more than 1 entry
						$cats = $this->parseCatTree($this->cats, $catid);
						$cats = $cats["tree"];
					}
				}

				$terrains = $row->terrain;
				$terrains = explode(",", $terrains);
				$terrain = array();
				$model = new JtgModelFiles;

				foreach ($terrains as $v)
				{
					$tmp = $model->getTerrain("*", false, "WHERE id = " . $v);

					if ( isset( $tmp[0] ) AND ( $tmp[0]->title ) )
					{
						$terrain[] = JText::_($tmp[0]->title);
					}
					else
					{
						$terrain[] = "<font class=\"errorEntry\">" .
								JText::sprintf('COM_JTG_ERROR_MISSING_TERRAINID', $v) .
								"</font>";

						if ( $v != 0 )
						{
							$missingterrain = true;
						}
					}
				}

				if ( ( isset($terrains[0]) ) AND ( $terrains[0] == "" ) )
				{
					$terrain = "<font class=\"emptyEntry\">" . JText::_('JNONE') . "</font>";
				}
				else
				{
					$terrain = implode(", ", $terrain);
				}
				?>
			<tr class="<?php echo "row$k ";
				echo $k? 'row-odd':'row-even'; ?>">
				<!--   <td align="center"><?php echo $this->pagination->getRowOffset($i); ?></td>-->
				<td align="center"><?php echo $row->id; ?></td>
				<td align="center"><?php echo $checked; ?></td>
				<td align="center">
					<?php echo '<span class="hasTip" title="' . $row->file . '">' . $title . '</span>'; ?> </td>
				<td align="center" nowrap><?php echo $imagelink; ?></td>
				<td align="left"><?php echo $cats; ?></td>
				<td align="center"><?php echo $terrain; ?></td>
				<td align="center"><?php echo $row->level; ?></td>
				<td align="center"><?php echo $row->date; ?></td>
				<td align="center"><?php echo $published;?></td>
				<td><?php echo $row->default_map? $row->default_map: ''; ?></td>
				<td align="center"><?php echo $hiddenlink;?></td>
				<td align="center"><?php echo $access; ?></td>
				<td align="center"><?php echo $user->username;?></td>
			</tr>
			<?php
			$k = 1 - $k;
			}
			?>
		</tbody>
		<?php
		if ( count($missingcat) == 0)
		{
			$missingcat = null;
		}
		else
		{
			$missingcat = "<br /><font class=\"errorEntry\">"
					. JText::sprintf('COM_JTG_ERROR_MISSING_CATS')
					. "</font>";
		}

		if ($missingterrain === true)
		{
			$missingterrain = "<br /><font class=\"errorEntry\">"
					. JText::sprintf('COM_JTG_ERROR_MISSING_TERRAINS')
					. "</font>";
		}
		else
		{
			$missingterrain = null;
		}

		$checkall = "<input type=\"checkbox\" onclick=\"Joomla.checkAll(this)\" title=\"" . JText::_('JGLOBAL_CHECK_ALL') . "\" value=\"\" name=\"checkall-toggle\">";

		?>
		<thead>
			<tr>
				<!-- <th class="title"><?php echo JText::_('COM_JTG_NUM'); ?></th> -->
				<th class="title" nowrap="nowrap"><?php
				echo JHtml::_('grid.sort',
						JText::_('COM_JTG_ID'), 'id', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
				<th class="title"><?php echo $checkall; ?></th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_TITLE') . "<small> (" . JText::_('COM_JTG_GPS_FILE') . ")</small> ",
						'title', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
				<th class="title"><?php
				echo JText::_('COM_JTG_GPS_FILETYPE');
				?>:</th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_CAT'),
	'cat', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:
					<?php echo $missingcat; ?>
				</th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_TERRAIN'),
	'terrain', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:
					<?php echo $missingterrain; ?>
				</th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_LEVEL'),
						'level', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_DATE'),
						'date', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_PUBLISHED'),
						'published', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
				<th class="title"><?php echo JText::_('COM_JTG_DEFAULT_MAP'); ?></th>
				<th class="title"><?php echo JText::_('COM_JTG_DEFAULT_OVERLAYS'); ?></th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_HIDDEN'),
						'hidden', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_ACCESS_LEVEL'),
						'access', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
				<th class="title"><?php
				echo JHtml::_('grid.sort', JText::_('COM_JTG_INFO_AUTHOR'),
						'uid', @$this->lists['order_Dir'], @$this->lists['order'], 'files'); ?>:</th>
			</tr>
		</thead>
	</table>
	<input type="hidden" name="option" value="com_jtg" /> <input
		type="hidden" name="task" value="files" /> <input type="hidden"
		name="boxchecked" value="0" /> <input type="hidden" name="controller"
		value="files" /> <input type="hidden" name="filter_order"
		value="<?php echo $this->lists['order']; ?>" /> <input type="hidden"
		name="filter_order_Dir"
		value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	&nbsp;
</form>
