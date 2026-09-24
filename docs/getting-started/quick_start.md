# Quick Start

## Technical requirements

- PHP 8.2 or higher
- PHP extensions: `json`, `mbstring`, `xml`
- Symfony 6.4 or 7.x
- [Composer](https://getcomposer.org/)

If you plan to use Poncho on a new project, [create a new Symfony app first](https://symfony.com/doc/current/setup.html#creating-symfony-applications):

```bash
composer create-project symfony/skeleton:"7.2.x" my_project_directory
cd my_project_directory
composer require webapp
```

## 1. Install the package

```bash
composer require poncho/admin-bundle
```

To track the development branch instead of a release:

```bash
composer require poncho/admin-bundle:"dev-main"
```

## 2. Register the bundle

Poncho has no [Symfony Flex](https://symfony.com/doc/current/setup/flex.html) recipe yet, so
Composer will **not** register it for you. Add it to `config/bundles.php` by hand:

```php
// config/bundles.php
return [
    // ...
    Poncho\AdminBundle\PonchoAdminBundle::class => ['all' => true],
];
```

Without this line the bundle is installed but never loaded: its services and the
`make:admin:*` commands will not exist, and the next steps will fail with
*"There are no commands defined in the make:admin namespace"*.

## 3. Publish the assets

The bundle ships prebuilt CSS and JavaScript, which have to be linked into `public/`:

```bash
php bin/console assets:install public
```

Applications created with `symfony/skeleton` run this automatically on every
`composer install` via the `auto-scripts` block in `composer.json`, so you can usually skip it.

There is nothing to build and no Node toolchain required. Recompiling the theme yourself —
to override colours or the layout — is optional and documented separately.

## 4. Create your first page

```bash
php bin/console make:admin:home
```

This generates an admin controller, a Twig template and a menu class. See
[Create your first page](getting-started/create_home) for what it produces.

## 5. Add authentication

```bash
php bin/console make:admin:security
```

This creates an `AdminUser` entity, imports the bundle's login, profile and user-management
routes, and configures a firewall. Two follow-ups are required:

1. In `config/packages/security.yaml`, move the generated `admin` firewall **above** `main`.
2. Update your database schema and create a first account:

```bash
php bin/console doctrine:schema:update --force
php bin/console poncho_admin:create:user
```

See [Configure security](getting-started/configure_security) for details.

## Where to go next

- [Create your first CRUD](getting-started/crud)
- [Configuration reference](config/poncho_admin)
