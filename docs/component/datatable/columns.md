# Columns

```php
$builder->add(string $name, string $type = PropertyColumnType::class, array $options = []);
$builder->remove(string $name);
$builder->has(string $name): bool;
```

Columns render in the order they are added. The name must be unique within the table and, for
`PropertyColumnType` and its children, is also the default property path.

## Options shared by every column

| Option | Type | Default | |
| --- | --- | --- | --- |
| `label` | `?string` | the name, humanized (`launchedAt` → `Launched at`) | Header text. `null` for no header |
| `translation_domain` | `null\|string\|false` | `null` | Domain used to translate `label`. `false` disables translation |
| `order` | `false\|null\|'ASC'\|'DESC'` | `false` (`null` for property columns) | `false`: not sortable. `null`: sortable, no default. `'ASC'`/`'DESC'`: sortable and sorted by default |
| `order_by` | `null\|string\|array` | `null` (the property path for property columns) | What sorting orders by. A path without `.` is prefixed with the query alias; an array orders by several fields |
| `class` | `?string` | `null` | CSS class on both the header and the cells |
| `width` | `?string` | `null` | Any CSS width, e.g. `'120px'` or `'10%'` |
| `render` | `?callable` | `null` | `fn ($row, array $options): string` — replaces the type's rendering. **Escaped** |
| `render_html` | `?callable` | `null` | Same signature, but the output is **not** escaped |
| `is_safe_html` | `bool` | `false` | Skip escaping of the type's own output |

```php
$builder->add('name', options: [
    'label' => 'label.name',
    'translation_domain' => 'messages',
    'order' => 'ASC',
    'width' => '30%',
]);

$builder->add('status', options: [
    'render_html' => fn (Mission $m) => sprintf('<span class="badge bg-%s">%s</span>',
        $m->failed ? 'danger' : 'success',
        htmlspecialchars($m->status)
    ),
]);
```

!> `render_html` output goes into the page verbatim. Escape anything that came from a user, as
`htmlspecialchars()` does above.

## PropertyColumnType

The default type. Reads a value with Symfony's [PropertyAccess](https://symfony.com/doc/current/components/property_access.html)
and renders it as a string.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `property_path` | `string` | the column name | Any property path: `name`, `rocket.name`, `[key]` for arrays |
| `property_accessor` | `PropertyAccessorInterface` | a default accessor | |

Sortable by default (`order: null`), ordering by `property_path`.

```php
$builder->add('rocket', options: ['property_path' => 'rocket.name', 'order_by' => 'r.name']);
```

A dotted `order_by` such as `r.name` needs a matching join in your adapter query.

Rows must be arrays or objects.

## DateColumnType

Formats a `DateTimeInterface`. Any other value is cast to string.

| Option | Type | Default |
| --- | --- | --- |
| `format` | `string` | `d/m/Y` |

```php
$builder->add('launchedAt', DateColumnType::class, ['format' => 'd/m/Y H:i', 'order' => 'DESC']);
```

## BooleanColumnType

Renders a green *Yes* or red *No* badge.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `yes_value` | `string` | `label.yes` translated | |
| `no_value` | `string` | `label.no` translated | |
| `yes_icon` | `string` | `mdi mdi-check me-1` | |
| `no_icon` | `string` | `mdi mdi-cancel me-1` | |
| `strict_comparison` | `bool` | `false` | Render nothing for values that are not strictly `bool` — useful to leave `null` blank |

## BadgeColumnType

Wraps the value in a Bootstrap badge. Empty values render nothing. The value is escaped.

| Option | Type | Default |
| --- | --- | --- |
| `badge_class` | `?string` | `bg-primary` |

## DetailsColumnType

An expand toggle that opens a child row under the current one.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `render_details` | `callable` | **required** | `fn ($row, array $options): string` — HTML of the child row. Return `''` for no toggle |

Its label defaults to `null`.

```php
$builder->add('details', DetailsColumnType::class, [
    'render_details' => fn (Mission $m) => $this->twig->render('mission/_details.html.twig', ['mission' => $m]),
]);
```

The child HTML is rendered for every row up front and embedded in the page, so keep it light.

## ActionColumnType

A cell of action links per row, built with a `ColumnActionBuilder`.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `build` | `callable` | **required** | `fn (ColumnActionBuilder $actions, $row, array $options)` |

Its label defaults to `null` and its class to `text-end text-nowrap`.

```php
$builder->add('__actions__', ActionColumnType::class, [
    'build' => function (ColumnActionBuilder $actions, Mission $mission) {
        $actions->editLink(['route' => 'mission_edit', 'route_params' => ['id' => $mission->id]]);
        if (!$mission->locked) {
            $actions->deleteLink(['route' => 'mission_delete', 'route_params' => ['id' => $mission->id]]);
        }
    },
]);
```

`ColumnActionBuilder` is documented under [Actions](component/datatable/actions).

## The base ColumnType

`ColumnType` itself renders the row cast to a string. It is mostly useful for fully custom
rendering through `render` or `render_html`, when no property is involved:

```php
use Poncho\AdminBundle\Lib\DataTable\Column\ColumnType;

$builder->add('summary', ColumnType::class, [
    'render' => fn (Mission $m) => sprintf('%s (%d crew)', $m->name, count($m->crew)),
]);
```

To write your own reusable column type, see [Extending: DataTable](extending/datatable).
