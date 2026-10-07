UPGRADE FROM 1.x to 2.0
========================

Requirements
------------

 * **PHP 8.4 is now the minimum**, up from 8.2. Symfony 8 itself requires PHP 8.4, so there is no
   way to run this version on an older PHP.

 * **Symfony 8.1 is now the minimum**, up from 6.4. `^6.4|^7.0` support is dropped entirely —
   `composer.json` now requires `^8.1`.

   Symfony **8.0.x is explicitly not supported**, even though it is a stable release: in that
   version, `symfony/doctrine-bridge`'s schema-cache listener calls a `doctrine/dbal`
   `Schema::edit()` API that only exists in `doctrine/dbal ^4.5`, which is not released yet. Running
   `doctrine:schema:create` (or anything that generates a schema — migrations included) fails
   outright on 8.0.x as a result. This was fixed by 8.1.0; if your application is stuck on 8.0 for
   another reason, wait for either a Symfony 8.0 patch or a DBAL 4.5 release before upgrading this
   bundle.

 * **`doctrine/doctrine-bundle` now requires `^3.0`** (was `^2.13.2`), and **`doctrine/dbal` now
   requires `^4.0`** (was `^3.9.4`). These follow from the Symfony bump — `doctrine-bundle ^3.0`
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

`doctrine/orm` version pin
---------------------------

This bundle pins `doctrine/orm` to `^3.3.2,<3.7.0`. `doctrine/orm 3.7.x` introduced a runtime
dependency on the same unreleased `doctrine/dbal ^4.5` `Schema::edit()` API mentioned above (its own
`composer.json` doesn't declare this, so Composer's solver won't catch it — you only find out when
`doctrine:schema:create` throws). If your application requires `doctrine/orm` directly with a wider
range, add the same upper bound, or accept the risk until DBAL 4.5 ships. This pin will be lifted
once `doctrine/dbal ^4.5` is released and this bundle's own floor is raised to match.

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
