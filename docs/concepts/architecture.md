# Architecture

Poncho is built from a handful of small subsystems — [DataTable](component/datatable/index),
[Menu](component/menu/quick_start), [JsResponse](component/jsresponse/index), [forms](component/form/types) — that all
follow the same design. Once you know it, every part of the bundle reads the same way, and
extending one works exactly like extending another.

## The design: Symfony's Form component, reused

If you have written a Symfony `FormType`, you already know how a Poncho `DataTableType`,
`ColumnType`, `ActionType`, `AdapterType` or `MenuType` works. Each subsystem splits into five
roles:

| Role | Examples | Responsibility |
| --- | --- | --- |
| **Type** | `DataTableType`, `ColumnType`, `ActionType`, `AdapterType`, `MenuType` | A declarative, stateless definition. Declares its options in `configureOptions(OptionsResolver)` |
| **Registry** | `DataTableRegistry`, `MenuRegistry` | Holds every known type, keyed by its service id |
| **Factory** | `DataTableFactory` | Resolves options through `OptionsResolver` and produces DTOs |
| **Builder** | `DataTableBuilder`, `ColumnActionBuilder`, `MenuBuilder`, `MenuItemBuilder` | Fluent API used inside `buildTable()` / `buildMenu()` |
| **DTO** | `DTO\DataTable`, `DTO\Column`, `DTO\Action`, `DTO\Menu`, `DTO\MenuItem` | The runtime object that gets handled and rendered |

A type is a **service**, and a single instance is shared across every table or menu that uses it.
Keep types stateless: anything that varies per table belongs in `$options`.

## How your classes are discovered

You never register a type by hand. `PonchoAdminExtension` declares these autoconfiguration rules:

```php
$container->registerForAutoconfiguration(DataTableType::class)->addTag('poncho.datatable.type');
$container->registerForAutoconfiguration(ColumnType::class)->addTag('poncho.datatable.columntype');
$container->registerForAutoconfiguration(ActionType::class)->addTag('poncho.datatable.actiontype');
$container->registerForAutoconfiguration(AdapterType::class)->addTag('poncho.datatable.adaptertype');
$container->registerForAutoconfiguration(MenuType::class)->addTag('poncho.menu.type');
$container->registerForAutoconfiguration(MenuVisitor::class)->addTag('poncho.menu.visitor');
```

`PonchoComponentPass`, a compiler pass registered by `PonchoAdminBundle::build()`, then collects
every tagged service and injects it into the matching registry.

So the chain for a class of yours is:

1. You write `App\DataTable\MissionTableType extends DataTableType`.
2. Symfony autoconfigures it with the `poncho.datatable.type` tag.
3. The compiler pass calls `DataTableRegistry::registerType('App\DataTable\MissionTableType', …)`.
4. `$this->createTable(MissionTableType::class)` looks it up by that name.

This relies on your class being an **autoconfigured service**. With Symfony's default
`config/services.yaml` (`App\: resource: '../src/'`, `autoconfigure: true`) that is automatic. If
you exclude a directory or disable autoconfiguration, you get:

```
DataTableType "App\DataTable\MissionTableType" doesn't exist, maybe you have forget to register it ?
```

Fix it by tagging the service yourself, or by making sure it is autoconfigured.

Because the registry key is the service id, and autoconfigured services are named after their
class, **types are always referenced by fully qualified class name** — `MissionTableType::class`,
never a short alias.

## Options, in three layers

Every DTO's options are resolved by an `OptionsResolver` fed from three layers, lowest priority
first:

1. **Bundle defaults** — the `final static defaultConfigureOptions()` method on the base type.
2. **Bundle configuration** — for tables, `poncho_admin.datatable.*` supplies the defaults for
   `page_length`, `dom`, `class` and `container_class`.
3. **Your type** — whatever your `configureOptions()` sets.

The options passed at the call site (`createTable(MissionTableType::class, ['page_length' => 50])`)
win over all three.

## Request lifecycle of a table

A DataTable page is served by **one controller action answering two kinds of request**:

```
GET  /mission                       → no `_dtid` in the request
     └── $table->isCallback() === false → render Twig, which emits <poncho-datatable>

POST /mission  (_dtid=<table id>)   → sent by <poncho-datatable> on load, paging, sort, filter
     └── $table->isCallback() === true  → return $table->getCallbackResponse() (JSON)
```

A request counts as a callback only when its HTTP method matches the table's `method` option
**and** it carries a `_dtid` parameter equal to the table's id. That is why several tables can
share one URL, and why a table built in one action cannot answer callbacks aimed at another.

See [DataTable](component/datatable/index) for the full walkthrough.

## Where things live

| Directory | Contents |
| --- | --- |
| `src/Lib/` | The reusable components: DataTable, Form, Menu, JsResponse, and `AdminController` |
| `src/Controller`, `src/Form`, `src/DataTable`, `src/Service`, `src/Security` | The built-in admin features — login, password reset, profile, user CRUD, notifications — built *with* `src/Lib` |
| `src/Entity/` | `BaseAdminUser` and `BaseNotification`, mapped as Doctrine mapped superclasses through `config/doctrine/*.orm.xml`, plus `IdTrait` and `SearchTrait` |
| `src/Maker/` + `skeleton/` | The `make:admin:*` commands and the code they generate |
| `config/` | Service definitions (`*.php`), route files (`routes/*.php`) and Doctrine mappings |
| `templates/` | Every Twig template, all overridable |
| `assets/` | JavaScript and SCSS sources |
| `public/` | The compiled, versioned CSS and JavaScript that ship with the package |
| `translations/` | The `PonchoAdmin` translation domain |

`src/Lib` has no dependency on the built-in admin features, so it is usable on its own: nothing
stops you using DataTables and JsResponse in an application that never enables the user
management.
