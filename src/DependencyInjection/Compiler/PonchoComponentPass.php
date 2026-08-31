<?php

namespace Poncho\AdminBundle\DependencyInjection\Compiler;

use Poncho\AdminBundle\Lib\DataTable\DataTableRegistry;
use Poncho\AdminBundle\Lib\Menu\MenuRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class PonchoComponentPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container): void
    {
        $registry = $container->getDefinition(DataTableRegistry::class);
        $this->addToRegistry($container, $registry, DataTableRegistry::TAG_TYPE, 'registerType');
        $this->addToRegistry($container, $registry, DataTableRegistry::TAG_COLUMN_TYPE, 'registerColumnType');
        $this->addToRegistry($container, $registry, DataTableRegistry::TAG_ACTION_TYPE, 'registerActionType');
        $this->addToRegistry($container, $registry, DataTableRegistry::TAG_ADAPTER_TYPE, 'registerAdapterType');

        $registry = $container->getDefinition(MenuRegistry::class);
        $this->addToRegistry($container, $registry, MenuRegistry::TAG_TYPE, 'registerType');
        $this->addToRegistry($container, $registry, MenuRegistry::TAG_VISITOR, 'registerVisitor');
    }

    private function addToRegistry(ContainerBuilder $container, Definition $registry, string $tag, string $method): void
    {
        $taggedServices = $container->findTaggedServiceIds($tag);

        foreach ($taggedServices as $id => $tags) {
            $registry->addMethodCall($method, [$id, new Reference($id)]);
        }
    }
}
