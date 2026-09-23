# Deprecations

A deprecation announces that something will be removed in the next major version, while it keeps
working until then. It is how the bundle changes without breaking the
[backward compatibility promise](contributing/bc), and it follows
[Symfony's conventions](https://symfony.com/doc/current/contributing/code/conventions.html#deprecating-code).

Deprecations are added in **minor** versions only, never in a patch.

## Checklist

Every deprecation comes with all of these:

1. **The old code keeps working**, usually by delegating to the new one.
2. **A runtime notice** telling users what to use instead — how depends on what is deprecated, see
   below.
3. **A `@deprecated` tag** on PHP code, so IDEs strike it through:
   `@deprecated since poncho/admin-bundle 1.3, use newMethod() instead`.
4. **A CHANGELOG entry** under `### Deprecated` in the `[Unreleased]` section.
5. **An entry in `UPGRADE-<version>.md`** — `UPGRADE-1.3.md` for a deprecation in 1.3 — with the
   old and the new code side by side. [UPGRADE-1.2.md](https://github.com/beaumu/poncho-admin-bundle/blob/main/UPGRADE-1.2.md)
   is an example.
6. **A test** that the old code still works *and* triggers the notice — see [Testing](#testing).
7. **No use by the bundle itself.** Move the bundle's own code to the replacement in the same pull
   request. CI fails on any deprecation triggered from the bundle's own code.

The message always names the package, the version, and the replacement:

```
Method "Foo::bar()" is deprecated since poncho/admin-bundle 1.3, use "Foo::baz()" instead.
```

## Triggering the notice

### Methods and functions

```php
/**
 * @deprecated since poncho/admin-bundle 1.3, use baz() instead
 */
public function bar(): void
{
    trigger_deprecation('poncho/admin-bundle', '1.3', 'Method "%s()" is deprecated, use "%s::baz()" instead.', __METHOD__, self::class);

    $this->baz();
}
```

`trigger_deprecation()` comes from `symfony/deprecation-contracts`. The first pull request that uses
it adds that package to `require` in `composer.json` — the bundle requires what it uses directly.

!> **A method Symfony calls itself** — such as `UserInterface::eraseCredentials()` — must not
trigger a notice: users cannot stop Symfony calling it, so they would see a notice they cannot fix.
Mark it with PHP's `#[\Deprecated]` attribute and the `@deprecated` tag only, as `BaseAdminUser`
does since 1.2. Symfony 7.3+ recognises the attribute and stops calling the method.

### Classes

Trigger the notice when the file is loaded, above the class:

```php
namespace Poncho\AdminBundle\Lib\DataTable\Column;

trigger_deprecation('poncho/admin-bundle', '1.3', 'The "%s" class is deprecated, use "%s" instead.', OldColumnType::class, NewColumnType::class);

/**
 * @deprecated since poncho/admin-bundle 1.3, use NewColumnType instead
 */
class OldColumnType extends NewColumnType
{
}
```

For a **renamed** class, keep the old name as a subclass of the new one, as above, so that
`instanceof` checks and type hints on either name keep working.

### Arguments

To make an argument obsolete, keep it, stop using it, and trigger the notice when it is passed. To
*add* an argument to a method that subclasses override — which would break their signature — read
it through `func_get_args()` and deprecate not passing it:

```php
public function render(mixed $rowData, array $options /* , ?Context $context = null */): string
{
    $context = \func_num_args() > 2 ? func_get_arg(2) : null;
    if (null === $context) {
        trigger_deprecation('poncho/admin-bundle', '1.3', 'Not passing a "$context" to "%s()" is deprecated.', __METHOD__);
    }
    // …
}
```

In the next major, the commented-out argument becomes real.

### Type options

```php
$resolver
    ->setDefined('old_option')
    ->setDeprecated('old_option', 'poncho/admin-bundle', '1.3', 'The option "%name%" is deprecated, use "new_option" instead.');
```

To deprecate only some values, pass a closure as the message:
`fn (Options $options, mixed $value): string` returns the message, or an empty string when the
value is fine.

### Configuration keys

In `Configuration.php`:

```php
$u->scalarNode('enabled')
    ->setDeprecated('poncho/admin-bundle', '1.3', 'The "%path%.%node%" option is deprecated and has no effect.');
```

Then regenerate the reference with `ddev doc-update-config`.

### Services and routes

```php
// config/*.php
$services->alias('poncho_admin.old_id', NewService::class)
    ->deprecate('poncho/admin-bundle', '1.3', 'The "%alias_id%" service is deprecated, use "NewService" instead.');

// config/routes/*.php
$routes->alias('poncho_admin_old_name', 'poncho_admin_new_name')
    ->deprecate('poncho/admin-bundle', '1.3', 'The "%alias_id%" route is deprecated, use "poncho_admin_new_name" instead.');
```

### Templates and blocks

```twig
{% deprecated 'The "@PonchoAdmin/old.html.twig" template is deprecated since poncho/admin-bundle 1.3, use "@PonchoAdmin/new.html.twig" instead.' %}
{% include '@PonchoAdmin/new.html.twig' %}
```

Keep the version inside the message: the tag's `package` and `version` arguments need Twig 3.11,
newer than the bundle requires.

A block cannot trigger a notice by itself. To rename one, have the new block call the old one, so
that application themes overriding the old name keep working, and document the rename in the
UPGRADE file.

### Translation keys

Keep the old key next to the new one until the next major. There is no runtime notice; the UPGRADE
file lists the renamed keys.

### JavaScript

Log once per page, and keep the old behaviour:

```js
if (!this.constructor.warned) {
    console.warn('poncho: "oldMethod()" is deprecated since poncho/admin-bundle 1.3, use "newMethod()" instead.')
    this.constructor.warned = true
}
```

A renamed JsResponse action stays registered under both names.

## Testing

Mark the test `@group legacy` and declare the expected notice:

```php
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

class FooTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @group legacy
     */
    public function testBarIsDeprecated(): void
    {
        $this->expectDeprecation('Since poncho/admin-bundle 1.3: Method "Foo::bar()" is deprecated, use "Foo::baz()" instead.');

        (new Foo())->bar();
    }
}
```

Note the prefix: `trigger_deprecation()` turns the package and version into
`Since poncho/admin-bundle 1.3: `.

## Removing deprecated code

Deprecated code is removed in the **next major** version's development branch, never before. The
pull request:

- removes the code, the notices and the legacy tests;
- adds an entry under `### Removed` in the CHANGELOG;
- adds the change to `UPGRADE-<major>.0.md` — every removal listed there, so that users upgrading
  can work through one list.
