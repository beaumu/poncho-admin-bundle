# Backward compatibility promise

Upgrading to a new **minor** or **patch** version of the bundle must never break an application.
This page defines what that covers — for users, what they may rely on; for contributors, what they
may change. It follows [Symfony's promise](https://symfony.com/doc/current/contributing/code/bc.html),
extended to the bundle's templates, configuration and frontend, because those are what applications
extend most.

Versions follow [Semantic Versioning](https://semver.org/):

| Version | May contain |
| --- | --- |
| Patch — `1.2.x` | Bug fixes only. Security fixes |
| Minor — `1.x.0` | New features, [deprecations](contributing/deprecations). Bug fixes |
| Major — `x.0.0` | Removal of deprecated code, and other BC breaks, each listed in `UPGRADE-x.0.md` |

## What is covered

Everything below is **public API**, unless marked `@internal` or described in the documentation as
experimental.

| Area | Public API |
| --- | --- |
| **PHP** | Every class, interface and trait in `Poncho\AdminBundle\`, except `Poncho\AdminBundle\Tests\`: their names, their public and protected methods and properties, and their constants |
| **Types** | The options of every table, column, action, adapter, form and menu type: names, allowed types, defaults and meaning |
| **Configuration** | Every key under `poncho_admin`, its allowed values and its default |
| **Services** | Service ids and aliases (`UserManagerInterface`, `DataTableFactory`, `MenuProvider`, the visitors, `poncho_admin.assets.package`…), and the tags `poncho.*` |
| **Routes** | The names, paths and parameters of the routes the bundle ships (`poncho_admin_*`) |
| **Twig** | Template paths under `@PonchoAdmin`, the blocks they define and the variables they receive; Twig functions and filters with their arguments; form theme block names |
| **Translations** | Keys of the `PonchoAdmin` domain |
| **Markup hooks** | CSS classes, ids and `data-*` attributes the templates emit and the documentation mentions |
| **Frontend** | Custom element names and their public methods; `window.poncho` and its members; JsResponse action names and parameters; the `data-xhr`, `data-confirm`, `data-spinner`, `data-method` attributes; built asset names (`poncho_admin.js`, `poncho_admin.css`); SCSS variables declared `!default` |
| **Makers** | Command names and their questions. Not the generated code — once generated, it is the application's |
| **Database** | The mapping of `BaseAdminUser`: a change needs a migration in the application, so it is a BC break |

Not covered: `private` members, anything `@internal`, the exact HTML outside the hooks above, the
contents of compiled assets, the test application, and behaviour that is documented as a bug.

## Rules for contributors

What may change in a **minor** version, depending on how users use the code:

### Interfaces

`UserManagerInterface`, `MenuVisitor` and `NotificationProviderInterface` are **implemented** by
applications.

| Change | Minor version? |
| --- | --- |
| Add a method | **No** — every implementation breaks. Add a new interface, or add the method to the default implementation first and to the interface in the next major |
| Add an argument to a method | **No** |
| Change a type, rename, remove | **No** |
| Add a constant | Yes |

### Classes users extend

Types, `Base*` classes, `UserManager`, `AdminController` and every non-`final` class are
**extended** by applications. Protected members are therefore public API.

| Change | Minor version? |
| --- | --- |
| Add a public or protected method | Yes — but it may clash with a method a subclass already has; choose a specific name |
| Add an abstract method | **No** |
| Add an optional argument to a public or protected method | **No** for methods subclasses override — their signature no longer matches. Use `func_get_args()` and a deprecation instead, as Symfony does |
| Add a constructor dependency | Only as an optional argument at the end — subclasses call `parent::__construct()` |
| Narrow a parameter type, widen a return type | **No** |
| Make a method `final`, reduce visibility, remove | **No** — deprecate |
| Change what a protected property holds | **No** |

### Options and configuration

| Change | Minor version? |
| --- | --- |
| Add an option or a configuration key with a default that keeps today's behaviour | Yes |
| Add a required option or key | **No** |
| Change a default that changes behaviour | **No** — add a new option, or deprecate the old default |
| Allow more types or values | Yes |
| Allow fewer types or values, rename, remove | **No** — deprecate with `setDeprecated()` |

### Templates and frontend

| Change | Minor version? |
| --- | --- |
| Add a block, a template, a variable, a Twig function argument with a default | Yes |
| Rename or remove a block, a template or a variable | **No** — deprecate with `{% deprecated %}` |
| Change markup inside a block | Yes, if the covered hooks stay |
| Rename or remove a covered CSS class, id or `data-*` attribute | **No** |
| Add a translation key | Yes |
| Rename or remove a translation key, change the meaning of a message | **No** — keep the old key until the next major |
| Add a method to a custom element, a member to `window.poncho`, a JsResponse action | Yes |
| Rename or remove them, change their parameters | **No** |
| Rename or remove a `!default` SCSS variable | **No** |

### Security fixes

A security fix may break backward compatibility in a patch version when there is no other way to
close the hole. The CHANGELOG entry and the advisory then say exactly what changed and how to adapt.

## When a break is unavoidable

Deprecate the old way in a minor version, so that users see a notice and have time to change —
see [Deprecations](contributing/deprecations) — and remove it in the next major, with an entry in
`UPGRADE-x.0.md`. When unsure whether a change is a break, say so in the pull request: the table's
**BC breaks?** row exists for that question.
