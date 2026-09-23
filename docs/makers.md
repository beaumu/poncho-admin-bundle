# Makers

Five [MakerBundle](https://symfony.com/bundles/SymfonyMakerBundle/current/index.html) commands
generate code that follows the bundle's conventions. They need `symfony/maker-bundle` in your
application, and are interactive — they take no arguments.

| Command | Asks for | Generates | Also changes |
| --- | --- | --- | --- |
| `make:admin:home` | controller class | Controller, template, `AdminMenu` | `poncho_admin.menu` |
| `make:admin:security` | user entity class | `AdminUser` entity, repository | `poncho_admin.user.class`, `routes.yaml`, `security.yaml` |
| `make:admin:table` | entity, controller, edit view | Entity, repository, form, table type, controller, templates | — |
| `make:admin:tree` | entity, controller, edit view | Nested-set entity, repository, form, table type, controller, templates | — |
| `make:admin:notification` | entity class | Notification entity, repository, provider | `poncho_admin.notification`, `routes.yaml` |

Each prints its next steps when it finishes. Only YAML configuration can be updated
automatically; with PHP or XML config files the makers print what to add instead.

## make:admin:home

Your admin's entry point. See [Create your first page](getting-started/create_home).

## make:admin:security

User entity, login, password reset, profile and user CRUD. See
[Configure security](getting-started/configure_security) — the generated `security.yaml` needs two
corrections, and the whole `access_control` list is **replaced**.

## make:admin:table and make:admin:tree

A full CRUD for one entity. See [Create a CRUD](getting-started/crud).

`make:admin:tree` requires `stof/doctrine-extensions-bundle` and refuses to run without it. See
[Tree tables](component/datatable/tree).

## make:admin:notification

See [Notifications](component/notification).

## Changing what they generate

The templates live in `vendor/poncho/admin-bundle/skeleton/`. They cannot be overridden from your
application; to change them, contribute to the bundle.
