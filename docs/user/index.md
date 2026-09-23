# User management

Poncho ships a complete admin-user system: login and logout, password reset by e-mail, a profile
page, and a CRUD to manage the users themselves. `make:admin:security` wires it into your
application — see [Configure security](getting-started/configure_security).

## The user entity

Your admin user extends `Poncho\AdminBundle\Entity\BaseAdminUser`, a Doctrine mapped superclass:

```php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Poncho\AdminBundle\Entity\BaseAdminUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity]
#[UniqueEntity('email')]
class AdminUser extends BaseAdminUser
{
    public function getRoles(): array
    {
        return ['ROLE_ADMIN'];
    }
}
```

You provide `getRoles()`. Everything else comes from the base class:

| Property | Mapped as | |
| --- | --- | --- |
| `id` | `integer`, generated | |
| `email` | `string(60)`, **unique** | The user identifier used to log in |
| `password` | `string`, **not null** | The hash |
| `plainPassword` | not mapped | Set by forms; hashed by `UserManager::updatePassword()` |
| `firstname`, `lastname` | `string(255)`, nullable | `getFullName()` joins them |
| `active` | `boolean`, default `true` | Inactive users cannot log in or reset their password |
| `createdAt` | `datetime` | Set on construction |
| `passwordReset*` | four nullable columns | Used by the [password reset](user/password_reset) |

Only `id`, `password` and `email` are serialized into the session. Add your own fields to the
subclass as usual.

?> `email` is limited to **60 characters** by the mapping. Longer addresses fail at the database.

`eraseCredentials()` is deprecated since 1.2 — call `erasePlainPassword()`. See
[UPGRADE-1.2](https://github.com/beaumu/poncho-admin-bundle/blob/main/UPGRADE-1.2.md).

## Configuration

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    user:
        class: App\Entity\AdminUser
        manager: Poncho\AdminBundle\Service\UserManager
        table: Poncho\AdminBundle\DataTable\UserTableType
        form: Poncho\AdminBundle\Form\UserType
        password_reset_from_email: no-reply@example.com
        password_reset_from_name: 'Mission Control'
        password_reset_ttl: 86400
        profile:
            enabled: true
            route: poncho_admin_profile_index
            form: Poncho\AdminBundle\Form\ProfileType
```

| Option | Default | |
| --- | --- | --- |
| `class` | `App\Entity\AdminUser` | Your entity |
| `manager` | `…\Service\UserManager` | Service implementing `UserManagerInterface` |
| `table` | `…\DataTable\UserTableType` | Table of the user CRUD |
| `form` | `…\Form\UserType` | Form of the user CRUD |
| `password_reset_from_email` | `no-reply@poncho.dev` | Sender of reset e-mails. **Change it** — mail from a domain you don't control is likely to be rejected |
| `password_reset_from_name` | `''` | |
| `password_reset_ttl` | `86400` | Lifetime of a reset link, in seconds |
| `profile.enabled` | `true` | Register the profile page |
| `profile.route` | `poncho_admin_profile_index` | Route the user menu links to |
| `profile.form` | `…\Form\ProfileType` | Form of the profile page |

!> `user.enabled` has no effect: the user services are always registered, whatever its value. It is
kept only so existing configuration stays valid.

## Routes

Import the ones you use:

```yaml
# config/routes.yaml
poncho_admin_security_:
    resource: '@PonchoAdminBundle/config/routes/security.php'
    prefix: /admin
poncho_admin_user_:
    resource: '@PonchoAdminBundle/config/routes/user.php'
    prefix: /admin
poncho_admin_profile_:
    resource: '@PonchoAdminBundle/config/routes/profile.php'
    prefix: /admin
```

| Route | Path | |
| --- | --- | --- |
| `poncho_admin_login` | `/login` | |
| `poncho_admin_logout` | `/logout` | `GET`; handled by the firewall |
| `poncho_admin_security_passwordresetrequest` | `/password-reset` | |
| `poncho_admin_security_passwordresetcheckemail` | `/password-reset/check-email` | |
| `poncho_admin_security_passwordreset` | `/password-reset/{token}` | |
| `poncho_admin_user_index` | `/user` | The user table |
| `poncho_admin_user_edit` | `/user/edit/{id}` | Add (no `id`) or edit, in a modal |
| `poncho_admin_user_delete` | `/user/delete/{id}` | |
| `poncho_admin_profile_index` | `/profile` | |

!> `poncho_admin_user_delete` accepts any HTTP method and checks no CSRF token. Restrict access to it
until that is fixed — see [Security](security#csrf-on-delete-routes).

Link the CRUD from your menu:

```php
$builder->root()->add('users')->icon('mdi mdi-account-group')->route('poncho_admin_user_index');
```

## The firewall

`make:admin:security` adds this to `config/packages/security.yaml`:

```yaml
security:
    password_hashers:
        App\Entity\AdminUser: auto
    providers:
        admin_entity_provider:
            entity:
                class: App\Entity\AdminUser
                property: email
    firewalls:
        admin:
            pattern: ^/admin
            lazy: true
            provider: admin_entity_provider
            user_checker: Poncho\AdminBundle\Security\UserChecker
            entry_point: Poncho\AdminBundle\Security\AuthenticationEntryPoint
            form_login:
                login_path: poncho_admin_login
                check_path: poncho_admin_login
                default_target_path: app_admin_home_index
                enable_csrf: true
            logout:
                path: poncho_admin_logout
                target: poncho_admin_login
    access_control:
        - { path: ^/admin/login$, roles: PUBLIC_ACCESS }
        - { path: ^/admin/password_request, roles: PUBLIC_ACCESS }
        - { path: ^/admin/password_reset, roles: PUBLIC_ACCESS }
        - { path: ^/admin, roles: ROLE_ADMIN }
```

!> **Known issue: the generated `access_control` locks out password reset.** The two
`password_` rules use underscores, but the routes are `/admin/password-reset…` with a hyphen, so
they fall through to `^/admin` and require `ROLE_ADMIN` — a logged-out user asking for a reset, or
following the e-mailed link, is sent back to the login page. Replace those two lines with:
```yaml
        - { path: ^/admin/password-reset, roles: PUBLIC_ACCESS }
```

- **`UserChecker`** refuses inactive users with *Account is disabled.*
- **`AuthenticationEntryPoint`** redirects anonymous visitors to the login page — except XHR
  requests, which get a `401` so the page shows a toast instead of loading the login form into a
  modal.
- **`default_target_path`** is `app_admin_home_index`, the route `make:admin:home` creates. Run that
  maker too, or change it.

The `admin` firewall must come **before** `main`: Symfony uses the first firewall whose pattern
matches, and the maker appends it at the end.

!> The maker **replaces** your whole `access_control` list rather than adding to it. If your
application already had rules, restore them afterwards — `git diff config/packages/security.yaml`
shows what was lost.

## The first user

```bash
php bin/console poncho_admin:create:user
```

asks for a first name, last name, e-mail and password.

!> **Known issue:** in 1.1 this command fails with *Column 'password' cannot be null* — it stores the
user without hashing the password. Until it is fixed, hash the password with Symfony and insert the
row yourself:
```bash
php bin/console security:hash-password 'your-password' 'App\Entity\AdminUser'
php bin/console dbal:run-sql "INSERT INTO admin_user (email, password, active, created_at) VALUES ('you@example.com', '<the hash>', 1, NOW())"
```
Adjust the table name to your entity's.

## Customising

**The CRUD.** Point `user.table` and `user.form` at your own classes. Extending the built-in ones is
the easiest start:

```php
class AdminUserTableType extends UserTableType
{
    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        parent::buildTable($builder, $options);
        $builder->add('department');
    }
}
```

Your `user.form` receives the option `password_required`, set to `true` when creating a user — keep
declaring it.

**The profile page.** Point `user.profile.form` at your own form. Its data is the logged-in user.

**The manager.** See [Extending: users](extending/user).

**Templates.** `@PonchoAdmin/security/*`, `@PonchoAdmin/profile/index.html.twig`,
`@PonchoAdmin/user/edit.html.twig` and `@PonchoAdmin/email/password_reset.html.twig` can all be
overridden. See [Templates](twig#overriding-templates).
