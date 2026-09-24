# Configure security

```bash
php bin/console make:admin:security
```

It asks for the name of your user entity, then:

- creates the entity, extending `BaseAdminUser`, and its repository,
- sets `poncho_admin.user.class`,
- imports the login, profile and user-management routes under `/admin`,
- adds a password hasher, a user provider, an `admin` firewall and `access_control` rules to
  `config/packages/security.yaml`.

Two fixes are needed afterwards.

**1. Firewall order.** The `admin` firewall is appended after `main`, but Symfony uses the first
firewall that matches. Move it above `main`.

**2. Your existing rules.** The maker replaces the whole `access_control` list. If you had rules,
restore them.

Then update the schema:

```bash
php bin/console cache:clear
php bin/console doctrine:schema:update --force
```

## The first user

```bash
php bin/console poncho_admin:create:user
```

Log in at `/admin/login`. Any `/admin` page now requires authentication.

## Managing users

Add the user table to your menu:

```php
$builder->root()
    ->add('users')
        ->icon('mdi mdi-account-group')
        ->route('poncho_admin_user_index');
```

Everything about users — the entity, the options, the CRUD, the profile page — is under
[User management](user/index).

!> Before going to production, read [Security](security): the built-in delete route has no CSRF
protection.
