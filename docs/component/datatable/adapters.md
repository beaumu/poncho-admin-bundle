# Adapters

An adapter turns the table's current state — page, sort, filter values — into rows. A table has
exactly one.

```php
$builder->useAdapter(string|callable $type, array $options = []);
$builder->useEntityAdapter(string|array $options);        // EntityAdapterType
$builder->useNestedEntityAdapter(string|array $options);  // NestedEntityAdapterType
$builder->clearAdapter();
```

Passing a class name string to the two entity shortcuts is short for `['class' => …]`.

## EntityAdapterType

A Doctrine query over one entity, paginated with Doctrine's `Paginator`.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `class` | `string` | **required** | Entity class |
| `query_alias` | `string` | `e` | Alias of the root entity |
| `query` | `?callable` | `null` | `fn (QueryBuilder $qb, array $formData)` — add joins, conditions, parameters |
| `em` | `null\|string\|EntityManagerInterface` | `null` | Entity manager; `null` picks the one managing `class` |
| `fetch_join_collection` | `bool` | `true` | Passed to `Paginator` |
| `use_output_walker` | `?bool` | `null` | Passed to `Paginator::setUseOutputWalkers()` when set |
| `use_distinct_hint` | `bool` | `true` | When `false`, disables the `DISTINCT` hint of the count query |

```php
$builder->useEntityAdapter([
    'class' => Mission::class,
    'query' => function (QueryBuilder $qb, array $formData) {
        $qb->leftJoin('e.rocket', 'r')->addSelect('r');

        if (isset($formData['search'])) {
            DoctrineUtils::matchAll($qb, ['e.name', 'r.name'], $formData['search']);
        }

        if (isset($formData['status'])) {
            $qb->andWhere('e.status = :status')->setParameter('status', $formData['status']);
        }
    },
]);
```

`$formData` holds the submitted [filter](component/datatable/filters) values, keyed by field name.

Paging and sorting are applied **after** your callable, from the table state: `setFirstResult()`,
`setMaxResults()`, and one `addOrderBy()` per sorted column. Don't set them yourself; an
`orderBy()` of your own would be overridden by the user's sort.

For very large tables (around a million rows and up), the default paginator settings get slow.
Disabling the extra work usually fixes it:

```php
$builder->useEntityAdapter([
    'class' => Mission::class,
    'fetch_join_collection' => false,
    'use_output_walker' => false,
    'use_distinct_hint' => false,
]);
```

This is only correct when your query has no to-many joins.

## NestedEntityAdapterType

Loads a nested-set tree, such as one managed by Gedmo's `Tree` extension, for
[tree tables](component/datatable/tree).

| Option | Type | Default | |
| --- | --- | --- | --- |
| `class` | `string` | **required** | |
| `query_alias` | `string` | `e` | |
| `query` | `?callable` | `null` | `fn (QueryBuilder $qb, array $formData, array $tableOptions)` — note the third argument |
| `em` | `null\|string\|EntityManagerInterface` | `null` | |
| `left_path` | `string` | `left` | Field holding the nested-set left value; rows are ordered by it |
| `level_path` | `string` | `level` | Field holding the depth |
| `min_level` | `int` | `1` | Rows below this level are excluded. `1` hides the single root node of a Gedmo tree |

It returns **every** matching row: there is no paging, and column sorting is ignored, because a tree
is only meaningful in left-value order.

## CallableAdapterType

Any PHP code that can produce rows. Pass a callable straight to `useAdapter()`:

```php
use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableResult;
use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableState;

$builder->useAdapter(function (DataTableState $state): DataTableResult {
    $response = $this->client->request('GET', 'https://api.example.com/launches', [
        'query' => [
            'offset' => $state->getStart(),
            'limit' => $state->getLength(),
            'q' => $state->getFormData()['search'] ?? null,
        ],
    ])->toArray();

    return new DataTableResult($response['items'], $response['total']);
});
```

The callable receives the `DataTableState` and must return a `DataTableResult`.

### DataTableState

| Method | Returns | |
| --- | --- | --- |
| `getStart()` | `int` | Offset of the first row |
| `getLength()` | `int` | Rows requested; `-1` means all |
| `getOrderBy()` | `array` | List of `['order_by' => string[], 'direction' => 'asc'\|'desc']`, already filtered to sortable columns |
| `getFormData()` | `array` | Filter values |
| `getDraw()` | `int` | datatables.net draw counter |
| `isCallback()` | `bool` | |
| `getDataTable()` | `DataTable` | |

### DataTableResult

```php
new DataTableResult(iterable $data = [], ?int $count = null);
```

`$data` is the current page. `$count` is the total **before** paging. Leave it out only when `$data`
already holds every row, in which case `count($data)` is used. If `$data` is not countable the
constructor throws; if it is countable but holds a single page, the pager silently shows one page.

## Errors

Throw `Poncho\AdminBundle\Lib\DataTable\AdapterException` from an adapter to report a failure to the
user: the table body shows the message instead of rows, and the response has status 500.

```php
throw new AdapterException('The launch service is unavailable, try again later.');
```

!> The message is inserted as HTML. Keep it static, and never include user input in it.

Any other exception propagates normally, which in production means a generic error in the table.

To write a reusable adapter type, see [Extending: DataTable](extending/datatable).
