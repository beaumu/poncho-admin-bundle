<?php

namespace Poncho\AdminBundle;

use Poncho\AdminBundle\DependencyInjection\Compiler\PonchoComponentPass;
use Poncho\AdminBundle\DependencyInjection\Compiler\PonchoNotificationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PonchoAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new PonchoComponentPass());
        $container->addCompilerPass(new PonchoNotificationPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
