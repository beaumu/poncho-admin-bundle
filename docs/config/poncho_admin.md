# Configuration reference

All configuration lives under the `poncho_admin` key, usually in
`config/packages/poncho_admin.yaml`. Every key is optional except where noted. The full tree, with
defaults, is [at the bottom of this page](#full-reference); your application can print it too:

```bash
php bin/console config:dump-reference poncho_admin
```

and show the values currently in effect with `php bin/console debug:config poncho_admin`.

## Application

| Key | Default | |
| --- | --- | --- |
| `app_name` | `poncho` | Shown in the sidebar, on the login and reset pages, in e-mails, and appended to page titles |
| `app_logo` | `null` | Asset path of the logo for light surfaces — the login page, e-mails |
| `app_logo_inverse` | `app_logo` | Asset path of the logo for the dark sidebar |
| `container_class` | `container-fluid` | Class of the layout's content container |
| `menu` | `BaseAdminMenu` | Class of the sidebar menu — see [The admin menu](component/menu/admin_menu) |

`app_logo` and `app_logo_inverse` are resolved with `asset()`, so they are paths under `public/`.
Set both when your logo is a single colour, so it stays visible on either background:

```yml
poncho_admin:
    app_logo:         poncho-black.svg
    app_logo_inverse: poncho-white.svg
```

The e-mail header uses `app_logo` too — see
[Password reset](user/password_reset) for what mail clients require of it.

## `user`

The admin users, login and password reset — see [Users](user/index).

| Key | Default | |
| --- | --- | --- |
| `class` | `App\Entity\AdminUser` | Your user entity, extending `BaseAdminUser`. **Set it** if yours is named otherwise |
| `manager` | `UserManager` | Service id of the [user manager](extending/user) |
| `table` | `UserTableType` | Table type of the users screen |
| `form` | `UserType` | Form type of the create/edit dialog |
| `password_reset_from_email` | `no-reply@poncho.dev` | Sender of reset e-mails. **Set it** to a domain you control — see [Password reset](user/password_reset) |
| `password_reset_from_name` | `''` | Sender name |
| `password_reset_ttl` | `86400` | Lifetime of a reset link, in seconds |
| `profile.enabled` | `true` | Registers the profile page and shows it in the user menu |
| `profile.route` | `poncho_admin_profile_index` | Route the user menu links to for the profile |
| `profile.form` | `ProfileType` | Form type of the profile page |

!> `user.enabled` has **no effect**: the user services and controllers are always registered, and
what exposes the screens is importing their routes. Leave it unset.

## `notification`

The notification bell — see [Notifications](component/notification).

| Key | Default | |
| --- | --- | --- |
| `enabled` | `false` | Shows the bell |
| `provider` | `null` | Service id of your `NotificationProviderInterface`. **Required** when enabled |
| `poll_interval` | `10` | Seconds between refreshes; `0` disables polling |

## `form`

| Key | Default | |
| --- | --- | --- |
| `layout` | `default` | `default` or `horizontal` — see [Form theme](component/form/theme#horizontal-layout) |
| `label_class` | `col-sm-2` | Label column class in the horizontal layout |
| `group_class` | `col-sm-10` | Field column class in the horizontal layout |

## `datatable`

Defaults for every table; each can be overridden per table — see
[Table options](component/datatable/options).

| Key | Default | |
| --- | --- | --- |
| `page_length` | `25` | Rows per page |
| `container_class` | `''` | Class of the element around the table |
| `class` | `table-centered` | Class of the `<table>` |
| `dom` | *see below* | datatables.net [`dom`](https://datatables.net/reference/option/dom) layout |

!> `datatable.reset_paging_on_reload` has **no effect**: no code reads it. `reloadTable()` always returns
to the first page; `reloadTable([false])` keeps the current one — see
[JsResponse](component/jsresponse/index).

## Full reference

Generated from `Configuration.php` by `ddev doc-update-config` — do not edit below this line.

```yaml
poncho_admin:

    # Name of app (Used on mail, sidebar title, login page, ...)
    app_name:             poncho

    # Path of logo, used on light surfaces (login page, emails)
    app_logo:             null

    # Path of logo used on dark surfaces (sidebar). Defaults to app_logo.
    app_logo_inverse:     null

    # Bootstrap container class : container, container-sm, container-fluid, ...
    container_class:      container-fluid

    # Name of menu to use on admin
    menu:                 Poncho\AdminBundle\Menu\BaseAdminMenu
    user:
        enabled:              false

        # The class name of UserManager service.
        manager:              Poncho\AdminBundle\Service\UserManager

        # Entity class of Admin user.
        class:                App\Entity\AdminUser

        # DataTable Type class of Admin CRUD.
        table:                Poncho\AdminBundle\DataTable\UserTableType

        # Form Type class of Admin CRUD.
        form:                 Poncho\AdminBundle\Form\UserType

        # Name of sender for password reset email.
        password_reset_from_name: ''

        # Email of sender for password reset email.
        password_reset_from_email: no-reply@poncho.dev

        # Time to live (in s) for request password.
        password_reset_ttl:   86400
        profile:
            enabled:              true

            # Route of Profile view.
            route:                poncho_admin_profile_index

            # Form Type class of Profile CRUD.
            form:                 Poncho\AdminBundle\Form\ProfileType
    notification:
        enabled:              false

        # Notification provider service used to provide notification from an user, must implements NotificationProviderInterface.
        provider:             null

        # Time (in s) between two requests of notification short-polling used to refresh notification view  (set it to 0 to disable).
        poll_interval:        10
    form:

        # Layout of bootstrap : default or horizontal.
        layout:               default

        # Default label class for horizontal bootstrap layout.
        label_class:          col-sm-2

        # Default group class for horizontal bootstrap layout.
        group_class:          col-sm-10
    datatable:

        # Default page length for datatable.
        page_length:          25

        # Default css class of container datatable.
        container_class:      ''

        # Default css class for table.
        class:                table-centered

        # Default dom for datatable @see https://datatables.net/reference/option/dom
        dom:                  "< tr><'row table-footer'<'col-sm-12 col-md-5'li><'col-sm-12 col-md-7'p>>"

        # Reset paging when call js()->reloadTable() ?
        reset_paging_on_reload: false
```