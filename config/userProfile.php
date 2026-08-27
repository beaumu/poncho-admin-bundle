<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Poncho\AdminBundle\Controller\ProfileController;
use Poncho\AdminBundle\Form\ProfileType;

return static function (ContainerConfigurator $configurator): void {

    $services = $configurator->services();

    $services->defaults()
        ->private()
        ->autowire(true)
        ->autoconfigure(false);

    $services->set(ProfileController::class)
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber');

    $services->set(ProfileType::class)
        ->tag('form.type');
};