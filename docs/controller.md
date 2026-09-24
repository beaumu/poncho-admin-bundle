# AdminController

`Poncho\AdminBundle\Lib\Controller\AdminController` extends Symfony's `AbstractController` and adds
the helpers the rest of the bundle is built on. Extending it is optional — every service it wraps
can be injected directly — but it is the shortest path, and it is what the makers generate.

```php
namespace App\Controller\Admin;

use Poncho\AdminBundle\Lib\Controller\AdminController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class HomeController extends AdminController
{
    #[Route('')]
    public function index(): Response
    {
        return $this->render('admin/home/index.html.twig');
    }
}
```

## DataTable

| Method | Returns | |
| --- | --- | --- |
| `createTable(string $type, array $options = [])` | `DataTable` | Builds a table from a `DataTableType` class |
| `createTableBuilder(array $options = [])` | `DataTableBuilder` | An ad-hoc table without a dedicated type class |

```php
$table = $this->createTable(MissionTableType::class, ['page_length' => 50]);
```

`createTableBuilder()` is handy for a one-off table defined inline. Call `getTable()` once you are
done adding columns and an adapter:

```php
$table = $this->createTableBuilder()
    ->add('name')
    ->useEntityAdapter(Mission::class)
    ->getTable();
```

See [DataTable](component/datatable/index).

## JsResponse

| Method | Returns |
| --- | --- |
| `js()` | a new `JsResponse` |

```php
return $this->js()
    ->closeModal()
    ->reloadTable()
    ->toastSuccess('Mission saved');
```

See [JsResponse](component/jsresponse/index).

## Doctrine

| Method | |
| --- | --- |
| `em(?string $name = null): EntityManagerInterface` | The default or a named entity manager |
| `getRepository(string $className, ?string $managerName = null)` | Shortcut for `$this->em()->getRepository()` |
| `persistAndFlush(object $entity, ?string $managerName = null): void` | `persist()` then `flush()` |
| `removeAndFlush(object $entity, ?string $managerName = null): void` | `remove()` then `flush()` |
| `findOrNotFound(string $className, mixed $id, ?string $managerName = null): object` | `find()`, or a 404 when nothing matches |

```php
$mission = $this->findOrNotFound(Mission::class, $id);
$mission->name = 'Apollo 11';
$this->persistAndFlush($mission);
```

## Toasts

Toasts queued from a controller are stored as flash messages in the `toast` bag
(`AdminController::BAG_TOAST`) and displayed on the next page render.

| Method |
| --- |
| `toast(string $type, TranslatableMessage\|string $text, TranslatableMessage\|string\|null $title = null)` |
| `toastInfo($text, $title = null)` |
| `toastSuccess($text, $title = null)` |
| `toastWarning($text, $title = null)` |
| `toastError($text, $title = null)` |

`$type` is one of `info`, `success`, `warning`, `error`. Pass a `TranslatableMessage` to have it
translated:

```php
use function Symfony\Component\Translation\t;

$this->toastSuccess(t('message.item_updated', [], 'PonchoAdmin'));
```

These are for full page loads. Inside an XHR response use the `JsResponse` equivalents instead —
`$this->js()->toastSuccess(…)` — which display immediately.

!> **Toast text is rendered as HTML, unescaped.** Never interpolate user-controlled data into it
without escaping it first. See [Security](security).

`toastSuccess()` and `toastWarning()` accept a third `bool $safeHtml` argument. It is currently
ignored.

## Translation and errors

| Method | |
| --- | --- |
| `trans(?string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string` | Shortcut for the translator |
| `throwNotFoundExceptionIfNull(mixed $target, string $message = 'Not Found'): void` | Throws a 404 when `$target === null` |
| `throwAccessDeniedExceptionIfFalse(mixed $target, string $message = ''): void` | Throws a 403 when `$target === false` |

```php
$this->throwAccessDeniedExceptionIfFalse($this->isGranted('ROLE_EDITOR'));
```

## Services it subscribes to

`AdminController` adds these to `AbstractController::getSubscribedServices()`, so they resolve
from the service locator without constructor injection:

- `Poncho\AdminBundle\Lib\DataTable\DataTableFactory`
- `Poncho\AdminBundle\Lib\JsResponse\JsResponseFactory`
- `doctrine` (`ManagerRegistry`)
- `translator` (`TranslatorInterface`)

When you override `getSubscribedServices()` yourself, merge with `parent::getSubscribedServices()`
or these helpers stop working.
