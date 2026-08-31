<?php

namespace Symfony\Component\Routing\Loader\Configurator;

use Poncho\AdminBundle\Controller\ProfileController;

return function (RoutingConfigurator $routes) {

    $routes
        ->add('poncho_admin_profile_index', '/profile')
        ->controller([ProfileController::class, 'index']);
};
