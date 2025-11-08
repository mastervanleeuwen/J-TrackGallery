<?php

/**
 * @component  J!Track Gallery (jtg) for Joomla! 3.x and 4.x
 *
 *
 * @package     Comjtg
 * @subpackage  Frontend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @copyright   2023 J!TrackGallery team
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 *
 */

namespace Jtg\Component\Jtg\Site\View;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Uri\Uri;

use Jtg\Component\Jtg\Site\Helpers\JtgHelper;

class JtgView extends HtmlView
{
    public function __construct($config = [])
    {
        Factory::getLanguage()->load('com_jtg');
        Factory::getLanguage()->load('com_jtg_common');

        // Com_jtg_additional language files are in /images/jtrackgallery/language
        // folder
        Factory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery', 'en-GB',
		true);
        Factory::getLanguage()->load('com_jtg_additional', JPATH_SITE . '/images/jtrackgallery', null, true);    
        
        parent::__construct($config);
    }


    public function display($tpl = null)
    {
        $cfg = JtgHelper::getConfig();

        $app = Factory::getApplication();

        // Set the template
        $tmpl = strlen($cfg->template) ? $cfg->template : 'default';

        $document = Factory::getDocument();
        $document->addStyleSheet(
		    Uri::base() . '/components/com_jtg/assets/template/' . $tmpl . '/jtg_style.css'
);
        $template_css = 'templates/' . $app->getTemplate() . '/css/jtg_style.css';

        if (File::exists($template_css))
        {
	        // Override with site template
	        $document->addStyleSheet($template_css);
        }

        return parent::display($tpl);
    }
}
