<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Poncho\AdminBundle\Command\CreateAdminUserCommand;
use Poncho\AdminBundle\Controller\SecurityController;
use Poncho\AdminBundle\Controller\UserController;
use Poncho\AdminBundle\DataTable\UserTableType;
use Poncho\AdminBundle\Form\ChangePasswordType;
use Poncho\AdminBundle\Form\UserType;
use Poncho\AdminBundle\Security\UserChecker;
use Poncho\AdminBundle\Service\UserManager;

return static function (ContainerConfigurator $configurator): void {

    $services = $configurator->services();

    $services->defaults()
        ->private()
        ->autowire(true)
        ->autoconfigure(false);

    $services->set(CreateAdminUserCommand::class)
        ->tag('console.command');

    $services->set(SecurityController::class)
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber');
    $services->set(UserController::class)
        ->tag('controller.service_arguments')
        ->tag('container.service_subscriber');

    $services->set(UserChecker::class);
    $services->set(UserTableType::class)
        ->tag('poncho.datatable.type');

    $services->set(UserManager::class);

    $services->set(ChangePasswordType::class)
        ->tag('form.type');
    $services->set(UserType::class)
        ->tag('form.type');

};