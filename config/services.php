<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Poncho\AdminBundle\Asset\AssetPackage;
use Poncho\AdminBundle\Maker\MakeAdminSecurity;
use Poncho\AdminBundle\Maker\MakeHome;
use Poncho\AdminBundle\Maker\MakeNotification;
use Poncho\AdminBundle\Maker\MakeTable;
use Poncho\AdminBundle\Maker\MakeTree;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;
use Poncho\AdminBundle\Menu\BaseAdminMenu;
use Poncho\AdminBundle\Security\AuthenticationEntryPoint;
use Poncho\AdminBundle\Twig\PonchoAdminTwigExtension;
use Poncho\AdminBundle\PonchoAdminConfiguration;

return static function (ContainerConfigurator $configurator): void {

    $services = $configurator->services();

    $services->defaults()
        ->private()
        ->autowire(true)
        ->autoconfigure(false);

    // Asset
    $services->set(AssetPackage::class)
        ->tag('assets.package', ['package' => AssetPackage::PACKAGE_NAME]);

    // Security
    $services->set(AuthenticationEntryPoint::class);

    // Menu
    $services->set(BaseAdminMenu::class)
        ->tag('poncho.menu.type');

    // Admin
    $services->set(PonchoAdminTwigExtension::class)
        ->arg(0, service('twig.form.renderer'))
        ->tag('twig.extension');
    $services->set(PonchoAdminConfiguration::class)
        ->bind('$logoutUrlGenerator', service('security.logout_url_generator'));

    // Maker
    $services->set(MakeHelper::class)
        ->arg(1, param('kernel.project_dir'));
    $services->set(MakeTable::class)
        ->tag('maker.command');
    $services->set(MakeTree::class)
        ->tag('maker.command');
    $services->set(MakeAdminSecurity::class)
        ->tag('maker.command');
    $services->set(MakeNotification::class)
        ->tag('maker.command');
    $services->set(MakeHome::class)
        ->tag('maker.command');
};