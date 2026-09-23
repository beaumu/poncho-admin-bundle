# Changelog

All notable changes to this project are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

For a bundle, the public API includes more than PHP classes: Twig template and
block names, configuration keys under `poncho_admin`, service IDs, route names
and the CSS hooks the templates emit are all things consumers override, so
changes to them are treated as breaking.

## [Unreleased]

### Deprecated
- `BaseAdminUser::eraseCredentials()` is deprecated; use the new
  `erasePlainPassword()`. The method now carries `#[\Deprecated]`, which is how
  Symfony 7.3+ recognises that the logic lives elsewhere and stops calling it
  after login. `plainPassword` was already excluded from `__serialize()`, so it
  never reached the session. See [UPGRADE-1.2.md](UPGRADE-1.2.md).

### Added
- `BaseAdminUser::erasePlainPassword()`.

### Changed
- `UserManager::updatePassword()` calls `erasePlainPassword()` instead of
  `eraseCredentials()`.
- Transitive dependencies with known advisories are forced to patched releases
  via `resolutions` (`axios`, `tar`, `nth-check`); build tooling upgraded. All
  critical advisories in the lockfile are cleared.

### Fixed
- The Symfony 6.4 test leg no longer fails to boot. 1.1.0 set
  `framework.property_info.with_constructor_extractor`, an option that only
  exists since Symfony 7.3; it is now applied conditionally. The five framework
  options Symfony 6.4 deprecates leaving unset are pinned to their 7.0 defaults.
  Both CI legs now run with zero deprecations.
- The PHPUnit workflow also runs when `tests/`, `config/`, `templates/`,
  `translations/`, `phpunit.xml.dist` or the workflow itself change — the 6.4
  breakage above went unnoticed because it touched none of `src/` or
  `composer.json`.
- CSS minification no longer emits an `SvgoParserError` warning on Bootstrap's
  percent-encoded inline icons.

## [1.1.0] - 2026-09-16

### Added
- `package.json` is now a real, resolvable package (`poncho-admin-bundle`) with
  `exports`, `files` and `peerDependencies`, so an application can compile the
  theme with its own Webpack Encore instead of using the prebuilt assets. It
  deliberately carries no `version`: the package is consumed through
  `file:vendor/poncho/admin-bundle` rather than published to npm, so the
  Composer tag stays the single source of truth and cannot drift.
- Third-party notices in `LICENSE.md`, including the AdminKit attribution the
  vendored theme requires.
- This changelog.

### Changed
- SCSS imports no longer use the webpack-only `~` prefix (48 occurrences). The
  stylesheets now compile with the plain Dart Sass CLI, Vite or any other
  toolchain, given `node_modules` on the load path.

### Fixed
- Test suite no longer emits avoidable deprecations: a schema manager factory
  and savepoints are configured, `report_fields_where_declared` was dropped, the
  `property_info` extractor option is pinned, and the redundant
  `doctrine:database:create` call was removed. 14 notices down to 1.
- CI no longer hides deprecations. `SYMFONY_DEPRECATIONS_HELPER` is now
  `max[self]=0`, so a deprecation triggered by this bundle's own code fails the
  build.

## [1.0.2] - 2026-09-14

### Fixed
- `UserChecker::checkPostAuth()` now accepts the `?TokenInterface $token`
  argument that `UserCheckerInterface` requires in the next Symfony major.
- A DataTable request without a `state` parameter returns 400 Bad Request
  instead of 500. `DataTableActionState::createFromRequest()` now throws
  `BadRequestHttpException` rather than `\InvalidArgumentException`.
- `BaseAdminUser::isPasswordResetExpired()` no longer fatals when no password
  reset is in progress; it returns `true` instead of dereferencing null.
- Password reset error handling.

## [1.0.1] - 2026-09-14

### Fixed
- JS response handling, and action types are configured automatically.

### Changed
- CI tests Symfony 7.4 instead of the end-of-life 7.2, which can no longer
  resolve `symfony/cache` because of CVE-2026-45073.

## [1.0.0] - 2026-09-14

First release under the Poncho name, forked from
[Umbrella Admin Bundle](https://github.com/acantepie/umbrella-admin-bundle).

### Added
- Light and dark logo variants: `app_logo_inverse` falls back to `app_logo`
  when unset, so the sidebar and login page can use different marks.

### Changed
- Renamed throughout: package `poncho/admin-bundle`, namespace
  `Poncho\AdminBundle\`, config key `poncho_admin`, routes `poncho_admin_*`,
  Twig namespace `@PonchoAdmin`, and the `poncho_form_theme()` function.
- `symfony/password-hasher`, `symfony/security-core`, `symfony/security-http`
  and `symfony/var-exporter` are required explicitly rather than relied on
  transitively.

[Unreleased]: https://github.com/beaumu/poncho-admin-bundle/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/beaumu/poncho-admin-bundle/compare/v1.0.2...v1.1.0
[1.0.2]: https://github.com/beaumu/poncho-admin-bundle/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/beaumu/poncho-admin-bundle/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/beaumu/poncho-admin-bundle/releases/tag/v1.0.0
