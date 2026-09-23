# Extending: users

The bundle's user screens, the login, the password reset and `poncho:create:user` all go through
one service: the **user manager**. Replace it to change how users are created, stored, deleted or
reset — audit logging, a different mail transport, soft deletes, an external directory.

For extra fields on the user itself, extend the entity — see [Users](user/index). For other screens,
see [Customising](user/index#customising).

## Extending the default manager

Subclass `UserManager` and override what you need. Its dependencies are `protected`, so they are
available to you: `$this->em`, `$this->repo`, `$this->mailer`, `$this->passwordHasher`,
`$this->translator`, `$this->config` and `$this->class` (your user class).

```php
namespace App\Security;

use Poncho\AdminBundle\Entity\BaseAdminUser;
use Poncho\AdminBundle\Service\UserManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Required;

class AuditedUserManager extends UserManager
{
    private LoggerInterface $logger;

    #[Required]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function save(BaseAdminUser $user): void
    {
        $isNew = null === $user->id;
        parent::save($user);

        $this->logger->info($isNew ? 'Admin user created' : 'Admin user updated', ['id' => $user->id]);
    }

    public function delete(BaseAdminUser $user): void
    {
        $id = $user->id;
        parent::delete($user);

        $this->logger->info('Admin user deleted', ['id' => $id]);
    }
}
```

The setter keeps the parent constructor untouched, so your class does not break when a later
version adds a dependency to it.

Then point the bundle at it:

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    user:
        manager: App\Security\AuditedUserManager
```

The value is a **service id**. With the default `App\` resource loading, a class's id is its class
name, so this works as is.

## Writing one from scratch

Implement `Poncho\AdminBundle\Service\UserManagerInterface`. Each method has a contract the bundle's
controllers rely on:

| Method | Called by | Must |
| --- | --- | --- |
| `create(): BaseAdminUser` | New-user form, `poncho:create:user` | Return a new, unsaved user of the configured class |
| `find(int $id): ?BaseAdminUser` | Edit and delete | Return `null` when not found — the controller turns it into a 404 |
| `updatePassword(BaseAdminUser $user)` | User form, profile, reset | If `plainPassword` is set: hash it into `password`, then call `erasePlainPassword()` and `erasePasswordReset()`. If it is empty, change nothing — the edit form leaves it empty to keep the password |
| `save(BaseAdminUser $user)` | After every form | Persist and flush |
| `delete(BaseAdminUser $user)` | Delete | Remove and flush |
| `sendResetPasswordEmail(string $email)` | Reset request | Create a reset token and send the link. Throw `ResetPasswordException` when there is no active user for the address |
| `validateResetPasswordTokenAndFetchUser(string $token): BaseAdminUser` | Reset link | Return the user the token belongs to, or throw `ResetPasswordException` if it is unknown, wrong or expired |

The reset controller treats a `ResetPasswordException` from `sendResetPasswordEmail()` as success
and shows the same "check your e-mail" page either way, so that the form never reveals which
addresses have accounts. Keep that property: do not reveal the failure any other way, such as a flash message or a log
visible to the user.

Whatever token format you use, the link in the e-mail must lead to the route
`poncho_admin_security_passwordreset` with the token as its `token` parameter; that route calls
`validateResetPasswordTokenAndFetchUser()` with it. See [Password reset](user/password_reset) for
how the default implementation keeps tokens safe — reuse the approach.

## Changing only the reset e-mail

For another layout or wording, you do not need a manager: override
`@PonchoAdmin/email/password_reset.html.twig` — see [Templates](twig#overriding-templates) — and the
translation keys under `password_resetting.email` — see [Translations](translations).

Override `sendResetPasswordEmail()` only to change *how* it is sent: another transport, a queue, or
a template name chosen per user.
