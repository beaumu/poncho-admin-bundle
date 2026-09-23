<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

return static function (ContainerConfigurator $container): void {
    // "with_constructor_extractor" was added in Symfony 7.3, where leaving it
    // unset triggers a deprecation because its default flips in 8.0. Older
    // versions reject the key outright, so only set it where it is understood.
    if (Kernel::VERSION_ID >= 70300) {
        $container->extension('framework', [
            'property_info' => ['with_constructor_extractor' => true],
        ]);
    }
};
