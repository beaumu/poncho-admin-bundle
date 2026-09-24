# Reporting a bug

A good report gets a bug fixed quickly. Before writing one:

1. **Check it is a bug.** Re-read the relevant page of this documentation — several pages list
   *known issues* already.
2. **Check the latest version.** Update to the latest release of the branch you use; the bug may be
   fixed. See the [CHANGELOG](https://github.com/beaumu/poncho-admin-bundle/blob/main/CHANGELOG.md).
3. **Search the existing [issues](https://github.com/beaumu/poncho-admin-bundle/issues?q=is%3Aissue)**,
   closed ones included.

!> A security vulnerability is **never** reported in a public issue. See [Security issues](contributing/security).

## Writing the report

Use the [bug report form](https://github.com/beaumu/poncho-admin-bundle/issues/new?template=1_Bug_report.yaml).
It asks for:

- **The version affected** — the exact one: `composer show poncho/admin-bundle | grep versions`.
- **A description** — what you did, what you expected, and what happened instead. Copy error messages
  and stack traces as text, not screenshots.
- **How to reproduce** — the smallest set of steps that shows the problem. Include your PHP and
  Symfony versions, and the code involved: the table type, form type or controller, reduced to what
  still triggers the bug.

The best reproducer is a **small repository**: a fresh `symfony new` project with the bundle
installed and one commit that shows the problem. Symfony asks for the same, and for the same reason
— a maintainer can clone and run it in a minute, instead of guessing at your setup.

- **A possible solution**, if you have one. Better still, a pull request — see
  [Submitting a patch](contributing/pull_requests).

For JavaScript bugs, also give the browser and its version, and the console output.

## After reporting

A maintainer may ask for more information; an issue without an answer for a long time may be closed.
If you can fix the bug yourself, say so in the issue so no one duplicates the work.
