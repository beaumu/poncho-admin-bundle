# JsResponse: client side

## Making a request from HTML

Any element with `data-xhr` sends an AJAX request instead of navigating, and hands the response to
the `JsResponse` handler. It works on elements added to the page later, too.

```html
<a href="#" data-xhr="{{ path('mission_edit', {id: mission.id}) }}">Edit</a>

<form method="post" action="#" data-xhr="{{ path('mission_note') }}">
    <textarea name="note"></textarea>
    <button type="submit">Save</button>
</form>
```

| Attribute | On | |
| --- | --- | --- |
| `data-xhr="<url>"` | any element or `<form>` | URL to request. Elements trigger on click, forms on submit |
| `data-confirm="<text>"` | same | Ask for confirmation first |
| `data-spinner` | same | Show the full-page spinner. Any value except `"false"` |
| `data-method="<method>"` | non-form elements | HTTP method. Defaults to `GET` |
| `data-xhr-id="<id>"` | same | Ignore clicks while a request with this id is still pending — prevents double submits |

A `<form data-xhr>` uses its own `method` and sends its fields as `multipart/form-data`, file
inputs included.

Other attributes wired at page load:

| Attribute | |
| --- | --- |
| `data-bs-toggle="tooltip"` | Initialises a Bootstrap tooltip. Only for elements present when the page loads; table rows re-initialise their own |
| `data-toggle="toast"` + `data-type`, `data-text`, `data-title` | Shows a toast when the page loads |

## Making a request from JavaScript

The bundle exposes a global `poncho` object:

```js
poncho.locale              // the page locale, from <html lang>
poncho.translator          // trans(key, params = {}, locale = null)
poncho.toast               // show(type, text, title, options), info(), success(), warning(), error()
poncho.spinner             // show(), hide()
poncho.confirmModal        // show({text, confirm, cancel_text, confirm_text})
poncho.jsResponseHandler   // registerAction(), removeAction(), clearActions(), setErrorHandler()
```

AJAX goes through `AjaxUtils`, which is not a global. Import it from the bundle's sources (see
[Frontend](frontend/index)) or reproduce the call:

```js
import AjaxUtils from 'poncho-admin-bundle/assets/utils/AjaxUtils.js'

AjaxUtils.post({url: '/mission/1/launch', data: {when: 'now'}, confirm: 'Launch?', spinner: true})
```

| Method | |
| --- | --- |
| `request(options)` | Any request. `options` are jQuery `$.ajax()` settings plus `confirm`, `spinner`, `xhr_id` |
| `get(options)`, `post(options)` | Shortcuts setting `method` |
| `requestWithElement(element, options = {})` | Read `data-xhr`, `data-confirm`, … from an element, then `request()` |

Every request carries the header `xhr-request: js`, and jQuery adds `X-Requested-With: XMLHttpRequest`.
The latter is what makes Poncho's authentication entry point answer an expired session with **401**
rather than a redirect to the login page.

## Errors

A failed request is passed to the error handler. The default one shows a toast: a warning for 401,
403 and 404, an error for anything else. Replace it with your own:

```js
poncho.jsResponseHandler.setErrorHandler((jqXHR, textStatus, errorThrown) => {
    if (jqXHR.status === 422) {
        poncho.toast.warning(jqXHR.responseJSON.message)
    } else {
        poncho.toast.error('Something went wrong')
    }
})
```

The arguments are jQuery's: a `jqXHR`, a status string and the error.

## Built-in actions

These are what the PHP methods produce. You rarely need their names, except to override one.

| Action | Params | Produced by |
| --- | --- | --- |
| `toast` | `type`, `text`, `title`, `options` | `toast*()` |
| `show_modal` / `close_modal` | `value`, `id`, `options` / `id` | `modal()`, `modalHtml()` / `closeModal()` |
| `show_offcanvas` / `close_offcanvas` | `value`, `id` / `id` | `offcanvas()`, `offcanvasHtml()` / `closeOffcanvas()` |
| `update` | `value`, `selector` | `update()`, `updateHtml()` |
| `remove` | `selector` | `remove()` |
| `call` | `selector`, `method`, `method_params` | `call()`, `callTable()`, `reloadTable()`, `unselectTable()` |
| `redirect` | `value` | `redirect()`, `redirectToRoute()` |
| `reload` | — | `reload()` |
| `forward` | `ajaxOptions` | `forward()`, `forwardToRoute()` |
| `download` | `content`, `filename` | `download()` |
| `eval` | `value` | `eval()` |

## Custom actions

Register a handler in JavaScript:

```js
poncho.jsResponseHandler.registerAction('confetti', (params) => {
    launchConfetti({colors: params.colors})
})
```

and send it from PHP with `add()`:

```php
return $this->js()
    ->toastSuccess('Mission launched')
    ->add('confetti', ['colors' => ['#0ea5e9', '#f59e0b']]);
```

Params must be JSON-serialisable. Registering an existing name replaces the built-in, so you can
restyle how any of the actions above behave. An action name the client does not know is logged to
the console and skipped; the rest of the response still runs.

Load your script after the bundle's — `poncho` is defined when `poncho_admin.js` executes. Adding it
to the `javascripts` block of the layout does that:

```twig
{% block javascripts %}
    {{ parent() }}
    <script src="{{ asset('js/admin-actions.js') }}"></script>
{% endblock %}
```

## Confirmation dialog

`data-confirm`, the `confirm` action option and `AjaxUtils`' `confirm` all open the same dialog:

```js
poncho.confirmModal.show({
    text: 'Abort the launch?',
    confirm_text: 'Abort',
    cancel_text: 'Keep going',
    confirm: () => abortLaunch(),
})
```

Only one can be open at a time; a second call while it is showing is ignored. Enter confirms.

!> The dialog text is inserted as HTML. See [Security](security).
