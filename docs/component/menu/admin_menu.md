# The admin menu

`BaseAdminMenu` renders the sidebar and the breadcrumb of `@PonchoAdmin/layout.html.twig`. Which
class is used comes from configuration:

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    menu: App\Menu\AdminMenu        # default: Poncho\AdminBundle\Menu\BaseAdminMenu, an empty menu
```

## Render options

`renderMenu()` merges your options over these defaults:

| Option | Default | |
| --- | --- | --- |
| `title` | `poncho_admin.app_name` | Text next to the logo |
| `logo` | `poncho_admin.app_logo_inverse`, falling back to `app_logo` | Asset path of the logo |
| `logo_route` | `null` | Route the logo links to |
| `searchable` | `false` | Show a search field that filters the items as you type. It is autofocused |
| `template` | `@PonchoAdmin/menu/sidebar.html.twig` | |

The breadcrumb takes one option:

| Option | Default |
| --- | --- |
| `template` | `@PonchoAdmin/menu/breadcrumb.html.twig` |

Change the defaults for every page in your menu's constructor:

```php
class AdminMenu extends BaseAdminMenu
{
    public function __construct(Environment $twig, PonchoAdminConfiguration $configuration)
    {
        parent::__construct($twig, $configuration);

        $this->defaultRenderOptions['searchable'] = true;
        $this->defaultRenderOptions['logo_route'] = 'app_admin_home';
    }
}
```

## How the layout uses it

`@PonchoAdmin/layout.html.twig` builds the menu **once** and reuses it:

```twig
{% if admin_menu is not defined %}
    {% set admin_menu = get_menu(poncho_admin.menuName()) %}
{% endif %}
```

then feeds `admin_menu` to the page `<title>`, the sidebar and the breadcrumb. So you can:

**Pass build options** — set `admin_menu` yourself before the layout runs:

```twig
{% extends '@PonchoAdmin/layout.html.twig' %}
{% set admin_menu = get_menu('App\\Menu\\AdminMenu', {section: 'missions'}) %}
```

**Change how the sidebar renders** — override its block:

```twig
{% block sidebar %}
    {{ render_menu(admin_menu, {searchable: true}) }}
{% endblock %}
```

**Extend the breadcrumb** — append items after the menu's trail:

```twig
{% block breadcrumb %}
    {{ render_breadcrumb(get_breadcrumb(admin_menu, {}, mission.name)) }}
{% endblock %}
```

An extra breadcrumb item is a string (a label), an array
`{label, route, route_params, translation_domain}`, or a `BreadcrumbItem`.

## Page title

The layout's `<title>` is the current item's trail joined with ` ~ `, then ` | ` and the app name:
*Missions ~ Failed | Admin*. Build it yourself with:

```twig
{{ get_page_title_from_menu(admin_menu, ' / ') }}
```

## Post-processing the menu

To change a menu after it is built and its current item resolved — a badge computed from the
current item, say — override `renderMenu()`. It receives the finished `Menu`:

```php
public function renderMenu(Menu $menu, array $options): string
{
    foreach ($menu->getRoot()->getChildren() as $item) {
        // …inspect or change $item
    }

    return parent::renderMenu($menu, $options);
}
```

Only the sidebar sees such a change — not the breadcrumb or the page title. To change the menu for
all of them, use a visitor. A plain `MenuVisitor` is never called today; decorating a built-in one
works — see [Extending: menus](extending/menu#option-2-a-visitor).

## Replacing the templates

Both templates can be overridden like any bundle template — see [Templates](twig#overriding-templates)
— or swapped per page with the `template` option. The sidebar template has one block,
`items_container`; each item is rendered by the `sidebar_item` block of
`@PonchoAdmin/menu/sidebar_block.html.twig`.
