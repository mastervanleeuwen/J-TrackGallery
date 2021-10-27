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
 */


// No direct access
defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;

jimport('joomla.application.component.controller');
/**
 * JtgControllerMaps class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class JtgControllerMaps extends JtgController
{
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function bak__construct()
	{
		parent::__construct();
		$mainframe = JFactory::getApplication();
		$where = array();

		// Get the pagination request variables

		/*
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
		*/

		// In case limit has been changed, adjust limitstart accordingly

		/*
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$limitstart = JFactory::getApplication()->input->get('limitstart',0);
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		*/
		$filter_state = $mainframe->getUserStateFromRequest($this->option . 'filter_state', 'filter_state', '', 'word');

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'a.published = 1';
			}
			elseif ($filter_state == 'U' )
			{
				$where[] = 'a.published = 0';
			}
		}

		$where = ' WHERE ' . implode(' AND ', $where);
		$search = JFactory::getApplication()->input->get('search', true);
		$layout = JFactory::getApplication()->input->get('layout', true);
		$task = JFactory::getApplication()->input->get('task', true);
		$controller = JFactory::getApplication()->input->get('controller', true);
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderup()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');
		$model->move(-1);

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderdown()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');
		$model->move(1);

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function saveorder()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid 	= JFactory::getApplication()->input->get('cid', array(), 'array');
		$order 	= JFactory::getApplication()->input->get('order', array(), 'array');
		ArrayHelper::toInteger($cid);
		ArrayHelper::toInteger($order);

		$model = $this->getModel('map');
		$model->saveorder($order, $cid);

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function publish()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_PUBLISH'), 'Error');
		}

		$model = $this->getModel('maps');

		if (!$model->publish($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function unpublish()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('maps');

		if (!$model->publish($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function remove()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = JFactory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_JTG_SELECT_AN_ITEM_TO_DELETE'), 'Error');
		}

		$model = $this->getModel('maps');

		if (!$model->delete($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function savemap()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');
		$savemap = $model->saveMap();

		if (!$savemap)
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function savemaps()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');

		if (!$model->saveMaps())
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function updatemap()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');

		if (!$model->updateMap())
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(JRoute::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}
}
