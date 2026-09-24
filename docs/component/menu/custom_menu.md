# Other menus

The admin sidebar is one menu, but any number can exist — a footer, a tab bar, a user menu. Extend
`MenuType` directly and implement the rendering yourself:

```php
namespace App\Menu;

use Poncho\AdminBundle\Lib\Menu\Builder\MenuBuilder;
use Poncho\AdminBundle\Lib\Menu\DTO\Breadcrumb;
use Poncho\AdminBundle\Lib\Menu\DTO\Menu;
use Poncho\AdminBundle\Lib\Menu\MenuType;
use Twig\Environment;

class FooterMenu extends MenuType
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function buildMenu(MenuBuilder $builder, array $options): void
    {
        $builder->root()
            ->add('privacy')->route('app_privacy')->end()
            ->add('terms')->route('app_terms');
    }

    public function renderMenu(Menu $menu, array $options): string
    {
        return $this->twig->render('menu/footer.html.twig', ['menu' => $menu]);
    }

    public function renderBreadcrumb(Breadcrumb $breadcrumb, array $options): string
    {
        return $this->twig->render('menu/footer_breadcrumb.html.twig', ['breadcrumb' => $breadcrumb]);
    }
}
```

`renderMenu()` and `renderBreadcrumb()` throw a `LogicException` unless you implement them —
implement only what you render.

A menu is referenced by its fully qualified class name.

## Twig functions

| Function | Returns | |
| --- | --- | --- |
| `get_menu(menu, options = {})` | `Menu` | Build a menu. `options` reach `buildMenu()` |
| `render_menu(menu, options = {})` | HTML | `options` reach `renderMenu()` |
| `get_breadcrumb(menu, options = {}, ...children)` | `Breadcrumb` | `options` reach `buildMenu()`; `children` are appended |
| `render_breadcrumb(breadcrumb, options = {})` | HTML | `options` reach `renderBreadcrumb()` |
| `get_page_title_from_menu(menu, separator = ' ~ ')` | `string` | The current item's trail |

`menu` is a class name or a `Menu`; `breadcrumb` is a `Breadcrumb`, a `Menu` or a class name.

!> Given a class name, each of these **builds the menu again**. On a page that renders the sidebar,
the breadcrumb and the title, build once with `get_menu()` and pass the result around, as the admin
layout does. Also note that `render_menu('App\\Menu\\X', …)` cannot pass build options — its
options go to `renderMenu()` only.

```twig
{% set footer = get_menu('App\\Menu\\FooterMenu') %}
{{ render_menu(footer) }}
```

## Rendering a menu yourself

A `Menu` holds a tree of `MenuItem`s. `MenuItem` is iterable and countable over its children.

```twig
{# menu/footer.html.twig #}
<ul>
    {% for item in menu.root %}
        {% if item.visible %}
            <li class="{{ item.active ? 'active' }}">
                {% if item.hasLink %}
                    <a href="{{ item.route ? path(item.route, item.routeParams) : item.url }}"
                       {% if item.target %}target="{{ item.target }}"{% endif %}>
                        {{ item.translationDomain ? item.label|trans({}, item.translationDomain) : item.label }}
                    </a>
                {% else %}
                    {{ item.label }}
                {% endif %}
            </li>
        {% endif %}
    {% endfor %}
</ul>
```

### Menu

| Method | |
| --- | --- |
| `getName()` | The class name |
| `getRoot()` | Root item. It is not rendered itself |
| `getCurrent()`, `setCurrent(?MenuItem)` | |

### MenuItem

| Method | |
| --- | --- |
| `getName()`, `getCssId()` | Id, and `menu-item-<id in snake_case>-<level>` — not unique if two branches reuse an id at the same depth |
| `getLabel()`, `getTranslationDomain()`, `getIcon()` | |
| `hasBadge()`, `getBadgeLabel()`, `getBadgeClass()` | |
| `hasLink()`, `getRoute()`, `getRouteParams()`, `getUrl()`, `getTarget()` | |
| `isVisible()`, `isActive()` | Active means current, or an ancestor of it |
| `getParent()`, `getChildren()`, `hasChildren()`, `getChild(string)`, `hasChild(string)` | |
| `getLevel()`, `isRoot()` | |
| `getMatchingRoutes()` | `route => params` |

### Breadcrumb

Iterable and countable over `BreadcrumbItem`s, with `getName()`, `getIcon()` (the current item's
icon, or the first ancestor's that has one), `add()` and `clear()`. Each `BreadcrumbItem` has
`getLabel()`, `getTranslationDomain()`, `getRoute()` and `getRouteParams()`.

Breadcrumb items only link through **routes**: an item built with `url()` appears in the trail as
plain text.
