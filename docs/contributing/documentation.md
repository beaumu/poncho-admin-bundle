# Documentation

This documentation lives in the bundle's repository, in `docs/`, and is contributed exactly like
code: fork, topic branch, pull request — see [Submitting a patch](contributing/pull_requests). Use
`[Docs]` as the title prefix. A pull request that changes behaviour updates the documentation in
the same pull request.

## Previewing

The site is built with [docsify](https://docsify.js.org/) straight from the Markdown files — there is
no build step.

```bash
ddev doc
```

serves it at the address the command prints; pages reload as you save.

## Where things go

| Directory | Contents |
| --- | --- |
| `getting-started/` | The tutorial, in order. Each page continues the previous one |
| `concepts/` | How the bundle is designed |
| `component/<name>/` | Reference for one component: every option, method and behaviour |
| `extending/` | How to build new things on top of the bundle |
| `user/`, `frontend/` | Reference for those areas |
| `config/` | The configuration reference, **generated** — see below |
| `contributing/` | This guide |

A new page must also be added to `_sidebar.md`.

## Writing

- **Document what the code does**, verified against it — not what it was meant to do. When the
  two differ, the documentation describes the actual behaviour and flags it as a known issue (see
  below), and an issue or a fix is opened for the bug.
- **Complete examples.** Show the `use` statements and the file a snippet goes in (as a comment
  on its first line: `# config/packages/poncho_admin.yaml`). An example must run as written — try it.
- **Tables for reference**, prose for explanation. An option table has the columns *Option*, *Type*,
  *Default* and a description.
- **Plain, direct language**, in English, second person ("you"). Short sentences.
  No "simply", "just" or "obviously".
- **Name things exactly**: class names, option names and routes in `code`, as they appear in the
  code.

### Links

Links are relative to the `docs/` root, **without** the `.md` extension:

```markdown
See [Adapters](component/datatable/adapters#errors).
```

Anchors are the heading in lower case, spaces replaced by `-`, punctuation dropped.

### Callouts

```markdown
!> A warning: a known issue, a security consequence, something that fails silently.

?> A tip.
```

Use them sparingly. A **known issue** is a `!>` callout saying what goes wrong, when, and the
workaround. When a fix is merged, its pull request removes the callout.

## The configuration reference

`config/poncho_admin.md` ends with a YAML dump of `Configuration.php`. Never edit the dump by hand:
after changing the configuration tree, run

```bash
ddev doc-update-config
```

and commit the result.

The script replaces everything from the page's **first** ` ```yaml ` fence to the end of the file.
Write the explanations above the dump, and fence YAML examples there as ` ```yml `, or the next
run deletes everything after them.

## Changelog and upgrade notes

`CHANGELOG.md` and the `UPGRADE-*.md` files are in the repository root, not in `docs/`. See
[Submitting a patch](contributing/pull_requests#step-3-work-on-your-patch) for when to update them.
