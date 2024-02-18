<?php
/**
 * @component  J!Track Gallery (jtg) for Joomla! 2.5 and 3.x
 *
 *
 * @package     Comjtg
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

// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;

/**
 * Model Class Categorie
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class JtgModelCat extends JModelLegacy
{
	/**
	 * function_description
	 *
	 */
	public function __construct()
	{
		parent::__construct();

		$cids = JFactory::getApplication()->input->get('cid', array(), 'array');
		$edit	= JFactory::getApplication()->input->get('edit', true);

		if ($edit && isset($cids[0]))
		{
			$this->setId((int) $cids[0]);
		}
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function saveCatImage()
	{
		JSession::checkToken() or die( 'JINVALID_TOKEN' );
		jimport('joomla.filesystem.file');
		$files = JFactory::getApplication()->input->files->get('files');

		return $this->uploadCatImage($files);
	}

	/**
	 * function_description
	 *
	 * @param   integer  $id  param_description
	 *
	 * @return return_description
	 */
	function setId($id)
	{
		// Set weblink id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * function_description
	 *
	 * @param   string  $direction  param_description
	 *
	 * @return boolean
	 */
	function move($direction)
	{
		$row = $this->getTable('jtg_cats');

		if (!$row->load($this->_id))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		if (!$row->move($direction))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   string  $order  param_description
	 * @param   array   $cid    param_description
	 *
	 * @return boolean
	 */
	function saveorder($order, $cid = array())
	{
		$row = $this->getTable('jtg_cats');
		$groupings = array();

		// Update ordering values
		for ( $i = 0; $i < count($cid); $i++ )
		{
			$row->load((int) $cid[$i]);

			// Track categories
			$groupings[] = $row->catid;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];

				if (!$row->store())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}
		}

		// Execute updateOrder for each parent group
		$groupings = array_unique($groupings);

		foreach ($groupings as $group)
		{
			$row->reorder('id = ' . (int) $group);
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   array    $cid      param_description
	 * @param   integer  $publish  param_description
	 *
	 * @return boolean
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	= JFactory::getUser();

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__jtg_cats'
			. ' SET published = ' . (int) $publish
			. ' WHERE id IN ( ' . $cids . ' )'
			. ' AND ( checked_out = 0 OR ( checked_out = ' . (int) $user->get('id') . ' ) )';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $files  param_description
	 *
	 * @return return_description
	 */
	function deleteCatImage($files)
	{
		jimport('joomla.filesystem.file');
		$path = JPATH_SITE . "/images/jtrackgallery/cats/";

		$return = true;

		foreach ($files as $file)
		{
			if (!JFile::delete($path . $file))
			{
				$return = false;
			}
		}

		return $return;
	}

	/**
	 * function_description
	 *
	 * @param   array  $cid  param_description
	 *
	 * @return boolean
	 */
	function delete($cid = array())
	{
		jimport('joomla.filesystem.file');
		$result = false;

		if (count($cid))
		{
			ArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			// Delete the images
			$query = "SELECT * FROM #__jtg_cats"
			. "\n WHERE id IN ( ' . $cids . ' )";

			$this->_db->setQuery($query);
			$rows = $this->_db->loadObjectList();

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}

			foreach ($rows as $row)
			{
				JFile::delete(JPATH_SITE . '/images/jtrackgallery/cats/' . $row->image);
			}

			// Delete from DB
			$query = 'DELETE FROM #__jtg_cats'
			. ' WHERE id IN ( ' . $cids . ' )';
			$this->_db->setQuery($query);

			if (!$this->_db->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function saveCat()
	{
		$mainframe = JFactory::getApplication();

		// Check the token
		JSession::checkToken() or die( 'JINVALID_TOKEN' );
		jimport('joomla.filesystem.file');

		$db = $this->getDbo();
		$title = JFactory::getApplication()->input->get('title', '', 'string');

		if ( $title == "" )
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_NO_TITLE'), 'Warning');

			return false;
		}

		$input = JFactory::getApplication()->input;
		$published = $input->getInt('publish');
		$desc = $input->get('desc', '', 'string');

		if ( (substr($desc, 0, 3) == '<p>') AND (substr($desc, -4, 4) == '</p>') )
		{
			// Remove enclosing <p> tags,try translating text, add <p> tags
			$desc = substr($desc, 3, -4);
		}

		$parent = $input->getInt('parent');
		$image = $input->get('catpic');
		$usepace = $input->get('usepace');
		$default_map = $input->get('default_map');

		$db->setQuery("SELECT MAX(ordering) FROM #__jtg_cats");
		$maxordering = $db->loadResult();
		$maxordering++;

		$query = "INSERT INTO #__jtg_cats SET"
		. "\n parent_id='" . $parent . "',"
		. "\n title=" . $db->quote($title) . ","
		. "\n image='" . $image . "',"
		. "\n usepace='" . $usepace . "',"
		. "\n default_map=" . $default_map . ","
		. "\n ordering=" . $maxordering . ","
		. "\n checked_out=0,"
		. "\n description=" . $db->quote(htmlentities($desc)) . ","
		. "\n published='" . $published . "'";

		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * function_description
	 *
	 * @param   unknown_type  $file  param_description
	 *
	 * @return bool true on success
	 */
	function uploadCatImage($file)
	{
		jimport('joomla.filesystem.file');

		if ($file['name'] != "")
		{
			$file['ext'] = JFile::getext($file['name']);
			$config = JtgHelper::getConfig();
			$allowedimages = $config->type;
			$allowedimages = explode(',', $allowedimages);

			if ( !in_array($file['ext'], $allowedimages) )
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_JTG_NOTALLOWED_FILETYPE', $file['ext']), 'Warning');

				return false;
			}

			$upload_dir = JPATH_SITE . "/images/jtrackgallery/cats/";
			$filename = JFile::makeSafe(strtolower($file['name']));

			if (JFile::exists($upload_dir . $filename))
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_CATPIC_ALLREADYEXIST'), 'Warning');

				return false;
			}
			else
			{
				$upload = JFile::upload($file['tmp_name'], $upload_dir . $filename);

				if (!$upload)
				{
					return false;
				}
				else
				{
					return true;
				}
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * function_description
	 *
	 * @return boolean
	 */
	function updateCat()
	{
		$mainframe = JFactory::getApplication();

		// Check the token
		JSession::checkToken() or die( 'JINVALID_TOKEN' );
		jimport('joomla.filesystem.file');

		$db = $this->getDbo();

		$input = JFactory::getApplication()->input;
		$id = $input->getInt('id');
		$file = $input->files->get('image');
		$title = $input->get('title', '', 'string');
		$image = $input->get('catpic');
		$usepace = $input->get('usepace');
		$default_map = $input->get('default_map');

		if ( $title == "" )
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_NO_TITLE'), 'Warning');

			return false;
		}

		$published = $input->getInt('publish');
		$desc = $input->get('desc', '', 'string');

		if ( (substr($desc, 0, 3) == '<p>') AND (substr($desc, -4, 4) == '</p>') )
		{
			// Remove enclosing <p> tags,try translating text, add <p> tags
			$desc = substr($desc, 3, -4);
		}

		$parent = $input->getInt('parent');
		$query = "UPDATE #__jtg_cats SET"
		. "\n parent_id='" . $parent . "',"
		. "\n title=" . $db->quote($title) . ","
		. "\n image='" . $image . "',"
		. "\n usepace='" . $usepace . "',"
		. "\n default_map=" . $default_map . ","
		. "\n description=" . $db->quote(htmlentities($desc)) . ","
		. "\n published='" . $published . "'"
		. "\n WHERE id='" . $id . "'";

		$db->setQuery($query);
		$db->execute();

		return true;
	}
}
