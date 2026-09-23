# Menu

The admin sidebar and its breadcrumb are both built from one menu class.

## The admin menu

Extend `BaseAdminMenu` and describe the tree in `buildMenu()`:

```php
namespace App\Menu;

use Poncho\AdminBundle\Lib\Menu\Builder\MenuBuilder;
use Poncho\AdminBundle\Menu\BaseAdminMenu;

class AdminMenu extends BaseAdminMenu
{
    public function buildMenu(MenuBuilder $builder, array $options): void
    {
        $root = $builder->root();

        $root->add('dashboard')
            ->icon('mdi mdi-view-dashboard')
            ->route('app_admin_home');

        $root->add('missions')
            ->icon('mdi mdi-rocket')
            ->add('all')
                ->label('All missions')
                ->route('app_admin_mission_index')
                ->end()
            ->add('failed')
                ->route('app_admin_mission_index', ['status' => 'failed'])
                ->badge('3', 'bg-danger')
                ->end();

        $root->add('documentation')
            ->icon('mdi mdi-book-open-variant')
            ->url('https://example.com/docs')
            ->target('_blank');
    }
}
```

and register it:

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    menu: App\Menu\AdminMenu
```

`make:admin:home` generates both for you.

## Building the tree

`$builder->root()` returns the root item. `add(string $id)` creates a child and returns **the child**,
so calls chain downwards; `end()` climbs back to the parent.

```php
$root->add('a')          // → a
    ->add('a1')          // → a1, child of a
        ->end()          // → a
    ->add('a2')          // → a2, child of a
        ->end();         // → a
```

Ids must be unique among siblings. An item without `label()` is labelled from its id, humanized:
`add('failed_missions')` shows *Failed missions*.

| Method | |
| --- | --- |
| `add(string $id)` | Add a child and return it |
| `get(string $id)`, `has(string $id)` | Access a child built earlier |
| `end()` | Return to the parent |
| `label(string $label)` | Text. Translated with the item's domain |
| `translationDomain(?string $domain)` | Default `messages`. `null` disables translation |
| `icon(?string $icon)` | CSS classes of an `<i>`, e.g. `mdi mdi-rocket` |
| `route(string $route, array $params = [])` | Link target, and a route that makes the item current |
| `url(?string $url)` | Link target, for external links |
| `target(?string $target)` | e.g. `_blank` |
| `badge(string $label, ?string $class = null)` | A badge next to the label |
| `matchRoute(string $route, array $params = [])` | An extra route that makes the item current |
| `show(bool $show = true)` | Visibility |
| `current(bool $current = true)` | Force this item to be the current one |

## Hiding items

Visibility is set with `show()`. There is no built-in role check, so inject what you need:

```php
use Poncho\AdminBundle\PonchoAdminConfiguration;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class AdminMenu extends BaseAdminMenu
{
    public function __construct(Environment $twig, PonchoAdminConfiguration $configuration, private readonly Security $security)
    {
        parent::__construct($twig, $configuration);
    }

    public function buildMenu(MenuBuilder $builder, array $options): void
    {
        $builder->root()
            ->add('users')
                ->icon('mdi mdi-account-group')
                ->route('poncho_admin_user_index')
                ->show($this->security->isGranted('ROLE_ADMIN'));
    }
}
```

A parent whose children are **all** hidden is hidden too, so an empty section never shows.

!> Hiding an item only removes it from the menu. It does not protect the route — do that with
`access_control` or `#[IsGranted]`.

## Next

- [Rendering and customising the admin menu](component/menu/admin_menu)
- [How the current item is found](component/menu/current_strategy)
- [Menus other than the admin sidebar](component/menu/custom_menu)
