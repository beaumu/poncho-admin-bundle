# Submitting a patch

Changes reach the bundle as **pull requests from a fork**, as in Symfony. Nobody pushes to this
repository directly — `main` is protected, maintainers included.

## Step 1: set up your environment

You need [Git](https://git-scm.com/), a [GitHub](https://github.com/) account and
[DDEV](https://ddev.com/). Nothing else: PHP, Composer, Node and Yarn run inside DDEV's containers,
at the versions the bundle supports.

1. **Fork** [beaumu/poncho-admin-bundle](https://github.com/beaumu/poncho-admin-bundle) on GitHub.
2. **Clone your fork** and add the original as `upstream`:

   ```bash
   git clone git@github.com:<your-username>/poncho-admin-bundle.git
   cd poncho-admin-bundle
   git remote add upstream https://github.com/beaumu/poncho-admin-bundle.git
   ```

3. **Start the environment** and install the dependencies:

   ```bash
   ddev start
   ddev composer install
   ddev exec yarn install
   ```

4. **Check that everything passes** before changing anything:

   ```bash
   ddev check
   ```

### Trying your change in an application

The bundle's own test suite boots a small application (`tests/App`), but most changes are easier to
judge in a real one. Clone your application **next to** the bundle and add a path repository to its
`composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../poncho-admin-bundle*",
            "options": {"versions": {"poncho/admin-bundle": "1.99.0"}}
        }
    ]
}
```

Composer then symlinks your checkout into `vendor/`, and every edit is live. The trailing `*` makes
the repository optional — without a sibling checkout Composer falls back to Packagist — and the
`versions` pin must satisfy the application's constraint on the bundle, or Composer silently
ignores the path repository. Confirm with `composer show poncho/admin-bundle`: the `dist` line
reads `[path]`. If the application runs in DDEV too, mount the sibling directory into its web
container.

## Step 2: choose the right branch

As in Symfony, **bug fixes** go to the **oldest maintained branch** that has the bug, and are merged
up into newer branches by the maintainers. **New features, deprecations and other changes** go to
the **development branch**.

| Branch | Receives | |
| --- | --- | --- |
| `main` | Features, deprecations, and bug fixes for the current major | Today, the only branch: 1.x is developed and maintained here |
| `1.x` | Bug fixes only | Created when work on 2.0 begins on `main` |

When in doubt, target `main` and say so in the pull request; a maintainer will ask you to rebase if
needed.

## Step 3: work on your patch

Create a **topic branch** from the up-to-date target branch:

```bash
git fetch upstream
git switch -c fix-tree-caret upstream/main
```

Name it after what it does. One topic branch holds one change: a fix and an unrelated clean-up are
two pull requests.

While you work:

- **Follow the [coding standards](contributing/standards).** `ddev fix-all` applies them.
- **Add tests.** A fix comes with a test that fails without it; a feature with tests of its
  behaviour. Run them with `ddev test`, or one file with
  `ddev test tests/Functional/DataTable/ColumnTest.php`.
- **Keep backward compatibility.** Read the [backward compatibility promise](contributing/bc) before
  changing a public class, a template, a config key or a JS element. To change or remove something,
  [deprecate](contributing/deprecations) it.
- **Don't reformat code you are not changing.** It hides the actual change in the diff.
- **Update the documentation** in `docs/` — see [Documentation](contributing/documentation).
- **Add a CHANGELOG entry** under `## [Unreleased]` in `CHANGELOG.md`, in the section that fits —
  `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security` — written for users: what changed
  for them, not how the code changed.
- **Deprecations and BC breaks** also get an entry in `UPGRADE-<next version>.md`, with before/after
  code. See [Deprecations](contributing/deprecations).

### Changes to the frontend

The compiled assets in `public/` are committed, so that the bundle works without a build step.
After changing `assets/`, rebuild them and commit the result **in a separate commit**:

```bash
ddev exec yarn build
```

After changing a translation file in `translations/`, regenerate the JavaScript translation
catalogue before building:

```bash
ddev exec bin/generate-translation
```

After changing `src/DependencyInjection/Configuration.php`, regenerate the configuration
reference:

```bash
ddev doc-update-config
```

### Commits

Write commits that each make sense on their own, with a message saying what and why:

```
Fix tree caret when tree_column_index is not 0

TreePlugin always inserted the caret into the first cell, so tables with
tree_column_index > 0 showed it in the wrong column.
```

Before submitting, bring your branch up to date by **rebasing** — not merging:

```bash
git fetch upstream
git rebase upstream/main
```

## Step 4: submit the pull request

```bash
ddev check
git push origin fix-tree-caret
```

`ddev check` fixes code style, then runs PHPStan and the tests — the same gates CI enforces.
Commit anything it fixed.

Open the pull request on GitHub, against the branch you chose in step 2.

**Title.** Prefix it with the part of the bundle it touches, in brackets, as Symfony does:

```
[DataTable] Fix tree caret when tree_column_index is not 0
```

Use `[DataTable]`, `[Form]`, `[Menu]`, `[JsResponse]`, `[Notification]`, `[User]`, `[Security]`,
`[Maker]`, `[Frontend]`, `[Translation]`, `[Docs]` or `[CI]`; several may be combined.

**Description.** The template starts with this table — fill in every row:

```markdown
| Q             | A
| ------------- | ---
| Branch?       | main
| Bug fix?      | yes/no
| New feature?  | yes/no
| Deprecations? | yes/no
| BC breaks?    | yes/no
| Issues        | Fix #123
| License       | MIT
```

Then explain the change: the problem, the solution, and anything a reviewer should look at closely.
For a visible change, add a screenshot.

**Work in progress?** Open it as a *draft* pull request.

### CI

Each pull request runs:

| Check | Local equivalent |
| --- | --- |
| PHPUnit on Symfony 6.4 and 7.4 | `ddev test` |
| PHPStan | `ddev analyse` |
| PHP CS Fixer | `ddev fix-php` |
| ESLint, when JavaScript changed | `ddev fix-js` |

CI fails on any deprecation triggered by the bundle's own code (`SYMFONY_DEPRECATIONS_HELPER:
max[self]=0`). To test the Symfony 6.4 leg locally:

```bash
ddev exec composer global config --no-plugins allow-plugins.symfony/flex true
ddev exec composer global require symfony/flex
ddev exec 'SYMFONY_REQUIRE="6.4.*" composer update'
ddev test
```

Flex must be installed globally for `SYMFONY_REQUIRE` to have any effect; without it Composer
silently resolves the latest Symfony. Run `ddev composer update` afterwards to return to the latest
versions — `composer.lock` is not committed.

## Step 5: follow up

A maintainer or another contributor reviews the pull request. Push new commits to the same branch to
address comments — the pull request updates itself. You may be asked to rebase or to squash commits
before the merge.

A pull request is merged when CI is green, the review is done and every row of the table holds.

## Reviewing

Reviews by other users are as valuable as code. To review a pull request:

- **Check out the branch** and try it, ideally in an application:
  `git fetch upstream pull/<number>/head:pr-<number>`.
- **Check the fix works**: reproduce the bug on `main`, then confirm it is gone on the branch.
- **Check the table**: tests present, CHANGELOG entry, documentation, no unannounced BC break.
- **Comment** with what you tested and how, and approve if everything holds. A review saying
  "works for me on Symfony 7.4 with a tree table" is useful.
