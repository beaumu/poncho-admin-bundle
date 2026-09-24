# DataTable JavaScript

`render_table()` emits a `<poncho-datatable>` custom element. Its `data-options` attribute holds
the datatables.net configuration generated from your table type; the element reads it, creates
the datatables.net instance and wires up the toolbar.

## Getting hold of a table

```js
const table = document.querySelector('#app_datatable_missiontable')
```

The element id is the table's `id` option.

## Methods

| Method | |
| --- | --- |
| `reload(paging = true)` | Reload the rows. `paging: true` goes back to the first page; `false` stays on the current one |
| `reloadAfter(wait = 0, paging = true)` | Debounced `reload()`: calling it again within `wait` ms restarts the timer |
| `getState()` | The request parameters of the last draw, plus `count.page` and `count.total` |
| `error(message)` | Replace the rows with an error message |
| `registerPlugin(plugin)` | Attach a plugin (see below) |

A **selectable** table also has:

| Method | |
| --- | --- |
| `selectPage()` | Select every row on the current page |
| `unselectPage()` | Unselect every row on the current page |
| `unselectAll()` | Clear the selection across all pages |
| `getSelectedIds()` | `string[]` |

and its `getState()` additionally returns `ids` and `count.selected`.

## Properties

| Property | |
| --- | --- |
| `datatable` | The underlying datatables.net `DataTable` instance — its full API is available |
| `options` | The parsed `data-options` |
| `form` | The toolbar `<form>`, or `null` |
| `table`, `thead`, `tbody` | The table elements |

For example, reacting to every redraw through the datatables.net API:

```js
table.datatable.on('draw', () => console.log(`${table.datatable.rows().count()} rows`))
```

## Calling it from the server

A `JsResponse` can call any of these methods on every element matching a CSS selector:

```php
$this->js()->reloadTable();                            // reload() on every <poncho-datatable>
$this->js()->reloadTable([false]);                     // reload(false): keep the current page
$this->js()->unselectTable();                          // unselectAll()
$this->js()->callTable('reloadAfter', [500]);          // any method, any arguments
$this->js()->reloadTable([], '#app_datatable_missiontable');   // one table only
```

?> The first argument of `reloadTable()` and `callTable()` is the **list of arguments** passed to the
JavaScript method, not an options map. `reloadTable([false])` calls `reload(false)`; an associative
array such as `['paging' => false]` throws an `InvalidArgumentException`.

Without a selector these target **every** table on the page.

## What gets sent

On each draw the element sends datatables.net's parameters (`draw`, `start`, `length`, `order`),
minus `columns` and `search` which the server does not use, plus:

- `_dtid` — the table id, so the controller recognises the request,
- every field of the toolbar form, flattened the way PHP expects (`name[sub][0]`).

## Plugins

A plugin is an object with a `configure(ponchoDatatable)` method, called once:

```js
class HighlightPlugin {
    configure(table) {
        table.datatable.on('draw', () => {
            table.tbody.querySelectorAll('tr').forEach(tr => {
                if (tr.dataset.id === '42') tr.classList.add('table-warning')
            })
        })
    }
}

document.querySelector('#app_datatable_missiontable').registerPlugin(new HighlightPlugin())
```

Three are built in and registered automatically:

| Plugin | Registered when | Does |
| --- | --- | --- |
| `SelectPlugin` | `selectable: true` | Checkboxes, selection toolbar, the selection methods above |
| `TreePlugin` | `tree: true` | Indentation, carets, expand/collapse |
| `RowDetailsPlugin` | always | Child rows for [`DetailsColumnType`](component/datatable/columns#detailscolumntype) |

## Lifecycle

The element initialises in `connectedCallback()` and destroys its datatables.net instance in
`disconnectedCallback()`. Inserting a `render_table()` fragment into the page — for instance through
`JsResponse::update()` — therefore works without extra code, and removing it cleans up.

Tooltips (`data-bs-toggle="tooltip"`) inside the rows are re-created after every draw.
