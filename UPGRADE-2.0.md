UPGRADE FROM 1.x to 2.0
========================

Requirements
------------

 * **PHP 8.4 is now the minimum**, up from 8.2. Symfony 8 itself requires PHP 8.4, so there is no
   way to run this version on an older PHP.

 * **Symfony 8.1 is now the minimum**, up from 6.4. `^6.4|^7.0` support is dropped entirely —
   `composer.json` now requires `^8.1`.

   Symfony **8.0.x is explicitly not supported**: every 8.0.x `symfony/http-foundation` release is
   affected by published security advisories, so Composer refuses to install it, and fixes landed
   in 8.1.

 * **`doctrine/doctrine-bundle` now requires `^3.0`** (was `^2.13.2`), and **`doctrine/dbal` now
   requires `^4.5`** (was `^3.9.4`). These follow from the Symfony bump — `doctrine-bundle ^3.0`
   itself requires `dbal ^4.0` — but you need to run `composer update` and expect other
   Doctrine-adjacent packages (migrations, fixtures, extensions) to need their own major bumps too.

Required changes to your `doctrine.yaml`
-----------------------------------------

`doctrine-bundle ^3.0` removed several configuration options. If your application sets any of
these under `doctrine.dbal` or `doctrine.orm`, remove them — Composer will not catch this, only a
fresh `doctrine:schema:create` (or booting the app) will, with an
`InvalidConfigurationException: Unrecognized option "…"`.

 * **`doctrine.dbal.connections.<name>.use_savepoints`** — removed. Nested transactions always use
   savepoints now; `DBAL\Connection::setNestTransactionsWithSavepoints(false)` is no longer
   supported either. There is nothing to replace this with — just delete the line.

   Before:
   ```yaml
   doctrine:
       dbal:
           use_savepoints: true
   ```
   After: delete the `use_savepoints` line entirely.

 * **`doctrine.orm.entity_managers.<name>.auto_generate_proxy_classes`** and
   **`enable_lazy_ghost_objects`** — both removed. `doctrine/orm ^3.x` generates no proxy classes at
   all any more; every entity is a native PHP lazy ghost object (the feature these two options used
   to toggle), unconditionally. Delete both lines.

   Before:
   ```yaml
   doctrine:
       orm:
           auto_generate_proxy_classes: true
           enable_lazy_ghost_objects: true
   ```
   After: delete both lines.

 * **`doctrine.orm.entity_managers.<name>.controller_resolver.auto_mapping`** — still accepted, but
   deprecated since doctrine-bundle 3.1 and can now only be `false` (setting it to `true` is a hard
   error, not just a deprecation). If your app sets it to `false`, delete the whole
   `controller_resolver` block — that is already the default, the setting no longer does anything.
   If it is set to `true`, remove it and adapt: automatic entity-to-controller-argument mapping via
   the class name alone is no longer supported at all.

The makers write different files now
--------------------------------------

`make:admin:security`, `make:admin:notification` and `make:admin:home` used to merge into your
application's existing `config/routes.yaml` and `config/packages/poncho_admin.yaml` (and, for
security, `config/packages/security.yaml`'s `firewalls`/`access_control` too — replacing the
*whole* `access_control` list, discarding anything you already had there). They now write new,
bundle-owned files instead:

| Maker | New files |
| --- | --- |
| `make:admin:security` | `config/routes/poncho_admin_security.yaml`, `config/packages/poncho_admin_security.yaml` |
| `make:admin:notification` | `config/routes/poncho_admin_notification.yaml`, `config/packages/poncho_admin_notification.yaml` |
| `make:admin:home` | `config/packages/poncho_admin_home.yaml` |

Nothing already in `config/routes.yaml` or `config/packages/poncho_admin.yaml` is read or at risk
of being overwritten by re-running one of these makers.

The one exception is `config/packages/security.yaml`'s `firewalls` and `access_control`: Symfony's
SecurityBundle does not allow either to be defined in more than one config file once another file
already has one, so `make:admin:security` still edits that file — but it is now a genuine merge.
Whatever firewalls and `access_control` rules you already had are kept; the four the maker adds are
placed ahead of them.

This only affects **re-running** a maker. If you already ran one under 1.x, its output stays where
it is — nothing migrates your existing `config/routes.yaml` or `config/packages/poncho_admin.yaml`
content into the new files, and nothing needs to.

`doctrine/dbal` and `doctrine/orm` floors
-------------------------------------------

This bundle requires `doctrine/dbal ^4.5` and `doctrine/orm ^3.7`. `doctrine/orm 3.7` (and
`symfony/doctrine-bridge 8.x`'s schema listener) call a DBAL `Schema::edit()` API that only exists
from DBAL 4.5 — with an older DBAL, `doctrine:schema:create` throws. Neither package declares that
in its own `composer.json`, so Composer will not warn you. (An earlier 2.0.x release capped
`doctrine/orm` below 3.7 while DBAL 4.5 was unreleased; that cap is gone, and it conflicted with any
application already on ORM 3.7.)

Local development
------------------

If you run this bundle's own test suite locally (contributors), `.ddev/config.yaml` now pins
`php_version: "8.4"` — run `ddev restart` after pulling this change so the web container rebuilds on
the new PHP version.

What did **not** change
------------------------

 * The `BaseAdminUser::eraseCredentials()` deprecation from 1.2 (see `UPGRADE-1.2.md`) is **not**
   removed in this release — it is still deprecated, not gone. Removing deprecated code is a
   separate, later 2.x change; this release is scoped to the platform floor bump only.
 * No PHP 8.4 language features (property hooks, asymmetric visibility, etc.) have been adopted in
   the bundle's own code yet. This release only raises the floor; it does not yet take advantage of
   what the floor now allows.
