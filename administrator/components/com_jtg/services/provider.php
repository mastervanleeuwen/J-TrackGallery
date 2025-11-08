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

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

use Jtg\Component\Jtg\Administrator\Extension\JtgComponent;

return new class implements ServiceProviderInterface {
    
    public function register(Container $container): void {
        $container->registerServiceProvider(new MVCFactory('\\Jtg\\Component\\Jtg'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Jtg\\Component\\Jtg'));
        $container->registerServiceProvider(new RouterFactory('\\Jtg\\Component\\Jtg'));
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new JtgComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));
                return $component;
            }
        );
    }
};
