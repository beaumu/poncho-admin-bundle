# Extending: menus

Two things can be built: **menu types**, which define and render a menu, and **visitors**, which
change a menu after it is built.

For a menu type — a footer menu, a tab bar — see [Other menus](component/menu/custom_menu). To adapt
the admin sidebar, see [The admin menu](component/menu/admin_menu). This page covers changing a
menu after it is built: badges from a count, visibility from data, items added at runtime.

## How a menu is built

Each time a menu is requested — by the layout, `get_menu()`, `render_menu()` or
`get_breadcrumb()` — `MenuProvider` does this:

1. calls your type's `buildMenu()` with a fresh `MenuBuilder`;
2. runs the visitors, in order:
   - `MenuVisibilityVisitor` hides a parent whose children are all hidden,
   - `MenuCurrentVisitor` finds the current item from the request and marks it and its ancestors
     active;
3. hands the `Menu` to whoever asked — `renderMenu()`, the breadcrumb, the page title.

Anything that only needs the builder belongs in `buildMenu()`: badges from a count, `show()` from
`isGranted()`. What follows is for changes that need the **finished** menu — typically the current
item.

## Option 1: in `renderMenu()`

Override `renderMenu()` on your menu type. It receives the finished menu:

```php
class AdminMenu extends BaseAdminMenu
{
    public function renderMenu(Menu $menu, array $options): string
    {
        $current = $menu->getCurrent();
        if (null !== $current && $current->getParent()?->getName() === 'missions') {
            $current->setBadge('editing', 'bg-warning');
        }

        return parent::renderMenu($menu, $options);
    }
}
```

Simple, but only the rendered sidebar sees the change: the breadcrumb and page title are built
from the menu too and do not pass through `renderMenu()`.

## Option 2: a visitor

A visitor implements `MenuVisitor`:

```php
interface MenuVisitor
{
    public function visit(Menu $menu): void;
}
```

!> Registering a visitor is **not** enough: `MenuBuilder::getMenu()` resets every menu's visitor
list to the two built-in ones, so a visitor of your own is never called. Until that changes, hook in
by **decorating** one of the built-in visitors, as below.

Decorate `MenuCurrentVisitor` to run after the current item is known:

```php
namespace App\Menu;

use Poncho\AdminBundle\Lib\Menu\DTO\Menu;
use Poncho\AdminBundle\Lib\Menu\Visitor\MenuCurrentVisitor;
use Poncho\AdminBundle\Lib\Menu\Visitor\MenuVisitor;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(MenuCurrentVisitor::class)]
class InboxBadgeVisitor implements MenuVisitor
{
    public function __construct(
        #[AutowireDecorated] private readonly MenuVisitor $inner,
        private readonly MessageRepository $messages,
    ) {
    }

    public function visit(Menu $menu): void
    {
        $this->inner->visit($menu);   // current and active items are resolved after this

        if (AdminMenu::class !== $menu->getName() || !$menu->getRoot()->hasChild('inbox')) {
            return;
        }

        $unread = $this->messages->countUnread();
        if ($unread > 0) {
            $menu->getRoot()->getChild('inbox')->setBadge((string) $unread, 'bg-danger');
        }
    }
}
```

- The decorator runs for **every** menu; check `$menu->getName()` — the menu's class name — as
  above.
- It runs every time the menu is built, so possibly several times per page. Keep it cheap, or cache.
- To hide items, decorate `MenuVisibilityVisitor` instead and change visibility **before** calling
  `$this->inner->visit($menu)`, so that parents left with no visible child are hidden too.

## What you can change

Items are `MenuItem` objects. Besides the getters listed in [Other menus](component/menu/custom_menu#menuitem):

| Method | |
| --- | --- |
| `setLabel()`, `setTranslationDomain()`, `setIcon()` | |
| `setBadge(string $label, ?string $class = null)` | |
| `setRoute(string $route, array $params = [])`, `setUrl()`, `setTarget()` | |
| `addMatchingRoute()` | Too late to affect the current item if you run after `MenuCurrentVisitor` |
| `setVisible(bool)`, `setActive(bool)` | |
| `addChild(MenuItem)` | New items: `new MenuItem($menu, 'id')` |
| `$menu->setCurrent(?MenuItem)` | Force the current item. Before `MenuCurrentVisitor`, this skips the request matching |
