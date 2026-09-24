# JsResponse

A `JsResponse` lets a controller drive the page after an AJAX request: open a modal, reload a table,
show a toast, redirect — without writing JavaScript. It is a JSON `Response` whose body is a list of
instructions that the browser executes in order.

```php
return $this->js()
    ->closeModal()
    ->reloadTable()
    ->toastSuccess('Mission saved');
```

sends

```json
[
    {"action": "close_modal", "params": {"id": null}},
    {"action": "call", "params": {"selector": "poncho-datatable", "method": "reload", "method_params": []}},
    {"action": "toast", "params": {"type": "success", "text": "Mission saved", "title": null, "options": []}}
]
```

`$this->js()` is available in any [`AdminController`](controller). Elsewhere, inject
`Poncho\AdminBundle\Lib\JsResponse\JsResponseFactory` and call `create()`.

A `JsResponse` only does something when the request was made by Poncho's AJAX layer — a link or
form with `data-xhr`, an action with `xhr: true`, or `AjaxUtils` from JavaScript. See
[Client side](component/jsresponse/client).

## Toasts

| Method |
| --- |
| `toast(string $type, string\|TranslatableMessage $text, string\|TranslatableMessage\|null $title = null, array $options = [])` |
| `toastInfo($text, $title = null, array $options = [])` |
| `toastSuccess($text, $title = null, array $options = [])` |
| `toastWarning($text, $title = null, array $options = [])` |
| `toastError($text, $title = null, array $options = [])` |

`$options` is passed to [Toastify](https://github.com/apvarun/toastify-js#api): `duration` (ms,
default 3000), `gravity` (`top`/`bottom`), `position` (`left`/`center`/`right`), `close`
(default `true`), `stopOnFocus`, `className`.

```php
$this->js()->toastError('Launch aborted', 'Mission control', ['duration' => 10000]);
```

!> Text and title are rendered as HTML. Escape user data before putting it in a toast. See
[Security](security).

## Modals and offcanvas

| Method | |
| --- | --- |
| `modal(string $template, array $context = [], ?string $id = null, array $options = [])` | Render a template and show it as a modal |
| `modalHtml(string $html, ?string $id = null, array $options = [])` | Show HTML as a modal |
| `closeModal(?string $id = null)` | Close it |
| `offcanvas(string $template, array $context = [], ?string $id = null)` | Render a template and show it as an offcanvas panel |
| `offcanvasHtml(string $html, ?string $id = null)` | |
| `closeOffcanvas(?string $id = null)` | |

The HTML must be a complete Bootstrap modal (or offcanvas) — start from one of the bundled
templates rather than writing one:

| Template | |
| --- | --- |
| `@PonchoAdmin/lib/modal/default.html.twig` | Header, body, close button. Blocks: `modal_title`, `modal_content`, `modal_footer`, … |
| `@PonchoAdmin/lib/modal/form.html.twig` | A form that submits back over XHR. Variables: `form`, `action` (default: the current URL), `form_layout` |
| `@PonchoAdmin/edit_modal.html.twig` | The form modal titled *Edit* or *Add* from `entity.id` |
| `@PonchoAdmin/lib/offcanvas/default.html.twig` | |
| `@PonchoAdmin/lib/offcanvas/form.html.twig` | |

The default modal also reads `title`, `class` (dialog size, default `modal-lg`), `header_class`
(default `bg-light`), `body_class` and `content`.

!> `content` is rendered with `| raw`. Don't pass user data through it unescaped.

**Only one modal and one offcanvas exist at a time.** The client always gives them the ids
`poncho-modal` and `poncho-offcanvas`: the `$id` arguments and the modal `$options` are currently
ignored. Calling `modal()` while a modal is open **replaces its content in place** — which is how a
multi-step form stays in one dialog.

## Tables and custom elements

| Method | |
| --- | --- |
| `reloadTable(array $methodParams = [], string $cssSelector = 'poncho-datatable')` | `reload(...$methodParams)` on matching tables |
| `unselectTable(string $cssSelector = 'poncho-datatable')` | `unselectAll()` |
| `callTable(string $method, array $methodParams = [], string $cssSelector = 'poncho-datatable')` | Any table method |
| `call(string $method, array $methodParams, string $cssSelector)` | Any method on any element |

`$methodParams` is the **argument list**, so it must be a list: `reloadTable([false])` calls
`reload(false)` and keeps the current page. An associative array throws
`InvalidArgumentException`.

`call()` works on any element exposing methods — every Poncho custom element does:

```php
$this->js()->call('setValue', [[3, 7]], '#mission_crew');   // <select is="poncho-autocomplete">
```

## Page content

| Method | |
| --- | --- |
| `update(string $template, array $context, string $cssSelector)` | Render a template into every matching element (`innerHTML`) |
| `updateHtml(string $html, string $cssSelector)` | Same, with HTML |
| `remove(string $cssSelector)` | Remove every matching element |

```php
$this->js()->update('mission/_status.html.twig', ['mission' => $mission], '#mission-status');
```

Custom elements inside inserted HTML initialise themselves, so a `render_table()` fragment works.

## Navigation

| Method | |
| --- | --- |
| `redirect(string $url)` | `window.location = $url` |
| `redirectToRoute(string $route, array $params = [])` | |
| `reload()` | Reload the current page |
| `forward(string $url, array $ajaxOptions = [])` | Make a new AJAX request and handle its `JsResponse` |
| `forwardToRoute(string $route, array $params = [], array $ajaxOptions = [])` | |

`forward()` chains a second request — for instance after a save, open the next item's modal.
`$ajaxOptions` are [jQuery `$.ajax()` settings](https://api.jquery.com/jQuery.ajax/) such as
`method` or `data`, plus Poncho's `confirm`, `spinner` and `xhr_id`.

## Downloads

```php
download(string $content, ?string $filename = null)
```

Makes the browser save `$content` as a file:

```php
return $this->js()->download($csv, 'missions.csv');
```

The content travels inside the JSON response, so this suits generated files of a few megabytes at
most. For anything larger, return a normal `BinaryFileResponse` from a plain link.

## Arbitrary JavaScript

```php
eval(string $js)
```

Runs `$js` with the browser's `eval()`.

!> Never build it from user input. Prefer a [custom action](component/jsresponse/client#custom-actions).

## Custom and low-level

| Method | |
| --- | --- |
| `add(JsMessage\|string $action, array $params = [])` | Append any action, including your own |
| `clear(): void` | Drop all queued actions |

See [custom actions](component/jsresponse/client#custom-actions).

## Testing a controller that returns one

The body is written only when the response is sent, so `getContent()` returns an empty string in a
functional test. Assert on what the client receives instead:

```php
$client->request('POST', '/mission/edit/1');
$messages = json_decode($client->getResponse()->getContent(), true);
```

— `KernelBrowser` sends the response, so its content is populated.
