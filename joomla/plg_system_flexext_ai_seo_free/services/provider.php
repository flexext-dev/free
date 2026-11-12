<?php
/**
 * @package     FlexExt
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Visacom\Plugin\System\FlexextAiSeoFree\Extension\FlexextAiSeoFree;

if (!class_exists(FlexextAiSeoFree::class, false)) {
    require_once dirname(__DIR__) . '/src/Extension/FlexextAiSeoFree.php';
}

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new FlexextAiSeoFree(
                    (array) PluginHelper::getPlugin('system', 'flexext_ai_seo_free')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
