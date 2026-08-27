<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Poncho\AdminBundle\Command\IndexEntityCommand;
use Poncho\AdminBundle\Lib\DataTable\ActionRenderer;
use Poncho\AdminBundle\Lib\DataTable\DataTableFactory;
use Poncho\AdminBundle\Lib\DataTable\DataTableRegistry;
use Poncho\AdminBundle\Lib\DataTable\DataTableRenderer;
use Poncho\AdminBundle\Lib\DataTable\DataTableType;
use Poncho\AdminBundle\Lib\DataTable\Twig\DataTableExtension;
use Poncho\AdminBundle\Lib\Form\Extension\AutoCompleteExtension;
use Poncho\AdminBundle\Lib\Form\Extension\FormTypeExtension;
use Poncho\AdminBundle\Lib\JsResponse\JsResponseFactory;
use Poncho\AdminBundle\Lib\Menu\MenuProvider;
use Poncho\AdminBundle\Lib\Menu\MenuRegistry;
use Poncho\AdminBundle\Lib\Menu\Twig\MenuExtension;
use Poncho\AdminBundle\Lib\Menu\Visitor\MenuCurrentVisitor;
use Poncho\AdminBundle\Lib\Menu\Visitor\MenuVisibilityVisitor;

return static function (ContainerConfigurator $configurator): void {

    $services = $configurator->services();

    $services->defaults()
        ->private()
        ->autowire(true)
        ->autoconfigure(false);

    // -- Menu -- //
    $services->set(MenuRegistry::class);
    $services->set(MenuProvider::class);
    $services->set(MenuVisibilityVisitor::class)
        ->tag('poncho.menu.visitor');
    $services->set(MenuCurrentVisitor::class)
        ->tag('poncho.menu.visitor');
    $services->set(MenuExtension::class)
        ->tag('twig.extension');

    // -- Js Response -- //
    $services->set(JsResponseFactory::class);

    // -- DataTable -- //
    $services->set(DataTableFactory::class);
    $services->set(DataTableRegistry::class);
    $services->set(DataTableRenderer::class);
    $services->set(ActionRenderer::class);
    $services->set(DataTableType::class)
        ->tag(DataTableRegistry::TAG_TYPE);

    $services->set(DataTableExtension::class)
        ->tag('twig.extension');

    $services->load('Poncho\\AdminBundle\\Lib\\DataTable\\Adapter\\', '../src/Lib/DataTable/Adapter/*')
        ->tag(DataTableRegistry::TAG_ADAPTER_TYPE);

    $services->load('Poncho\\AdminBundle\\Lib\\DataTable\\Column\\', '../src/Lib/DataTable/Column/*')
        ->tag(DataTableRegistry::TAG_COLUMN_TYPE);

    $services->load('Poncho\\AdminBundle\\Lib\\DataTable\\Action\\', '../src/Lib/DataTable/Action/*')
        ->tag(DataTableRegistry::TAG_ACTION_TYPE);

    // -- Form -- //
    $services->load('Poncho\\AdminBundle\\Lib\\Form\\', '../src/Lib/Form/*')
        ->tag('form.type');

    $services->set(FormTypeExtension::class)
        ->tag('form.type_extension');
    $services->set(AutoCompleteExtension::class)
        ->tag('form.type_extension');
};
