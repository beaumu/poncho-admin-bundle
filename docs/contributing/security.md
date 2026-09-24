# Security issues

**Never report a security issue in a public GitHub issue, pull request or discussion.** Until a fix
is released, every application using the bundle is exposed.

## Reporting

Report it privately through GitHub's
[security advisory form](https://github.com/beaumu/poncho-admin-bundle/security/advisories/new).
Include:

- the affected versions;
- what an attacker can do, and what they need first — an account, an admin session, a user who
  follows a link;
- the steps to reproduce, ideally a proof of concept.

Only the maintainers see the report.

## What happens next

As in Symfony's [security process](https://symfony.com/doc/current/contributing/code/security.html):

1. A maintainer acknowledges the report and confirms whether it is a vulnerability.
2. The fix is prepared **privately**, in the advisory's temporary fork, with your help if you
   want — not in a public pull request.
3. A patch release is published for every maintained branch, together with the advisory and a
   CHANGELOG entry under `### Security`. A CVE is requested through GitHub when warranted.
4. You are credited in the advisory, unless you prefer not to be.

Please keep the issue confidential until the advisory is published.

## Not a vulnerability

Some behaviour is documented as the application's responsibility — escaping data passed to options
that accept HTML, protecting its own routes with `access_control`, CSRF tokens on its own
actions. See [Security](security). A report that the bundle makes a secure setup *hard*, or that a
default is unsafe, is still welcome — through the same form when in doubt.
