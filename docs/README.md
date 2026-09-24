<h1 align="center" style="border-bottom: none">
    <img src="./poncho-black.svg" width="25" height="25" alt="Poncho logo"/> Poncho Admin Bundle
</h1>

> Easiest way to create beautiful administration backends with Symfony.
>
> Fork of [Umbrella Admin Bundle](https://github.com/acantepie/umbrella-admin-bundle) by Adrien Cantepie.

## What's included <!-- {docsify-ignore} -->

- **Components** to build admin pages: [DataTables](component/datatable/index) with filters, actions,
  selection and trees; [form types](component/form/types) and a Bootstrap 5
  [form theme](component/form/theme); a [menu](component/menu/quick_start) with breadcrumbs; and
  [JsResponse](component/jsresponse/index), which lets a controller drive the page — modals, toasts,
  table reloads — without writing JavaScript.
- **Ready-made features**: [admin users](user/index) with login, [password reset](user/password_reset)
  and a profile page, and a [notification bell](component/notification).
- **A theme** based on Bootstrap 5 and AdminKit, working out of the box, which you can
  [recompile with your own colours](frontend/theming).
- **Makers** that generate a [CRUD in one command](makers).

Requires PHP 8.2+ and Symfony 6.4 or 7.x.

## Where to start <!-- {docsify-ignore} -->

| | |
| --- | --- |
| New to the bundle | [Quick start](getting-started/quick_start), then the rest of *Getting started* in order |
| Want to understand how it fits together | [Architecture](concepts/architecture) |
| Looking up an option or a method | The *Components* section, and the [configuration reference](config/poncho_admin) |
| Need something the bundle does not do | [Extending Poncho](extending/index) |
| Going to production | [Security](security) |
| Want to contribute | [Contributing](contributing/index) |

## Built with <!-- {docsify-ignore} -->

- Theme: [Bootstrap 5](https://getbootstrap.com/), [AdminKit](https://adminkit.io/)
- Icons: [Material Design Icons](https://pictogrammers.com/library/mdi/),
  [Unicons](https://iconscout.com/unicons/explore/line)
- JavaScript: [datatables.net](https://datatables.net/) (tables), [Tom Select](https://tom-select.js.org/)
  (autocomplete), [Flatpickr](https://flatpickr.js.org/) (date picker),
  [Dragula](https://bevacqua.github.io/dragula/) (sortable collections),
  [Toastify](https://github.com/apvarun/toastify-js) (toasts), [SimpleBar](https://github.com/Grsmto/simplebar) (sidebar scrolling)
