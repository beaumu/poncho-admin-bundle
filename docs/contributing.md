Contribution Guidelines
=======================

Thank you for considering contributing to this project :)

Requirements
------------

Before your first contribution, make sure you'll meet these requirements:

* You have a user account on [GitHub](https://github.com/).
* You have [DDEV](https://ddev.com/) installed, and have started the project once (`ddev start`).
* After cloning, run `git config core.hooksPath .githooks` once — this enables a local
  pre-commit hook that runs php-cs-fixer/eslint on staged files via DDEV.

Proposing New Features
----------------------
[Create a feature request][propose-feature]

Reporting Bugs
--------------
[Create an issue][create-issue] (If the bug hasn't been reported yet).

Sending Pull Requests
---------------------
This project follows the same contribution workflow used by the Symfony project.
First you must clone the repository, then create a feature branch and finally,
submit a pull request via GitHub.

To check your code before submit you can run `ddev check` (or individually: `ddev test`, `ddev analyse`, `ddev fix-all`).

Read the [Symfony contribution guide][sf-contribution] for more details.

Further information
-------------------

* [General GitHub documentation][gh-help]
* [GitHub pull request documentation][gh-pr]

[gh-help]: https://help.github.com
[gh-pr]: https://help.github.com/send-pull-requests
[poncho-issues]: https://github.com/beaumu/poncho-admin-bundle/labels/Bug
[create-issue]: https://github.com/beaumu/poncho-admin-bundle/issues/new?assignees=&labels=Bug&template=1_Bug_report.yaml
[propose-feature]: https://github.com/beaumu/poncho-admin-bundle/issues/new?assignees=&labels=Feature&template=2_Feature_request.yaml
[sf-contribution]: http://symfony.com/doc/current/contributing/documentation/overview.html#your-first-documentation-contribution