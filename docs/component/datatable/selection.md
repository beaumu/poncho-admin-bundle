# Selection & bulk actions

Set `selectable: true` and the table gains a checkbox column, a "select page" toggle in its header,
and a selection bar above the rows:

```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefault('selectable', true);
}
```

Selection is by row id — the `data-id` attribute read through the `id_path` option — and it
**persists across pages**: paging or filtering does not drop the rows already selected.

## Bulk actions

An action that should operate on the selection needs two options:

- `send_state: true` — sends the table's state, selected ids included, with the request.
- `display: 'selection'` — shows the action only while something is selected.

```php
use Poncho\AdminBundle\Lib\DataTable\Action\ButtonActionType;

$builder->addAction('delete_selection', ButtonActionType::class, [
    'route' => 'mission_delete_selection',
    'send_state' => true,
    'display' => 'selection',
    'class' => 'btn btn-danger',
    'icon' => 'mdi mdi-delete me-1',
    'text' => 'Delete selection',
    'confirm' => 'Delete the selected missions?',
]);
```

`display: 'no_selection'` does the opposite: the action shows only while nothing is selected.

### Reading the state

On the server, rebuild the state from the request with `DataTableActionState`:

```php
use Poncho\AdminBundle\Lib\DataTable\Utils\DataTableActionState;

#[Route('/mission/delete-selection')]
public function deleteSelection(Request $request): Response
{
    $state = DataTableActionState::createFromRequest($request);

    foreach ($state->getSelectedIds() as $id) {
        $mission = $this->em()->find(Mission::class, $id);
        if ($mission) {
            $this->em()->remove($mission);
        }
    }
    $this->em()->flush();

    return $this->js()
        ->unselectTable()
        ->reloadTable()
        ->toastSuccess(sprintf('%d missions deleted', $state->selectedCount()));
}
```

`createFromRequest()` reads `state` from the request body, falling back to the query string. A
request without one gets a **400 Bad Request**.

!> **Bulk actions are sent as `GET`.** A `send_state` action goes through `AjaxUtils` without a
method, so jQuery defaults to `GET`, and `LinkActionType` has no option to change it. Two
consequences: don't restrict the route to `POST` or it answers 405, and the whole state — every
selected id — travels in the query string, where a very large selection can exceed the server's URL
length limit (414). A destructive bulk action is also exposed to CSRF; see [Security](security).

| Method | Returns | |
| --- | --- | --- |
| `getSelectedIds()` | `array` | Ids of the selected rows, as strings |
| `selectedCount()` | `int` | |
| `pageCount()` | `int` | Rows on the current page |
| `totalCount()` | `int` | Rows across all pages, after filtering |
| `getData()` | `array` | The raw state |
| `disablePagination()` | `self` | Drops `start` and `length` from the state |

### Acting on the current filter instead

The state also carries the filters and sort order the user is looking at. Replay it into the table
itself to get "everything matching the current filter" — the basis of an export:

```php
$state = DataTableActionState::createFromRequest($request)->disablePagination();

$table = $this->createTable(MissionTableType::class);
$table->submit($state->getData());

foreach ($table->getAdapterResult()->getData() as $mission) {
    // every row matching the user's filters, in the user's order
}
```

`disablePagination()` removes the page window, so the result is not limited to the page on screen.

## Rows that cannot be selected

Set `RowView::$selectable` to `false` in `buildRowView()`. The checkbox stays visible but is
ignored:

```php
public function buildRowView(RowView $view, DataTable $dataTable, array $options): void
{
    $view->selectable = !$view->source->locked;
}
```

## From JavaScript

A selectable `<poncho-datatable>` gains `selectPage()`, `unselectPage()`, `unselectAll()` and
`getSelectedIds()`, and its `getState()` includes `ids` and `count.selected`. From a `JsResponse`:

```php
$this->js()->unselectTable();          // clears the selection
```

See [DataTable JavaScript](component/datatable/javascript).
