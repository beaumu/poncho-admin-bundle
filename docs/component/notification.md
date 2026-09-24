# Notifications

A bell in the admin top bar opens a dropdown listing recent notifications, refreshed while it is
open. The bundle provides the widget, the endpoint and a base entity; **you** decide what a
notification is and who sees it.

## Setting it up

```bash
php bin/console make:admin:notification
```

generates a notification entity and repository, and a provider, then:

- imports `@PonchoAdminBundle/config/routes/notification.php` in `config/routes.yaml`, under `/admin`,
- sets `poncho_admin.notification.provider` and `poll_interval` in `config/packages/poncho_admin.yaml`.

Update the schema, then create one:

```php
$notification = new AdminNotification();
$notification->title = 'Launch complete';
$notification->text = 'Apollo 11 reached orbit.';
$notification->url = $this->generateUrl('mission_show', ['id' => 11]);
$notification->successIcon();

$this->persistAndFlush($notification);
```

### By hand

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    notification:
        provider: App\Notification\AdminNotificationProvider
        poll_interval: 10
```

```yaml
# config/routes.yaml
poncho_admin_notification_:
    resource: '@PonchoAdminBundle/config/routes/notification.php'
    prefix: /admin
```

!> The route import is required. The widget links to `poncho_admin_notification_list`, so with
notifications enabled but the routes not imported, **every admin page fails to render**.

Setting any key under `notification` enables it; `enabled: false` turns it off. With notifications
enabled and no `provider`, the container fails to build.

| Option | Default | |
| --- | --- | --- |
| `provider` | `null` | Service id of your provider |
| `poll_interval` | `10` | Seconds between refreshes while the dropdown is open. `0` or `false`: refresh only on open |

## The provider

Implement `NotificationProviderInterface`:

```php
interface NotificationProviderInterface
{
    /** @return iterable<BaseNotification> */
    public function collect(): iterable;

    public function render(BaseNotification $notification): string;
}
```

`collect()` returns the notifications to show — typically the latest few for the current user.
`render()` turns one into HTML.

Extending `BaseNotificationProvider` gives you `render()` for free, with the bundle's template
(icon, title, text, link, relative date — *5 minutes ago*):

```php
namespace App\Notification;

use App\Entity\AdminNotification;
use Doctrine\ORM\EntityManagerInterface;
use Poncho\AdminBundle\Notification\BaseNotificationProvider;
use Symfony\Bundle\SecurityBundle\Security;

class AdminNotificationProvider extends BaseNotificationProvider
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Security $security,
    ) {
    }

    public function collect(): iterable
    {
        return $this->em->createQueryBuilder()
            ->select('n')
            ->from(AdminNotification::class, 'n')
            ->innerJoin('n.users', 'u')
            ->andWhere('u = :user')
            ->setParameter('user', $this->security->getUser())
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
```

The generated entity has a commented-out `users` relation for exactly this.

!> `collect()` must return something **countable** — an array or a Doctrine collection. The endpoint
calls `count()` on it, so a generator (`yield`) fails with a `TypeError`, even though the interface
only promises `iterable`.

`BaseNotificationProvider` receives Twig and KnpTimeBundle's `DateTimeFormatter` through setters,
injected by a compiler pass. Implement the interface directly and you render however you like.

## BaseNotification

A Doctrine mapped superclass. Your entity extends it.

| Property | Type | |
| --- | --- | --- |
| `id` | `?int` | |
| `createdAt` | `DateTimeInterface` | Set to now on construction |
| `title` | `?string` | |
| `text` | `?string` | |
| `url` | `?string` | Makes the notification a link |
| `icon` | `?string` | CSS classes of the icon |
| `iconColor` | `?string` | A Bootstrap colour: `primary`, `success`, `danger`, … |

Shortcuts that set `icon` and `iconColor`:

| Method | Icon | Colour |
| --- | --- | --- |
| `waitingIcon()` | clock | `secondary` |
| `runningIcon()` | spinner | `primary` |
| `successIcon()` | check | `success` |
| `errorIcon()` | alert | `danger` |

## What it does not do

The widget is a **recent-activity list**. There is no unread count on the bell, no read/unread
state and no "show all" page — the template has placeholders for them, commented out. Build those
on your entity and provider if you need them.

## The endpoint

`GET /admin/notification/list` (with the prefix above) returns:

```json
{"count": 2, "notifications": [{"html": "…"}, {"html": "…"}]}
```

or, with nothing to show, `{"count": 0, "html": "…empty state…"}`.

The widget requests it when the dropdown opens, then every `poll_interval` seconds while it stays
open.
