# Releases

This page is for maintainers.

## Branches and tags

- `main` is protected: every change reaches it through a reviewed pull request, maintainers'
  included.
- Contributors work in their **forks**. A branch pushed to this repository shows up on Packagist as
  `dev-<branch>`; keep topic branches out of it.
- A release is a **tag** `vX.Y.Z` on the branch it belongs to. Packagist picks it up through its
  GitHub webhook; nothing is published to npm.
- When work on a new major starts on `main`, the previous major gets a maintenance branch — `1.x` —
  from its last release. Bug fixes then land on `1.x` and are merged up into `main` by a maintainer
  (`git merge 1.x`, resolving conflicts in a pull request).

## Before a release

Through a pull request to the release branch:

1. In `CHANGELOG.md`, turn `## [Unreleased]` into `## [X.Y.Z] - YYYY-MM-DD`, add a new empty
   `## [Unreleased]` above it, and update the compare links at the bottom.
2. For a minor or major version, check that `UPGRADE-X.Y.md` covers every deprecation and break
   listed in the CHANGELOG.
3. If `assets/` changed, check the compiled assets in `public/` are up to date: `ddev exec yarn build`
   must produce no diff.
4. If the configuration changed, `ddev doc-update-config` must produce no diff.

Merge it once CI is green on every leg.

## Tagging

Run the **Release** workflow from GitHub: *Actions → Release → Run workflow*, choose the branch and
the kind of version — `patch`, `minor` or `major`.

It runs `bin/create-version.sh`, which takes the latest tag reachable from the branch, increases
the chosen part, and pushes the new annotated tag. It pushes the tag only, never a branch, so it
works with `main` protected.

| Changes since the last release | Version |
| --- | --- |
| Only bug and security fixes | `patch` |
| New features or deprecations | `minor` |
| Removals or other BC breaks | `major` |

## After tagging

1. Check that Packagist lists the new version.
2. Create a GitHub release from the tag, with the version's CHANGELOG section as its notes.
3. For a fix on a maintenance branch, merge it up into `main`.
