# Coding standards

Code is checked by tools where possible; the rest is convention. When a rule below and the
surrounding code disagree, follow the surrounding code and mention it in the pull request.

## PHP

**Style** is [Symfony's](https://symfony.com/doc/current/contributing/code/standards.html), enforced
by PHP CS Fixer (`@Symfony` and `@Symfony:risky`, see `.php-cs-fixer.dist.php`) on `src/`, `tests/`
and `bin/`. `ddev fix-php` applies it. The project's own deviations:

- one space around the concatenation operator: `'a' . $b`;
- imports sorted alphabetically, no leading backslash;
- no file header comment;
- trailing commas in multi-line arrays and blank lines before statements are not enforced — follow
  the surrounding code, which mostly omits trailing commas.

Native functions the PHP compiler optimises — `\sprintf()`, `\count()`, `\is_array()`, … — are called
with a leading `\`; the fixer adds it.

**Static analysis** is PHPStan at level 5 on `src/`, with the Symfony and Doctrine extensions
(`ddev analyse`). New code must not add errors, and must not add entries to `ignoreErrors`.

**Language level.** Code runs on PHP 8.2 and Symfony 6.4 — the lowest versions `composer.json`
allows. Don't use a newer feature, and check a Symfony API exists in 6.4 before using it. When
behaviour must differ per Symfony version, test `Kernel::VERSION_ID` as the test application does.

### Conventions

These are the patterns the code base follows, so that every part of the bundle feels the same to a
user extending it:

| | |
| --- | --- |
| **Types** | Extension points are *types* — `…TableType`, `…ColumnType`, `…ActionType`, `…AdapterType`, `…MenuType` — with a `configureOptions(OptionsResolver)`. Every option gets a default or is required, and an allowed type |
| **Base classes** | A class meant to be extended by the application is named `Base…` (`BaseAdminUser`, `BaseAdminMenu`) |
| **Visibility** | Classes users extend use `protected` members and constructor-promoted `protected readonly` dependencies, so subclasses can reach them. Use `private` for what subclasses must not depend on, and `final` for classes that are not extension points |
| **Suffixes** | `…Interface`, `…Exception`, `…Extension`, `…Subscriber`, `…Type`, as in Symfony |
| **Services** | Registered in `config/*.php` under their class name. A service users should replace gets an interface and a config key (like `user.manager`) |
| **Discovery** | New extension points are autoconfigured by base class and tagged `poncho.<component>.<kind>` |
| **Exceptions** | Throw Symfony's HTTP exceptions (`BadRequestHttpException`, …) for bad requests, never a generic exception that becomes a 500 |
| **Configuration** | Every node in `Configuration.php` has an `info()` and a default |

Mark anything public that users must not rely on with `@internal`.

## Twig

- Templates live in `templates/`, reusable component templates in `templates/lib/<component>/`.
  File names are `snake_case.html.twig`.
- Anything a user may want to change is a named **block**, and the variables a template receives
  are part of its API — see the [BC promise](contributing/bc).
- Escape by default. `|raw` only on HTML the bundle built itself, never on a value that may come
  from a user — and if an option accepts HTML, its documentation says so.
- Text goes through the translator, in the `PonchoAdmin` domain.

## Translations

Keys live in `translations/PonchoAdmin.en.php`, as nested `snake_case` arrays. After adding or
changing a key used from JavaScript, run `ddev exec bin/generate-translation` and rebuild the
assets. A new key is documented in [Translations](translations).

## JavaScript

ES modules under `assets/`, checked by ESLint (`eslint:recommended`, see `.eslintrc.js`):

- 4-space indentation, single quotes;
- one component per file, named like its default export (`PonchoDataTable.js` exports
  `PonchoDataTable`);
- interactive components are **custom elements** named `poncho-…`, initialising in
  `connectedCallback()` — see [Form widgets](extending/form) for the pattern;
- shared state and helpers live on `window.poncho`, not on new globals;
- no new jQuery: it is being phased out. Use `fetch()` and DOM APIs in new code.

`ddev fix-js` applies the fixable rules.

## CSS

SCSS under `assets/scss/`. Variables users may override are declared with `!default` — see
[Theming](frontend/theming). Prefer Bootstrap utilities and variables over new rules.

## Tests

- PHPUnit tests under `tests/Unit` (no kernel) and `tests/Functional` (booting `tests/App`).
- Test names say what is tested: `testDeleteRequiresValidToken()`.
- A test must not depend on another test's state or on the order tests run in.
- A bug fix comes with the test that reproduces it.
