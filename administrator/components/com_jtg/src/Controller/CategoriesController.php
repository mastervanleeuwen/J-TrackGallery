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

namespace Jtg\Component\Jtg\Administrator\Controller;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * Controller Class Categories
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */
class CategoriesController extends BaseController
{
	/**
	 * View method for JTG
	 *
	 * This function override joomla.application.component.controller
	 * View Cache not yet implemented in JTrackGallery
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types
	 *
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$input = Factory::getApplication()->input;
		switch ($this->getTask()) 
		{
			case 'editcat':
				$input->set('layout', 'editform');
				break;
			case 'newcat':
				$input->set('layout', 'form');
				$input->set('task',	'default');
				break;
			case 'managecatpics':
				$input->set('layout', 'managecatpics');
				break;
			case 'uploadcatpics':
				$input->set('layout', 'managecatpicsform');
				break;
			case 'managecatpics':
				$input->set('layout',	'managecatpics');
				$input->set('task',	'default');
				break;
			case 'newcatpic':
				$input->set('layout',	'managecatpicsform');
				$input->set('task',	'new');
				break;
			case 'editcatpic':
				$input->set('layout',	'managecatpicsform');
				$input->set('task',	'edit');
				break;
		}
		parent::display($cachable, $urlparams);
	}
	/**
	 * function_description
	 *
	 * @return return_description
	 */
	function uploadcatimages ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$success = $model->saveCatImage();

		// Redirect to cats overview
		$link = Route::_("index.php?option=com_jtg&controller=categories&task=managecatpics", false);

		if ($success)
		{
			$this->setRedirect($link, Text::_('COM_JTG_CATPIC_SAVED'));
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_CATPIC_NOTSAVED'), 'Warning');
			$this->setRedirect($link);
		}
	}

	/**
	 * function_description
	 *
	 * @uses JtgModelCat::saveCat
	 * @return redirect
	 */
	function savecat ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$model = $this->getModel();
		$success = $model->saveCat();

		// Redirect to cats overview
		$link = Route::_("index.php?option=com_jtg&task=categories&controller=categories", false);

		if ($success)
		{
			$this->setRedirect($link, Text::_('COM_JTG_CAT_SAVED'));
		}
		else
		{
			$this->setRedirect($link, Text::_('COM_JTG_CAT_NOT_SAVED'));
		}
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderup ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = $this->input->get('cid', array(), 'array');
		$model = $this->getModel();
		$model->move($cid[0], -1);

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=categories&controller=categories', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function orderdown ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = $this->input->get('cid', array(), 'array');
		$model = $this->getModel();
		$model->move($cid[0],1);

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=categories&controller=categories', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function saveorder ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		$order = Factory::getApplication()->input->get('order', array(), 'array');
		ArrayHelper::toInteger($cid);
		ArrayHelper::toInteger($order);

		$model = $this->getModel();
		$model->saveorder($order, $cid);

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=categories&controller=categories', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function publish ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_SELECT_AN_ITEM_TO_PUBLISH'), 'Error');
		}

		$model = $this->getModel();

		if (! $model->publish($cid, 1))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=categories&controller=categories', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function unpublish ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_SELECT_AN_ITEM_TO_UNPUBLISH'), 'Error');
		}

		$model = $this->getModel();

		if (! $model->publish($cid, 0))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=categories&controller=categories', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function removepic ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');

		if (count($cid) < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_SELECT_AN_ITEM_TO_DELETE'), 'Error');
		}

		$model = $this->getModel();

		if (! $model->deleteCatImage($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=categories&controller=categories&task=managecatpics', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function remove ()
	{
		// Check for request forgeries
		Session::checkToken() or jexit(JTEXT::_('JINVALID_TOKEN'));

		$cid = Factory::getApplication()->input->get('cid', array(), 'array');
		ArrayHelper::toInteger($cid);

		if (count($cid) < 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_JTG_SELECT_AN_ITEM_TO_DELETE'), 'Error');
		}

		$model = $this->getModel();

		if (! $model->delete($cid))
		{
			echo "<script> alert('" . $model->getError(true) . "'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect(Route::_('index.php?option=com_jtg&task=categories&controller=categories', false));
	}

	/**
	 * function_description
	 *
	 * @return redirect
	 */
	function updatecat ()
	{
		// Check the token
		Session::checkToken() or die('JINVALID_TOKEN');

		$model = $this->getModel();
		$success = $model->updateCat();

		// Redirect to cats overview
		$link = Route::_("index.php?option=com_jtg&task=categories&controller=categories", false);

		if ($success)
		{
			$this->setRedirect($link, Text::_('COM_JTG_CAT_SAVED'));
		}
		else
		{
			$this->setRedirect($link, Text::_('COM_JTG_CAT_NOT_SAVED'));
		}
	}
}
