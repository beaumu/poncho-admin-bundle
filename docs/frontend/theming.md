# Theming

Recompile the bundle's stylesheet with your own colours, fonts and spacing, while keeping its
prebuilt JavaScript.

This needs Webpack Encore. The bundle's Sass sources are in `vendor/poncho/admin-bundle/assets/scss/`,
delivered by Composer; npm only supplies the libraries they import.

## 1. Install the libraries

```bash
npm install --save-dev bootstrap@^5.3.3 @mdi/font@^7.4.47 @iconscout/unicons@^4.2.0 \
    tom-select@^2.4.3 flatpickr@^4.6.13 datatables.net-bs5@^2.2.2 \
    simplebar@^6.3.0 toastify-js@^1.12.0 dragula@^3.7.3 sass sass-loader
```

These are the ones the stylesheet imports. Keep them within the ranges above: they are the bundle's
`peerDependencies`, and a different major version of Bootstrap in particular will not compile.

## 2. Link the bundle's sources

```json
{
    "devDependencies": {
        "poncho-admin-bundle": "file:vendor/poncho/admin-bundle"
    }
}
```

This is a symlink into `vendor/`, not a download: the sources always match the installed bundle
version, and nothing is published to npm.

## 3. Write your theme

```scss
// assets/styles/admin_theme.scss

// Your overrides come first: the theme's variables are declared !default, so they yield.
$primary: #e30613;
$sidebar-bg: #111111;
$font-family-sans-serif: 'Source Sans 3', system-ui, sans-serif;

@import 'poncho-admin-bundle/scss/admin';
```

Every variable is listed in `vendor/poncho/admin-bundle/assets/scss/variables/_app.scss` — Bootstrap's
own (`$primary`, `$body-bg`, `$border-radius`, `$spacer`, …) and the theme's (`$sidebar-bg`,
`$sidebar-link-color`, …). Anything defined above the `@import` wins.

## 4. Build it

```js
// webpack.config.js
Encore
    .addStyleEntry('admin_theme', './assets/styles/admin_theme.scss')
    .enableSassLoader((options) => {
        options.sassOptions = {
            quietDeps: true,
            // The theme is built on Bootstrap 5, whose Sass still uses @import.
            silenceDeprecations: ['import'],
        }
    })
```

## 5. Load it instead of the bundle's CSS

```twig
{# templates/bundles/PonchoAdminBundle/_stylesheets.html.twig #}
{{ encore_entry_link_tags('admin_theme') }}
```

Leave `_scripts.html.twig` alone: the prebuilt JavaScript contains no CSS, so it works with any
stylesheet.

The original partial also loads the *Inter* font from Google. Your override drops it — add your own
font here, ideally self-hosted.

## Why not rebuild the JavaScript too

The bundle's JavaScript entry, `assets/admin.js`, imports the **default** stylesheet itself. Bundling
it into your own entry therefore ships the default theme alongside yours. It also relies on a
global `$`, which means calling `Encore.autoProvidejQuery()`. Keep the prebuilt script unless you
need to change the JavaScript itself.

## With another toolchain

Any Sass compiler works if `node_modules` is on its load path and it can find the bundle's sources.
With the Dart Sass CLI, point straight at `vendor/`:

```bash
npx sass --load-path=node_modules --load-path=vendor/poncho/admin-bundle/assets/scss \
    --silence-deprecation=import --quiet-deps \
    assets/styles/admin_theme.scss public/css/admin_theme.css
```

and `@import 'admin';` in your theme file.

?> JavaScript imports through the package name must be **fully specified**:
`poncho-admin-bundle/assets/utils/AjaxUtils.js` resolves, `…/AjaxUtils` does not.
