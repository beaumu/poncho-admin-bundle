<?php

namespace Poncho\AdminBundle\Notification;

use Poncho\AdminBundle\Entity\BaseNotification;

interface NotificationProviderInterface
{
    /**
     * @return iterable<BaseNotification>
     */
    public function collect(): iterable;

    public function render(BaseNotification $notification): string;
}
