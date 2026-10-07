# Configure security

```bash
php bin/console make:admin:security
```

It asks for the name of your user entity, then creates it (extending `BaseAdminUser`) and its
repository, and writes three new files of its own:

| File | Contents |
| --- | --- |
| `config/routes/poncho_admin_security.yaml` | Imports the login, profile and user-management routes under `/admin` |
| `config/packages/poncho_admin_security.yaml` | `poncho_admin.user.class`, a password hasher and a user provider |
| `config/packages/security.yaml` | An `admin` firewall and four `access_control` rules — merged in, see below |

The first two are files the maker owns entirely — re-running the command regenerates them, and
nothing you already had is ever read from or merged into either one. `security.yaml` is the one
exception: Symfony does not allow a firewall or an `access_control` rule to be defined in a second
file once another file already has one of its own, so that edit has to land in your existing file.
It is a real merge, not a replacement — whatever firewalls and `access_control` rules you already
had survive; the maker's own four rules are added ahead of them.

One fix is still needed afterwards: **firewall order.** The `admin` firewall is appended after
`main`, but Symfony uses the first firewall whose pattern matches — move it above `main`.

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

!> Before going to production, read [Security](security).
