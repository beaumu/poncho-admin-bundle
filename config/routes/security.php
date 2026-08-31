<?php

namespace Symfony\Component\Routing\Loader\Configurator;

use Poncho\AdminBundle\Controller\SecurityController;

return function (RoutingConfigurator $routes) {

    $routes
        ->add(SecurityController::LOGIN_ROUTE, '/login')
        ->controller([SecurityController::class, 'login']);

    $routes
        ->add(SecurityController::LOGOUT_ROUTE, '/logout')
        ->methods(['GET'])
        ->controller([SecurityController::class, 'logout']);

    $routes
        ->add('poncho_admin_security_passwordresetrequest', '/password-reset')
        ->controller([SecurityController::class, 'passwordResetRequest']);

    $routes
        ->add('poncho_admin_security_passwordresetcheckemail', '/password-reset/check-email')
        ->controller([SecurityController::class, 'passwordRequestCheckEmail']);

    $routes
        ->add('poncho_admin_security_passwordreset', '/password-reset/{token}')
        ->controller([SecurityController::class, 'passwordReset']);
};
