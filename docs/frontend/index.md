# Frontend

## What ships

The package contains its CSS and JavaScript **already compiled**, in `public/`:

```
public/poncho_admin.<hash>.css
public/poncho_admin.<hash>.js
public/manifest.json
```

`assets:install` exposes them at `/bundles/ponchoadmin/`, and the layout loads them through two
partials:

```twig
{# @PonchoAdmin/_stylesheets.html.twig #}
<link rel="stylesheet" href="{{ asset('poncho_admin.css', 'poncho_admin.assets.package') }}">

{# @PonchoAdmin/_scripts.html.twig #}
<script src="{{ asset('poncho_admin.js', 'poncho_admin.assets.package') }}"></script>
```

`poncho_admin.assets.package` is an asset package pointing at `/bundles/ponchoadmin` that resolves
the hashed file names from `manifest.json`.

This needs **no Node, no build step and no configuration**, and works the same whether your
application uses Webpack Encore, AssetMapper, or nothing at all. It is the default and it stays the
default.

## Three ways to use the assets

| | You get | You need |
| --- | --- | --- |
| **Prebuilt** (default) | The bundle's theme as is | Nothing |
| **Your own build** | The theme recompiled with your colours, fonts, spacing | Webpack Encore and the bundle's peer dependencies. See [Theming](frontend/theming) |
| **AssetMapper** | Your *own* assets through AssetMapper, next to the prebuilt Poncho ones | Nothing extra — AssetMapper maps the bundle's `public/` as `bundles/ponchoadmin` automatically |

Recompiling the bundle's *sources* through AssetMapper is not supported yet: the JavaScript depends
on jQuery and the stylesheets on Sass.

## Loaded from third parties

Every admin page makes two requests outside your domain:

| Request | From | Why |
| --- | --- | --- |
| `https://unpkg.com/@ungap/custom-elements` | the layout's `<head>` | A polyfill: Safari does not support [customised built-in elements](#custom-elements) |
| `https://fonts.googleapis.com/css2?family=Inter…` | `_stylesheets.html.twig` | The *Inter* font |

!> Both matter in production. The polyfill is loaded **unversioned and without an integrity hash**,
so whatever unpkg serves runs with your admins' privileges. Both requests send each visitor's IP
address to a third party — a GDPR concern in the EU, where courts have ruled embedding Google Fonts
this way unlawful — and both break under a strict `Content-Security-Policy` or on an offline
network. See [Security](security#third-party-requests) for how to self-host them.

## The `poncho` global

`poncho_admin.js` defines `window.poncho`:

| Property | |
| --- | --- |
| `locale` | From `<html lang>` |
| `translator` | `trans(key, params = {}, locale = null)` over the `PonchoAdmin` strings |
| `toast` | `show(type, text, title, options)`, `info()`, `success()`, `warning()`, `error()` |
| `spinner` | `show()`, `hide()` — the full-page loader |
| `confirmModal` | `show({text, confirm, cancel_text, confirm_text})` |
| `jsResponseHandler` | See [JsResponse client side](component/jsresponse/client) |

## Custom elements

The interactive parts are [custom elements](https://developer.mozilla.org/docs/Web/API/Web_components/Using_custom_elements),
so they initialise themselves whenever they enter the page — including HTML inserted by a
`JsResponse`.

| Element | Rendered by |
| --- | --- |
| `<poncho-datatable>` | `render_table()` — see [DataTable JavaScript](component/datatable/javascript) |
| `<poncho-collection>` | `PonchoCollectionType` |
| `<input is="poncho-datepicker">` | `DatepickerType` |
| `<select is="poncho-autocomplete">` | `AutoCompleteType`, `ub_autocomplete` |
| `<div is="password-togglable">` | `PasswordTogglableType` |
| `<nav is="poncho-sidebar">` | The admin sidebar |
| `<li is="poncho-notification">` | The notification bell |

The `is="…"` ones are *customised built-ins*, which need the polyfill above in Safari.

Each exposes methods usable from JavaScript or from `JsResponse::call()`. For instance
`<select is="poncho-autocomplete">`:

| Method | |
| --- | --- |
| `getValue()`, `setValue(value, silent = false)` | |
| `selectAll(silent = false)`, `unselectAll(silent = false)` | |
| `getOptions()`, `getSelectedOptions()` | |
| `hideOptions(bool \| (option) => bool)` | Hide all options, or those the callback selects |

and `<poncho-collection>` has `addRow()`, `deleteRow(row)` and `count()`.

## Adding your own JavaScript and CSS

Extend the layout's blocks, keeping the bundle's:

```twig
{% block stylesheets %}
    {{ parent() }}
    {{ encore_entry_link_tags('admin') }}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    {{ encore_entry_script_tags('admin') }}
{% endblock %}
```

Your scripts run after `poncho_admin.js`, so `window.poncho` is available to them.

To **replace** the bundle's assets rather than add to them, override the two partials instead —
see [Theming](frontend/theming).
