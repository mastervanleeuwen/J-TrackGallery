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
 *
 */

namespace Jtg\Component\Jtg\Administrator\View\Categories;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;
use Jtg\Component\Jtg\Administrator\View\JtgView;

/**
 * JtgViewCats class for the jtg component
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @since       0.8
 */

class HtmlView extends JtgView
{
	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return void
	 */
	public function display($tpl = null)
	{	
		switch ($this->getLayout())
		{
			case 'form':
				$this->_displayForm($tpl);
				break;

			case 'editform':
				$this->_displayEditcat($tpl);
				break;

			case 'default':
				$this->_displayDefault($tpl);
				break;

			case 'managecatpics':
			case 'managecatpicsform':
				$this->_displayManageCatPics($tpl);
				break;
		}
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	function _displayManageCatPics($tpl)
	{
		$model = $this->getModel();
		$rows = $this->get('Pics');
		$children = array();
		$imagedir = JPATH_SITE . '/images/jtrackgallery/cats/';
		$imageurl = Uri::root() . 'images/jtrackgallery/cats/';
		$images = array();

		foreach ($rows as $k => $v )
		{
			$new = new \stdClass;
			$new->id = $v;
			$new->file = $v;
			$new->pathfile = $imagedir . $v;
			$new->name = File::stripext($v);
			$new->ext = File::getext($v);
			$new->checked_out = 0;
			$new->image = " <image src='" . $imageurl . $v . "' title='" . $v . "' alt='" . $v . "' />";
			$images[$k] = $new;
		}

		$config = JtgHelper::getConfig();
		$types = $config->type;
		$this->types = $types;
		$this->rows = $images;

		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	function _displayDefault($tpl)
	{
		$app = Factory::getApplication();
		$option = $app->input->get('option');
		$model = $this->getModel();

		$filter_order		= $app->getUserStateFromRequest(
				$option . "filter_order",
				'filter_order',
				'ordering',
				'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest(
				$option . "filter_order_Dir",
				'filter_order_Dir',
				'',
				'word' );

		$lists['order']		= $filter_order;
		$lists['order_Dir']	= $filter_order_Dir;

		$rows = $this->get('Data');
		$children = array();
		$image = array();
		$imagedir = Uri::root() . 'images/jtrackgallery/cats/';

		foreach ($rows as $v )
		{
			$v->title = Text::_($v->title);

			// TODO  unnecessary
			$v->name = $v->title;
			$pt	= $v->parent_id;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;

			if ($v->image)
			{
				$image[$v->id] = " <image src='" . $imagedir . $v->image . "' title='" . Text::_($v->title) . "' alt='" . Text::_($v->title) . "' />";
			}
		}

		$levellimit = 50;
		$list = HTMLHelper::_('menu.treerecurse', 0, '', array(), $children, max(0, $levellimit - 1));
		$list = array_slice($list, 0, 999);

		$total		= $this->get('Total');
		$pagination = $this->get('Pagination');
		$this->lists = $lists;
		$this->pagination = $pagination;
		$this->list = $list;
		$this->catpic = $image;
		$this->rows = $rows;

		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	protected function _displayForm($tpl)
	{
		$model = $this->getModel();
		$parent = $model->getParent();
		$nullcat = array('id' => 0, "name" => Text::_('JNONE'), "title" => Text::_('JNONE'));
		array_unshift($parent, $nullcat);

		$default_map = $model->getDefaultMaps();
		array_unshift($default_map, array('id' => 0, "name" => Text::_('JNONE')) );

      if (version_compare(JVERSION,'4.0','ge')) {
         $editor = Factory::getApplication()->getConfig()->get('editor');
      }
      else 
      {
         $editor = Factory::getConfig()->get('editor');
      }
      $editor = Editor::getInstance($editor);;

		$lists['block'] 	= HTMLHelper::_('select.booleanlist', 'publish', null, 1);
		$lists['usepace'] 	= HTMLHelper::_('select.booleanlist', 'usepace', null, false);
		$lists['parent'] 	= HTMLHelper::_('select.genericlist', $parent, 'parent', 'size="1"', 'id', 'name', '');
		$lists['default_map'] 	= HTMLHelper::_('select.genericlist', $default_map, 'default_map', 'size="1"', 'id', 'name', '');

		$config = JtgHelper::getConfig();
		$images = $model->getPics();

		$this->images = $images;
		$this->lists = $lists;
		$this->editor = $editor;
		$this->max_images = $config->max_images;
		$this->maxsize = $config->max_size;

		parent::display($tpl);
	}

	/**
	 * function_description
	 *
	 * @param   object  $tpl  template
	 *
	 * @return return_description
	 */
	function _displayEditcat($tpl)
	{
		$app = Factory::getApplication();
		$cid = $app->input->get('cid', array(), 'array');
		$id = $cid[0];

      if (version_compare(JVERSION,'4.0','ge')) {
         $editor = $app->getConfig()->get('editor');
      }
      else 
      {
         $editor = Factory::getConfig()->get('editor');
      }
      $editor = Editor::getInstance($editor);;

		$model = $this->getModel();
		$parent = $model->getParent($id);
		$nullcat = array('id' => 0, "name" => Text::_('JNONE'), "title" => Text::_('JNONE'));
		array_unshift($parent, $nullcat);

		$default_map=$model->getDefaultMaps();
		array_unshift($default_map, array('id' => 'null', "name" => Text::_('JNONE')) );

		$data = $model->getCat($id);

		$lists['block'] 	= HTMLHelper::_('select.booleanlist', 'publish', 'class="inputbox" size="1"', $data->published);
		$lists['usepace'] 	= HTMLHelper::_('select.booleanlist', 'usepace', 'class="inputbox" size="1"', $data->usepace);
		$lists['parent'] 	= HTMLHelper::_('select.genericlist', $parent, 'parent', 'size="1"', 'id', 'name', $data->parent_id);
		$lists['default_map'] 	= HTMLHelper::_('select.genericlist', $default_map, 'default_map', 'size="1"', 'id', 'name', $data->default_map);
		$config = JtgHelper::getConfig();
		$images = $model->getPics();
		$this->images = $images;
		$this->lists = $lists;
		$this->editor = $editor;
		$this->maxsize = $config->max_size;
		$this->data = $data;

		parent::display($tpl);
	}
}
