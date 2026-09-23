# Password reset

A logged-out user can ask for a reset link by e-mail from the login page's *Forgot password?* link.

!> In an application configured by `make:admin:security`, the reset pages are unreachable for
logged-out users until you fix the generated `access_control`. See
[The firewall](user/index#the-firewall).

## The flow

1. `/password-reset` — the user enters an e-mail address.
2. If it belongs to an **active** user, a link is e-mailed. Either way the user lands on
   `/password-reset/check-email`, so the form never reveals whether an address has an account.
3. `/password-reset/{token}` — the link opens a form to choose a new password.
4. On success the token is cleared, a toast confirms, and the user is sent to the login page.

An invalid, expired or already-used link shows `@PonchoAdmin/security/password_reset_error.html.twig`.

## How the token is protected

The link carries a 56-character token made of two parts:

- a **selector** — 24 hex characters from 12 random bytes — stored as-is and used to find the user,
- a **verifier** — 16 random bytes — of which only a SHA-256 hash is stored.

Validation looks the user up by selector, then compares the hash of the verifier with
`hash_equals()`. So a leaked database does not yield usable links, and comparing tokens leaks
nothing through timing. The link expires after `poncho_admin.user.password_reset_ttl` seconds
(24 hours by default) and stops working once used or once the password changes.

## Sending the e-mail

It goes through Symfony Mailer, so a working `MAILER_DSN` is required:

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    user:
        password_reset_from_email: no-reply@your-domain.example
        password_reset_from_name: 'Mission Control'
        password_reset_ttl: 3600
```

!> Change `password_reset_from_email`. The default, `no-reply@poncho.dev`, is a domain you don't
control; mail claiming to come from it is likely to be rejected or filed as spam.

The subject is the translation `password_resetting.email.subject` and the body
`password_resetting.email.body`, both in the `PonchoAdmin` domain. The body receives `%name%`,
`%app_name%` and `%reset_url%`, and is rendered as HTML.

### The template

`@PonchoAdmin/email/password_reset.html.twig` extends `@PonchoAdmin/email/layout.html.twig`, which
has the blocks `baseHref`, `preheader`, `header`, `main`, `content` and `footer`. Its variables are
`user` and `token`.

!> The layout's logo is `asset(poncho_admin.appLogo())`: a relative URL, which e-mail clients cannot
resolve, and typically an SVG, which Gmail and Outlook do not display. Override the `header` block
with an absolute URL to a PNG:
```twig
{% block header %}
    <img src="{{ absolute_url(asset('images/logo-email.png')) }}" width="50" alt="">
{% endblock %}
```

## Driving it yourself

`UserManagerInterface` exposes both halves, for a custom flow or an API:

```php
use Poncho\AdminBundle\Exception\ResetPasswordException;

try {
    $userManager->sendResetPasswordEmail('ada@example.com');
} catch (ResetPasswordException) {
    // no active user with that address — report success anyway
}

try {
    $user = $userManager->validateResetPasswordTokenAndFetchUser($token);
} catch (ResetPasswordException $e) {
    // $e->getMessage(): invalid_password_reset_token or expired_password_reset_token
}
```

Then set `$user->plainPassword`, call `updatePassword($user)` — which hashes it and clears the
reset token — and `save($user)`.
