# Twig

## Functions

| Function | |
| --- | --- |
| `render_table(table)` | Render a [DataTable](component/datatable/index) |
| `render_action(action)` | Render one table action |
| `get_menu(menu, options)`, `render_menu(menu, options)` | [Menus](component/menu/custom_menu#twig-functions) |
| `get_breadcrumb(menu, options, ...children)`, `render_breadcrumb(breadcrumb, options)` | |
| `get_page_title_from_menu(menu, separator = ' ~ ')` | |
| `poncho_form_theme(form, layout = null, useDefaultThemes = true)` | Apply a [form theme](component/form/theme) |

## Test

`instanceof` checks a value's class:

```twig
{% if entity is instanceof('App\\Entity\\Mission') %}…{% endif %}
```

## Global: `poncho_admin`

The bundle's configuration, as `Poncho\AdminBundle\PonchoAdminConfiguration`:

| Method | |
| --- | --- |
| `appName()`, `appLogo()`, `appLogoInverse()` | |
| `containerClass()` | |
| `menuName()` | The configured menu class |
| `logoutPath()` | |
| `notificationEnable()`, `notificationPollInterval()` | |
| `userClass()`, `userTable()`, `userForm()` | |
| `userProfileEnable()`, `userProfileRoute()`, `userProfileForm()` | |
| `userPasswordResetEmailAddress()`, `userPasswordResetTtl()` | |
| `getBootstrapFormLayout()` | |

```twig
<footer>{{ poncho_admin.appName }} — {{ 'now'|date('Y') }}</footer>
```

The same object is a service, injectable anywhere.

## The layout

Every admin page extends `@PonchoAdmin/layout.html.twig`.

| Block | Default content |
| --- | --- |
| `favicon` | `favicon.svg` and `favicon.ico` from your `public/` |
| `title` | The menu trail and the app name |
| `stylesheets` | `@PonchoAdmin/_stylesheets.html.twig` |
| `body_class` | empty — classes added to `<body>` |
| `body` | Everything visible |
| `sidebar` | `render_menu(admin_menu)` |
| `topbar` | Sidebar toggle, breadcrumb, notifications, user menu |
| `breadcrumb` | `render_breadcrumb(admin_menu)` |
| `content` | **Your page** |
| `javascripts` | `@PonchoAdmin/_scripts.html.twig` |

The variable `admin_menu` holds the built menu. Set it before the layout runs to pass build options —
see [The admin menu](component/menu/admin_menu#how-the-layout-uses-it).

```twig
{% extends '@PonchoAdmin/layout.html.twig' %}

{% block body_class %}page-missions{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="{{ asset('css/admin-extra.css') }}">
{% endblock %}

{% block content %}
    {{ render_table(table) }}
{% endblock %}
```

Other page templates you can extend or render:

| Template | Variables | |
| --- | --- | --- |
| `@PonchoAdmin/datatable.html.twig` | `table` | A page that is only a table |
| `@PonchoAdmin/edit.html.twig` | `form`, `entity` (required) | A page that is only a form |
| `@PonchoAdmin/edit_modal.html.twig` | `form`, `entity` or `title` | The same, in a modal |
| `@PonchoAdmin/security/layout.html.twig` | | Base of the login and reset pages. Blocks: `title`, `body`, `content` |

## Overriding templates

Any bundle template can be replaced the standard Symfony way — put a file at the same path under
`templates/bundles/PonchoAdminBundle/`:

```
templates/bundles/PonchoAdminBundle/security/login.html.twig      replaces @PonchoAdmin/security/login.html.twig
templates/bundles/PonchoAdminBundle/menu/breadcrumb.html.twig     replaces @PonchoAdmin/menu/breadcrumb.html.twig
```

To extend the original instead of copying it, use the `!` prefix, which always points at the
bundle's own file:

```twig
{# templates/bundles/PonchoAdminBundle/_userinfo.html.twig #}
{% extends '@!PonchoAdmin/_userinfo.html.twig' %}
```

The most common overrides:

| Template | Why |
| --- | --- |
| `_stylesheets.html.twig`, `_scripts.html.twig` | Load your own build of the assets — see [Theming](frontend/theming) |
| `_userinfo.html.twig` | The user menu in the top bar |
| `security/login.html.twig` | The login page |
| `email/password_reset.html.twig` | The reset e-mail |
| `lib/datatable/datatable.html.twig`, `lib/datatable/toolbar.html.twig` | Table markup. Prefer the `template` / `toolbar_template` options for a single table |

!> Template names and block names are part of the bundle's public API: a release that changes them
is a breaking change and says so in the changelog. Overriding a *whole* template still means you
own its markup — check the changelog when upgrading.
