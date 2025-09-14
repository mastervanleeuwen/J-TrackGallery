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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
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
		$app = Factory::getApplication();
		$where = array();

		$filter_state = $app->getUserStateFromRequest($this->option . 'filter_state', 'filter_state', '', 'word');

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
		$search = Factory::getApplication()->input->get('search', true);
		$layout = Factory::getApplication()->input->get('layout', true);
		$task = Factory::getApplication()->input->get('task', true);
		$controller = Factory::getApplication()->input->get('controller', true);
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderup()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');
		$model->move(-1);

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderdown()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');
		$model->move(1);

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function saveorder()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid 	= Factory::getApplication()->input->get('cid', array(), 'array');
		$order 	= Factory::getApplication()->input->get('order', array(), 'array');
		ArrayHelper::toInteger($cid);
		ArrayHelper::toInteger($order);

		$model = $this->getModel('map');
		$model->saveorder($order, $cid);

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function publish()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_SELECT_AN_ITEM_TO_PUBLISH'), 'Error');
		}

		$model = $this->getModel('maps');

		if (!$model->publish($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function unpublish()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel('maps');

		if (!$model->publish($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function remove()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_SELECT_AN_ITEM_TO_DELETE'), 'Error');
		}

		$model = $this->getModel('maps');

		if (!$model->delete($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function savemap()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');
		$savemap = $model->saveMap();

		if (!$savemap)
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function savemaps()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');

		if (!$model->saveMaps())
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}

	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function updatemap()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel('maps');

		if (!$model->updateMap())
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=maps&controller=maps', false));
	}
}
