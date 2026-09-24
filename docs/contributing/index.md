# Contributing

Poncho Admin Bundle is open source, and contributions are welcome: bug reports, fixes, features,
documentation, and reviews of other people's pull requests.

The process deliberately mirrors [Symfony's](https://symfony.com/doc/current/contributing/index.html).
If you have contributed to Symfony, you already know it: fork, topic branch, pull request, a
filled-in PR table, tests, a changelog entry, and a backward compatibility promise that decides what
may change when.

## Ways to contribute

| | |
| --- | --- |
| **Report a bug** | [Reporting a bug](contributing/bugs) |
| **Propose a feature** | Open a [feature request](https://github.com/beaumu/poncho-admin-bundle/issues/new?template=2_Feature_request.yaml) first, so the idea can be discussed before you write code |
| **Submit a fix or a feature** | [Submitting a patch](contributing/pull_requests) |
| **Improve the documentation** | [Documentation](contributing/documentation) |
| **Review pull requests** | Try a branch, comment on the approach, confirm a fix works for you — see [Reviewing](contributing/pull_requests#reviewing) |
| **Report a vulnerability** | Privately — see [Security issues](contributing/security). Never in a public issue |

## The rules, in short

- One pull request per change, from a **topic branch** of your **fork**, against the right
  [branch](contributing/pull_requests#step-2-choose-the-right-branch).
- Every change comes with **tests** and, when it changes behaviour, a **CHANGELOG entry** and
  **documentation**.
- The code follows the [coding standards](contributing/standards); `ddev check` must pass.
- Nothing that users rely on breaks outside a major version — see the
  [backward compatibility promise](contributing/bc). Things are
  [deprecated](contributing/deprecations) first, and removed in the next major.

## Reference

- [Reporting a bug](contributing/bugs)
- [Submitting a patch](contributing/pull_requests)
- [Coding standards](contributing/standards)
- [Backward compatibility promise](contributing/bc)
- [Deprecations](contributing/deprecations)
- [Documentation](contributing/documentation)
- [Security issues](contributing/security)
- [Releases](contributing/releases) — for maintainers

## License

By contributing, you agree that your contribution is licensed under the
[MIT license](https://github.com/beaumu/poncho-admin-bundle/blob/main/LICENSE.md), the bundle's.
