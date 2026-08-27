<?php

namespace Poncho\AdminBundle\Controller;

use Poncho\AdminBundle\Lib\Controller\AdminController;
use Poncho\AdminBundle\Notification\NotificationProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class NotificationController extends AdminController
{
    public function list(NotificationProviderInterface $provider): Response
    {
        $notifications = $provider->collect();

        if (0 === \count($notifications)) {
            return new JsonResponse([
                'count' => 0,
                'html' => $this->renderView('@PonchoAdmin/notification/empty.html.twig')
            ]);
        }

        $notificationData = [];
        foreach ($notifications as $notification) {
            $notificationData[] = [
                'html' => $provider->render($notification)
            ];
        }

        return new JsonResponse([
            'count' => \count($notifications),
            'notifications' => $notificationData
        ]);
    }
}
