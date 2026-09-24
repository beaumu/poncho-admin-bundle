# Actions

Actions are links and buttons. They appear in two places:

- **The toolbar**, next to the filters — `$builder->addAction()`.
- **Each row**, in an [`ActionColumnType`](component/datatable/columns#actioncolumntype) — a `ColumnActionBuilder`.

Both use the same action types and options.

## Toolbar actions

```php
$builder->addAction(string $name, string $type = ActionType::class, array $options = []);
$builder->removeAction(string $name);
$builder->hasAction(string $name): bool;
```

```php
use Poncho\AdminBundle\Lib\DataTable\Action\ButtonActionType;
use Poncho\AdminBundle\Lib\DataTable\Action\ButtonAddActionType;

$builder->addAction('add', ButtonAddActionType::class, [
    'route' => 'mission_edit',
    'xhr' => true,
]);

$builder->addAction('export', ButtonActionType::class, [
    'route' => 'mission_export',
    'class' => 'btn btn-light',
    'icon' => 'mdi mdi-download me-1',
]);
```

## Row actions: ColumnActionBuilder

Inside an `ActionColumnType`'s `build` callable you get a `ColumnActionBuilder` with shortcuts for
the common cases:

| Method | Produces |
| --- | --- |
| `showLink(array $options = [])` | Eye icon, tooltip `action.show` |
| `editLink(array $options = [])` | Pencil icon, tooltip `action.edit` |
| `deleteLink(array $options = [])` | Bin icon, tooltip `action.delete`, sent over XHR after a confirmation (`message.delete_confirm`) |
| `moveUpLink(array $options = [])` | Up arrow over XHR, adds `direction: up` to `route_params` |
| `moveDownLink(array $options = [])` | Down arrow over XHR, adds `direction: down` to `route_params` |
| `moveLinks(array $options = [])` | Both of the above |
| `link(array $options = [])` | A plain `LinkActionType` with class `table-link` |
| `html(string $html)` | Raw HTML, through `RawActionType` |
| `add(string $type = ActionType::class, array $options = [])` | Any action type |

Each shortcut's defaults are merged under your options, so any of them can be overridden:

```php
'build' => function (ColumnActionBuilder $actions, Mission $mission) {
    $actions->editLink([
        'route' => 'mission_edit',
        'route_params' => ['id' => $mission->id],
        'xhr' => true,
    ]);

    $actions->deleteLink([
        'route' => 'mission_delete',
        'route_params' => ['id' => $mission->id],
        'confirm' => 'Delete this mission?',
    ]);

    $actions->html('<span class="text-muted ms-2">#'.$mission->id.'</span>');
},
```

`deleteLink()`, `moveUpLink()`, `moveDownLink()` and `moveLinks()` attach a CSRF token to
`route_params['_token']` automatically (when CSRF protection is enabled), computed from the
`route` option and, when present, `route_params['id']`. The routes these generate — including the
ones `make:admin:table` and `make:admin:tree` generate — accept `GET` but check that token. A
plain `link()` you build yourself does neither; see [Security](security#csrf-on-delete-move-and-bulk-action-routes)
for how to add the same protection to it.

## Action types

### LinkActionType

An `<a>` element. Every other link-like type extends it.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `route` | `?string` | `null` | Route name |
| `route_params` | `array` | `[]` | |
| `url` | `?string` | `null` | Used when there is no `route` |
| `text` | `?string` | `null` | Visible label |
| `title` | `?string` | `null` | Tooltip. Translated |
| `icon` | `?string` | `null` | CSS classes of an `<i>`, e.g. `mdi mdi-pencil` |
| `class` | `?string` | `null` | CSS class of the link |
| `translation_domain` | `null\|string\|false` | `null` | Domain for `text`, `title` and `confirm` |
| `target` | `null\|'_blank'\|'_self'` | `null` | Only for plain links |
| `xhr` | `bool` | `false` | Send the request over AJAX and handle a [JsResponse](component/jsresponse/index) |
| `confirm` | `?string` | `null` | Ask for confirmation first. Translated |
| `spinner` | `bool` | `false` | Show the full-page spinner during the request |
| `display` | `null\|'selection'\|'no_selection'` | `null` | Show only when rows are selected, or only when none are. See [Selection](component/datatable/selection) |
| `send_state` | `bool` | `false` | Send the table's state along with the request. See [Selection](component/datatable/selection) |

How the link behaves depends on these, in this order:

1. `send_state: true` — an XHR carrying the table state.
2. `xhr: true` — an XHR.
3. Otherwise — a normal `href`, honouring `target`.

?> **Text and translation.** With `translation_domain: null` (the default), `text` goes through the
default translation domain; an untranslated string is printed as written. With
`translation_domain: false` the text is instead passed through Twig's `humanize` filter, which
lowercases everything after the first letter and splits on capitals — so `'Export CSV'` becomes
`Export c s v`. Leave the domain at `null` to show text verbatim.

### ButtonActionType

A `LinkActionType` styled as a button.

| Changed default | |
| --- | --- |
| `class` | `btn btn-primary` |
| `text` | the action name, humanized |

### ButtonAddActionType

A `ButtonActionType` with a plus icon (`icon: 'mdi mdi-plus me-1'`).

### RawActionType

Outputs its HTML unchanged.

| Option | Type | Default |
| --- | --- | --- |
| `html` | `string` | **required** |

## Handling the request

An action pointed at a route with `xhr: true` should answer with a `JsResponse`:

```php
#[Route('/mission/edit/{id}')]
public function edit(Request $request, ?int $id = null): Response
{
    $mission = $id ? $this->findOrNotFound(Mission::class, $id) : new Mission();
    $form = $this->createForm(MissionType::class, $mission);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $this->persistAndFlush($mission);

        return $this->js()
            ->closeModal()
            ->reloadTable()
            ->toastSuccess('Mission saved');
    }

    return $this->js()->modal('@PonchoAdmin/edit_modal.html.twig', [
        'form' => $form->createView(),
        'entity' => $mission,
    ]);
}
```

`@PonchoAdmin/edit_modal.html.twig` renders the form in a modal and submits it back over XHR to the
current URL, so the same action receives both the open and the submit. Passing `entity` gives the
modal an *Edit* or *Add* title depending on whether `entity.id` is set; pass `title` instead to
choose your own.

To write your own action type, see [Extending: DataTable](extending/datatable).
