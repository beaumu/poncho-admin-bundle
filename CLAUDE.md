# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

Poncho Admin Bundle (`poncho/admin-bundle`) is a Symfony bundle that scaffolds Bootstrap 5 admin backends: DataTables, forms, menus, notifications, user management, and CRUD generators (`maker` commands). It is a library consumed by other Symfony apps, not a standalone application. A minimal Symfony app under `tests/App` is used to run the bundle's own test suite.

## Commands

The project has no Makefile — tooling runs through DDEV custom commands (`.ddev/commands/web/*`), executed inside the DDEV web container so they run against the project's own PHP/Node versions rather than whatever's on the host. Run `ddev start` once, then:

Backend (PHP):
```bash
ddev analyse          # phpstan (level 5, src/ only)
ddev test             # vendor/bin/simple-phpunit
ddev fix-php          # php-cs-fixer fix
ddev fix-js           # yarn lint-fix (eslint on assets/)
ddev fix-all          # fix-php + fix-js
ddev check            # fix-all + analyse + test
```
Run a single test with phpunit directly, e.g.:
```bash
ddev test --filter testMethodName tests/Functional/DataTable/SomeTest.php
```
Tests boot the app in `tests/App` (`Poncho\AdminBundle\Tests\App\Kernel`), configured via `tests/App/config`. `tests/bootstrap.php` wipes the kernel cache dir before each run — expect cold caches every run.

Frontend (JS/SCSS), via `package.json` / `yarn` — not wrapped in a dedicated DDEV command, run via `ddev exec` (or directly on the host if `yarn`/`node` are installed there):
```bash
ddev exec yarn dev            # encore dev build
ddev exec yarn watch           # encore dev --watch
ddev exec yarn build           # encore production build (outputs to public/)
ddev exec yarn lint            # eslint assets/
ddev exec yarn lint-fix        # eslint --fix assets/ (same as `ddev fix-js`)
```

Docs (docsify, source in `docs/`):
```bash
ddev doc                 # yarn doc (docsify serve docs) — served on the exposed docsify port, see .ddev/config.yaml
ddev doc-update-config    # bin/update-doc-config — regenerates docs/config/poncho_admin.md from Configuration.php
```

CI (GitHub Actions) runs phpunit across the PHP 8.2 / Symfony 6.4 & 7.2 matrix, phpstan, php-cs-fixer (`--dry-run`), and eslint — each only on PRs touching the relevant paths (`src/**`, `**.php`, `**.js`). Match these before considering a change done (`ddev check` covers phpunit/phpstan/php-cs-fixer/eslint together).

## Architecture

### Bundle wiring
- `src/PonchoAdminBundle.php` registers two compiler passes and that's the whole bundle entrypoint.
- `src/DependencyInjection/PonchoAdminExtension.php` loads `config/*.php` (PHP-based DI config, not YAML) and conditionally loads `user.php`, `userProfile.php`, `notification.php` based on config flags (`user.profile.enabled`, `notification.enabled`).
- `src/DependencyInjection/Configuration.php` defines the `poncho_admin` config tree; `src/PonchoAdminConfiguration.php` is the runtime read-facade over the resolved config array (injected wherever bundle settings are needed instead of raw `%poncho_admin.*%` parameters).
- Compiler passes (`src/DependencyInjection/Compiler/`):
  - `PonchoComponentPass` wires tagged services into two runtime registries: `DataTableRegistry` (tags: type/column type/action type/adapter type) and `MenuRegistry` (tags: type/visitor). This is the extension point for custom DataTable columns/actions/adapters and menu item types — tag a service, the pass registers it, no manual wiring needed.
  - `PonchoNotificationPass` validates the user-supplied `NotificationProviderInterface` implementation and, if it extends `BaseNotificationProvider`, injects `DateTimeFormatter`/`twig` into it via method calls.

### `src/Lib/` vs top-level `src/`
`src/Lib/*` holds the generic, reusable engines (DataTable, Form, Menu, JsResponse, Controller base class) — framework-ish code meant to be extended. Top-level `src/*` (`Entity`, `Form`, `DataTable`, `Menu`, `Controller`, `Notification`, `Security`, `Service`) holds the bundle's own concrete admin/user/notification feature built on top of that engine — treat `Lib/` as the API surface and the rest as "first consumer of the API".

### DataTable engine (`src/Lib/DataTable/`)
Server-side-processed Bootstrap DataTables. Flow: `DataTableFactory::create()`/`createBuilder()` → `DataTableBuilder` (fluent config: adapter, columns, actions, toolbar) → builds a `DTO\DataTable` → `DataTableRenderer`/`Twig\DataTableExtension` render it; AJAX paging/sorting/search requests come back through `DataTableType` and are resolved via the configured `Adapter\*Type` (`DoctrineAdapterType`, `EntityAdapterType`, `NestedEntityAdapterType`, `CallableAdapterType`) into a `DTO\DataTableResult`/`DataTableResponse`. Column rendering (`Column\*Type`) and row actions (`Action\*Type`) are both pluggable via the tags above. `AdminController::createTable()`/`createTableBuilder()` (in `src/Lib/Controller/AdminController.php`) is the usual entrypoint from a controller.

### Form / Menu / JsResponse libs
- `src/Lib/Form/`: custom field types (`AutoCompleteType`, `DatepickerType`, `NestedEntityType`, `PonchoCollectionType`, etc.) plus a global `FormTypeExtension` that applies bundle-wide label/group CSS classes (configured via `form.label_class`/`form.group_class`).
- `src/Lib/Menu/`: `MenuBuilder`/`MenuItemBuilder` build a `DTO\Menu` tree; `Visitor\*` (current-item highlighting, visibility filtering) walk it — same tag-based pluggability as DataTable.
- `src/Lib/JsResponse/`: helper for building JSON responses that trigger client-side JS actions (toasts, redirects, DOM updates) consumed by `assets/jsresponse`.

### Controllers and Maker
`AdminController` (`src/Lib/Controller/AdminController.php`) is the base class every generated/bundle controller extends — it centralizes repository/EM shortcuts, DataTable creation, toast flashes (`BAG_TOAST`), and 404/403 helpers. The concrete bundle controllers (`src/Controller/*`) — user CRUD, profile, security (login/logout, password reset), notifications — are built on it and are also the reference implementation for what generated code should look like.

`src/Maker/*` are Symfony MakerBundle commands (`make:admin:table`, `make:admin:tree`, `make:admin:security`, `make:admin:home`, `make:admin:notification`) that scaffold new CRUD/entities/features into the *consuming* application, driven by templates in `skeleton/*.tpl.php`. `MakeHelper`/`MakeValidator` (`src/Maker/Utils/`) hold shared generation/validation logic. When changing a maker, keep it and its `skeleton/*.tpl.php` counterpart in sync.

### Notifications
`NotificationProviderInterface` + `BaseNotificationProvider` (`src/Notification/`) define how the bundle polls/renders notifications; a consuming app supplies its own provider service, aliased in DI via `notification.provider` config (see `PonchoNotificationPass` above). `src/Entity/BaseNotification.php` + `config/doctrine/BaseNotification.orm.xml` is the base mapped superclass entity.

### User management
`src/Entity/BaseAdminUser.php` (+ `config/doctrine/BaseAdminUser.orm.xml`) is the base mapped superclass a consuming app's user entity extends. `Service/UserManagerInterface`/`UserManager` wraps user creation/password logic, aliased via `user.manager` config. `Security/AuthenticationEntryPoint` and `Security/UserChecker` plug into Symfony Security. `Command/CreateAdminUserCommand` is the CLI to bootstrap an admin user.

### Doctrine mapping style
Bundle entities (`BaseAdminUser`, `BaseNotification`) use XML mapping (`config/doctrine/*.orm.xml`) rather than attributes, since they're mapped superclasses meant to be extended by the consuming app's own (attribute- or XML-mapped) entity.

### Frontend assets
`assets/` is a Symfony Webpack Encore app (single entry `admin.js`) providing the JS/SCSS counterpart to the PHP DataTable/Form/Menu/Notification/JsResponse libs (jQuery + Bootstrap 5 + DataTables.net + Tom Select + Flatpickr + SimpleBar + Toastify, per `package.json`). Built output goes to `public/` and is exposed to consuming apps as an Asset package (`src/Asset/AssetPackage.php`, tagged `assets.package`). PHP-side changes to DataTable/Form/JsResponse behavior often have a matching JS counterpart under the corresponding `assets/*` subfolder (`datatable`, `form`, `jsresponse`, `ui`) — check both sides when changing interactive behavior.

### Templates
`templates/` holds the bundle's Twig templates (admin layout, DataTable rendering, form themes, menu rendering, notification UI); `skeleton/*.tpl.php` are separate PHP-string templates used only by the Maker commands to generate code/templates into the consuming app — don't confuse the two.

### Docs
`docs/` is a docsify site (source of truth for consumer-facing documentation, deployed to the GitHub Pages docs site linked from `README.md`). `docs/config/poncho_admin.md` is generated by `bin/update-doc-config` from `Configuration.php` — don't hand-edit it, regenerate via `ddev doc-update-config` after changing the config tree.
