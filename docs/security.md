# Security

Read this before putting an admin built with Poncho into production. It lists the protections the
bundle provides, the ones it does not, and what to do about each.

To report a vulnerability, **do not open a public issue** — use GitHub's
[private security advisory form](https://github.com/beaumu/poncho-admin-bundle/security/advisories/new).
See [Security issues](contributing/security).

## What the bundle protects

- **Login** is Symfony's `form_login` with CSRF enabled; inactive users are refused.
- **Passwords** are hashed with Symfony's `auto` hasher. The plain password is never serialized into
  the session.
- **Password-reset links** use a selector plus a verifier stored only as a SHA-256 hash, compared in
  constant time, single-use, and expiring. Requesting one never reveals whether an address has an
  account. See [Password reset](user/password_reset).
- **Twig escapes by default**, and so do the table cells rendered through `render` and the badge
  column.

## CSRF on delete, move and bulk-action routes

`deleteLink()`, `moveUpLink()`, `moveDownLink()` and `moveLinks()` attach a CSRF token to the URL
automatically whenever the link is built with a `route` option and CSRF protection is enabled
(the default in any application using `symfony/security-bundle`). This covers the bundle's own
`poncho_admin_user_delete` route, and the `delete`/`move` actions `make:admin:table` and
`make:admin:tree` generate — both check the token before doing anything else. Nothing to do for
these if you generated your CRUD from the makers and did not remove the check.

**A route built by hand still needs the same two steps.** For an action you wired yourself —
around `ColumnActionBuilder::link()`, or outside a table entirely — add the token and verify it:

```php
// the table type — deleteLink()/moveLinks() do this for you; do the same for a plain link()
public function __construct(private readonly CsrfTokenManagerInterface $csrf)
{
}

// …in the ActionColumnType's build callable
$actions->link([
    'route' => 'mission_archive',
    'route_params' => [
        'id' => $mission->id,
        '_token' => $this->csrf->getToken(ColumnActionBuilder::csrfIntention('mission_archive', ['id' => $mission->id]))->getValue(),
    ],
]);
```

```php
// the controller
#[Route('/archive/{id}', requirements: ['id' => '\d+'])]
public function archive(Request $request, int $id): Response
{
    $intention = ColumnActionBuilder::csrfIntention('mission_archive', ['id' => $id]);
    if (!$this->isCsrfTokenValid($intention, $request->query->getString('_token'))) {
        throw $this->createAccessDeniedException('Invalid CSRF token.');
    }

    // …

    return $this->js()->reloadTable()->toastSuccess('Mission archived');
}
```

`ColumnActionBuilder::csrfIntention()` is a small static helper that turns a route (and, when
present, a row id) into a stable token id — use it on both sides so they agree.

**Bulk actions are not covered.** An action added with `addAction()` and `send_state: true` is
always sent as a `GET`, with no token, whatever type it is — including the built-in
`ButtonActionType`/`ButtonAddActionType`. Mitigate these, and anything you build outside the
`deleteLink()`/`moveLinks()` pair, with the `SameSite` cookie setting below.

**Defend everything else, including third-party bundles' own links, with one setting.** A
`SameSite=Strict` session cookie is not sent on any cross-site request, so an admin arriving from a
crafted link is simply not logged in:

```yaml
# config/packages/framework.yaml
framework:
    session:
        cookie_samesite: strict
```

The trade-off: following a legitimate link *into* the admin from another site, or from a mail client,
lands on the login page. For an admin interface that is usually acceptable. If you use remember-me,
set `samesite: strict` on it too.

Symfony's default, `lax`, is **not** enough: it still sends the cookie on top-level `GET`
navigations, which is exactly what a crafted link is.

## HTML that is not escaped

These accept **HTML**, and put it into the page as is. Never pass user-controlled data to them without
escaping it — `htmlspecialchars()` in PHP, `|e` in Twig:

| Where | |
| --- | --- |
| Toast text and title | `toast*()` on `AdminController` and `JsResponse` |
| Confirmation text | the `confirm` action option, `data-confirm`, `poncho.confirmModal` |
| Modal `content` | `@PonchoAdmin/lib/modal/default.html.twig` renders it with `\|raw` |
| `AdapterException` messages | shown in the table body |
| Column `render_html`, and column labels | labels are output with `\|raw` |
| `input_prefix`, `input_suffix`, `input_prefix_text`, `input_suffix_text` | form options |
| `RawActionType`, `ColumnActionBuilder::html()` | |
| `JsResponse::updateHtml()`, `modalHtml()`, `offcanvasHtml()` | |

For example:

```php
// vulnerable if the name came from a user
$this->toastSuccess("Mission {$mission->name} saved");

// safe
$this->toastSuccess(sprintf('Mission %s saved', htmlspecialchars($mission->name)));
```

The reset e-mail's own text — `password_resetting.email.body` — is inserted the same way: the whole
translated message carries `|raw`, since it legitimately contains the `<a href="…">` reset link.
The user's name and the app name are individually escaped before being substituted into it, so this
one is already safe; it's here as the pattern to follow for any translation of your own with the
same shape (real markup around a placeholder that can hold user data).

## `JsResponse::eval()`

Runs its argument as JavaScript in the admin's browser. Never build it from user input; use a
[custom action](component/jsresponse/client#custom-actions) instead.

## The profile page

The profile form changes the user's e-mail and password **without asking for the current password**.
Without a fresh-login requirement on it, someone with a borrowed or stolen remember-me session could
take over the account: change the e-mail, then request a reset.

`make:admin:security` now generates the fix — an `access_control` rule requiring a fresh login,
placed before the broader `^/admin` one:

```yaml
# config/packages/security.yaml
security:
    access_control:
        - { path: ^/admin/profile, roles: IS_AUTHENTICATED_FULLY }
        # …your other rules, with the broader ^/admin one last
```

If your `security.yaml` predates this, or you wrote it by hand, add that line yourself. Point
`poncho_admin.user.profile.form` at a form that asks for the current password, validated with
Symfony's `UserPassword` constraint, for defence in depth on top of it.

## Third-party requests

Every admin page loads one resource from outside your domain.

**Google Fonts** — the *Inter* font, from `_stylesheets.html.twig`. Replace the partial with one that
loads only the bundle's CSS, and serve the font yourself:

```twig
{# templates/bundles/PonchoAdminBundle/_stylesheets.html.twig #}
<link rel="stylesheet" href="{{ asset('poncho_admin.css', 'poncho_admin.assets.package') }}">
<link rel="stylesheet" href="{{ asset('fonts/inter.css') }}">
```

That request sends each visitor's IP address to a third party — relevant under the GDPR — and fails
under a strict `Content-Security-Policy` or without internet access.

## Checklist

- [ ] `framework.session.cookie_samesite: strict`
- [ ] CSRF tokens on any delete/move link you built by hand, and on bulk actions
- [ ] `IS_AUTHENTICATED_FULLY` on `/admin/profile`
- [ ] `password_reset_from_email` set to a domain you control
- [ ] User data escaped before it reaches a toast, a confirmation or raw HTML
- [ ] The Google Fonts request self-hosted, if you need a strict CSP or GDPR compliance
- [ ] Every `/admin` route covered by `access_control` — hiding a menu item protects nothing
