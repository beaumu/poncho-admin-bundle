<?php

namespace Symfony\Component\Routing\Loader\Configurator;

use Poncho\AdminBundle\Controller\UserController;

return function (RoutingConfigurator $routes) {

    $routes
        ->add('poncho_admin_user_index', '/user')
        ->controller([UserController::class, 'index']);

    $routes
        ->add('poncho_admin_user_edit', '/user/edit/{id}')
        ->requirements([
            'id' => '\d+'
        ])
        ->defaults([
            'id' => null
        ])
        ->controller([UserController::class, 'edit']);

    $routes
        ->add('poncho_admin_user_delete', '/user/delete/{id}')
        ->requirements([
            'id' => '\d+'
        ])
        ->controller([UserController::class, 'delete']);
};
