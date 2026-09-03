# PonchoAdmin Configuration Reference

To display the default values defined by PonchoAdmin on your own project, use :
```bash
php bin/console config:dump-reference PonchoAdminBundle
```

## Logo

`app_logo` is drawn on light surfaces (login page, e-mail header). `app_logo_inverse` is drawn on
the dark sidebar and falls back to `app_logo` when unset — set both when your logo is a single
colour, so it stays visible on either background:

```yml
poncho_admin:
    app_logo:         poncho-black.svg
    app_logo_inverse: poncho-white.svg
```

Both are asset paths resolved with Symfony's `asset()`, so they live under your app's `public/`.

Configuration reference :

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