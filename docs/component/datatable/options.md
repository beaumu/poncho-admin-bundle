# Table options

Options are passed when creating a table, or defaulted in your type's `configureOptions()`:

```php
// at the call site
$table = $this->createTable(MissionTableType::class, ['page_length' => 50]);

// or in the type
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setDefault('page_length', 50);
}
```

Your `configureOptions()` can also declare **new** options, which then reach `buildTable()` in
`$options` — the way to parameterise one type for several screens:

```php
public function configureOptions(OptionsResolver $resolver): void
{
    $resolver->setRequired('status')->setAllowedTypes('status', 'string');
}

public function buildTable(DataTableBuilder $builder, array $options): void
{
    $builder->useEntityAdapter([
        'class' => Mission::class,
        'query' => fn (QueryBuilder $qb) => $qb->andWhere('e.status = :s')->setParameter('s', $options['status']),
    ]);
}
```

## Identity and transport

| Option | Type | Default | |
| --- | --- | --- | --- |
| `id` | `string` | derived from the type class | Element id, and the `_dtid` sent with every callback. `App\DataTable\MissionTableType` → `app_datatable_missiontable` |
| `method` | `string` | `POST` | HTTP method of the callbacks. `POST`, `GET` (either case) |
| `load_route` | `?string` | `null` | Route the callbacks are sent to. When `null`, the current request URI |
| `load_route_params` | `array` | `[]` | Parameters for `load_route` |

?> Two tables on one page need different ids. That is automatic for two different types, but not for
two instances of the same type, or two tables made with `createTableBuilder()` — which all derive
the id from `DataTableType` itself. Pass an explicit `id` in those cases.

`DataTableBuilder::setLoadUrl(string $route, array $params = [])` sets `load_route` and
`load_route_params` from inside `buildTable()`.

## Paging and ordering

| Option | Type | Default | |
| --- | --- | --- | --- |
| `paging` | `bool` | `true`, `false` for trees | |
| `page_length` | `int` | `poncho_admin.datatable.page_length` (25) | Rows per page |
| `length_change` | `bool` | `false` | Show the rows-per-page selector |
| `length_menu` | `array` | `[25, 50, 100]` | Choices of that selector |
| `orderable` | `bool` | `true`, `false` for trees | Allow sorting at all. Per-column sorting is set by the column's `order` option |
| `scroll_y` | `?int` | `null` | Fixed body height in pixels, scrolling vertically |

## Appearance

| Option | Type | Default | |
| --- | --- | --- | --- |
| `container_class` | `?string` | `poncho_admin.datatable.container_class` (`''`) | CSS class of the `<poncho-datatable>` element |
| `class` | `?string` | `poncho_admin.datatable.class` (`table-centered`) | CSS class of the `<table>`; `table js-datatable` is always appended |
| `dom` | `string` | `poncho_admin.datatable.dom` | datatables.net [`dom`](https://datatables.net/reference/option/dom) layout string |
| `template` | `string` | `@PonchoAdmin/lib/datatable/datatable.html.twig` | Template of the whole table |

## Toolbar

| Option | Type | Default | |
| --- | --- | --- | --- |
| `toolbar_form_name` | `string` | `<id>_tbf` | Name of the filter form, and so the prefix of its fields in the request |
| `toolbar_form_options` | `array` | `validation_groups: false`, `csrf_protection: false`, `label: false`, `required: false` | Options of the filter form's root `FormType` |
| `toolbar_form_data` | `mixed` | `null` | Initial data of the filter form |
| `toolbar_template` | `string` | `@PonchoAdmin/lib/datatable/toolbar.html.twig` | |

The filter form must produce an **array**. A `data_class` on it makes the table throw
`Toolbar can only handle array form::getData()`.

## Selection and trees

| Option | Type | Default | |
| --- | --- | --- | --- |
| `selectable` | `bool` | `false` | Adds a checkbox column and the selection toolbar. See [Selection](component/datatable/selection) |
| `id_path` | `?string` | `id` | Property path of each row's id, written to `data-id` |
| `tree` | `bool` | `false` | Render as a tree. See [Tree tables](component/datatable/tree) |
| `parent_path` | `?string` | `parent` | Property path of the parent, for trees |
| `tree_column_index` | `int` | `0` | Column that carries the indentation and expand caret |

## Customising rows: `buildRowView()`

`buildRowView(RowView $view, DataTable $dataTable, array $options)` runs once per row, after the
cells are rendered. `RowView` exposes:

| Property | Type | |
| --- | --- | --- |
| `source` | `mixed` | The row's object or array |
| `data` | `array` | The rendered cells, in column order |
| `attr` | `array` | HTML attributes of the `<tr>`. `attr['class']` becomes its CSS class |
| `collapsed` | `bool` | Tree only: start collapsed. Default `true` |
| `selectable` | `bool` | Selectable tables only: whether the row's checkbox is active. Default `true` |

```php
public function buildRowView(RowView $view, DataTable $dataTable, array $options): void
{
    $mission = $view->source;

    if ($mission->failed) {
        $view->attr['class'] = 'table-danger';
    }

    $view->selectable = !$mission->locked;
}
```

## Bundle-wide defaults

The defaults marked `poncho_admin.datatable.*` above come from configuration:

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    datatable:
        page_length: 25
        container_class: ''
        class: table-centered
        dom: "< tr><'row table-footer'<'col-sm-12 col-md-5'li><'col-sm-12 col-md-7'p>>"
        reset_paging_on_reload: false
```

!> `reset_paging_on_reload` currently has no effect: nothing in the bundle reads it. To control
paging on reload, see `reloadTable()` in [JsResponse](component/jsresponse/index).
