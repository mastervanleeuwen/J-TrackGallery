<?php

/**
 * @component  J!Track Gallery (jtg) for Joomla! 4.0 and above
 *
 *
 * @package     Comjtg
 * @subpackage  Backend
 * @author      Marco van Leeuwen <mastervanleeuwen@gmail.com>
 * @copyright   2025 J!TrackGallery teams
 *
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @link        https://mastervanleeuwen.github.io/J-TrackGallery/
 * 
 */

namespace Jtg\Component\Jtg\Administrator\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Factory;

\defined('_JEXEC') or die;

class JtgComponent extends MVCComponent implements RouterServiceInterface
{
	use RouterServiceTrait;
}
