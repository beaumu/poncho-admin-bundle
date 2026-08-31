<?php

namespace Symfony\Component\Routing\Loader\Configurator;

use Poncho\AdminBundle\Controller\NotificationController;

return function (RoutingConfigurator $routes) {
    $routes
        ->add('poncho_admin_notification_list', '/notification/list')
        ->controller([NotificationController::class, 'list']);
};
