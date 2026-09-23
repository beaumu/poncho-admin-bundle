# Extending: DataTable

Four kinds of type make up a table. Each can be written in your application and used exactly like
the built-in ones. Read [the rules every type follows](extending/index#the-rules-every-type-follows)
first — they apply to all four.

The examples on this page together build one working table: invoices read from an array, with a
money column and an export button.

## Table types

A table type is already a class you write for every table. Two things make one reusable.

**Options.** Declare them in `configureOptions()`, read them in `buildTable()`, and pass them when
creating the table:

```php
class InvoiceTableType extends DataTableType
{
    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        $builder->add('amount', MoneyColumnType::class, ['currency' => $options['currency']]);
        // …
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('currency', 'EUR')
            ->setAllowedTypes('currency', 'string');
    }
}
```

```php
$table = $this->createTable(InvoiceTableType::class, ['currency' => 'USD']);
```

`configureOptions()` may also change the default of any [table option](component/datatable/options)
— `page_length`, `selectable`, `id_path`… — for every use of the type.

**Inheritance.** Put what several tables share in a base class and call `parent::buildTable()`:

```php
abstract class AuditedTableType extends DataTableType
{
    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        $builder->add('createdAt', DateColumnType::class, ['label' => 'Created']);
        $builder->add('createdBy');
    }
}

class InvoiceTableType extends AuditedTableType
{
    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        $builder->add('number');
        parent::buildTable($builder, $options);   // columns appear in call order
    }
}
```

Make the base class `abstract`, or it is registered as a table of its own. The builder's
`remove()`, `removeFilter()`, `removeAction()` and `clearAdapter()` let a child undo what a parent
added.

The table id — and so the toolbar form name and the `<poncho-datatable>` element id — comes from the
class name: `App\DataTable\InvoiceTableType` becomes `app_datatable_invoicetable`. Two tables of
the same type on one page need distinct ids; pass `['id' => 'invoices_paid']`.

## Column types

A column type turns a row into the HTML of one cell.

```php
namespace App\DataTable\Column;

use Poncho\AdminBundle\Lib\DataTable\Column\PropertyColumnType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyColumnType extends PropertyColumnType
{
    public function renderProperty(mixed $value, array $options): string
    {
        if (null === $value) {
            return '';
        }

        return \sprintf(
            '<span class="text-nowrap">%s %s</span>',
            number_format($value / 100, 2, ',', '.'),
            htmlspecialchars($options['currency'])
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('is_safe_html', true)
            ->setDefault('class', 'text-end')
            ->setDefault('currency', 'EUR')
            ->setAllowedTypes('currency', 'string');
    }
}
```

```php
$builder->add('amount', MoneyColumnType::class, ['currency' => 'USD']);
```

**Which class to extend:**

| Extend | Override | When |
| --- | --- | --- |
| `PropertyColumnType` | `renderProperty(mixed $value, array $options)` | The cell shows one property of the row — the common case. You get the value already read through `property_path` |
| `ColumnType` | `render(mixed $rowData, array $options)` | The cell combines several properties, or the row is not an object or array |
| Any built-in, e.g. `BadgeColumnType` | `configureOptions()` only | You want the same rendering with other defaults |

**Escaping.** Whatever `render()` returns is escaped with `htmlspecialchars()` unless the
`is_safe_html` option is `true`. A type that returns markup sets it to `true` as above — and must
then escape every value it inserts itself, as the example does with the currency.

**What you get in `$options`:** your own options plus every column option — `name`, `label`,
`class`, `width`, `order`, `order_by`, `translation_domain` — and, for `PropertyColumnType`,
`property_path` and `property_accessor`.

**The `render` option wins.** If the caller passes `render` or `render_html`, that callable is used
and your type's `render()` is not called at all.

**Sorting.** A column sorts only when `order` is not `false`. `PropertyColumnType` makes columns
sortable by default (`order: null`, `order_by` = the property path); a type computing its value
should set `order` back to `false` or give a meaningful `order_by`.

## Action types

An action type renders one button or link: in the toolbar through `addAction()`, or in a row through
`ColumnActionBuilder::add()`.

The simplest reuse is a preset of `ButtonActionType` (toolbar) or `LinkActionType` (rows):

```php
namespace App\DataTable\Action;

use Poncho\AdminBundle\Lib\DataTable\Action\ButtonActionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportActionType extends ButtonActionType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('class', 'btn btn-light')
            ->setDefault('icon', 'mdi mdi-download')
            ->setDefault('text', 'Export')
            ->setDefault('send_state', true);   // the controller receives the filters and selection
    }
}
```

```php
$builder->addAction('export', ExportActionType::class, ['route' => 'invoice_export']);

// in a row
$builder->add('actions', ActionColumnType::class, [
    'build' => function (ColumnActionBuilder $actions, Invoice $invoice) {
        $actions->add(ExportActionType::class, ['route' => 'invoice_export', 'route_params' => ['id' => $invoice->id]]);
    },
]);
```

For markup of your own, extend `ActionType` and implement `render()`:

```php
use Poncho\AdminBundle\Lib\DataTable\Action\ActionType;
use Twig\Environment;

class HelpActionType extends ActionType
{
    public function render(Environment $twig, array $options): string
    {
        return $twig->render('datatable/action/help.html.twig', $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('topic')->setAllowedTypes('topic', 'string');
    }
}
```

The result is inserted **unescaped**, so escape in the template (Twig does by default). To reuse the
bundle's attribute and icon rendering, `{% use "@PonchoAdmin/lib/datatable/action/helper.html.twig" %}`
and look at `@PonchoAdmin/lib/datatable/action/link.html.twig`.

Every action has `name` and `send_state`. `send_state` only has an effect in types that read it, as
`LinkActionType` does — see [Actions](component/datatable/actions) for what it sends.

!> Always pass a type to `addAction()` and `ColumnActionBuilder::add()`. Their default,
`ActionType`, is abstract and therefore not registered: leaving it out throws
`ActionType "…ActionType" doesn't exist`.

## Adapter types

An adapter type fetches the rows for one draw of the table.

```php
namespace App\DataTable\Adapter;

use Poncho\AdminBundle\Lib\DataTable\Adapter\AdapterType;
use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableResult;
use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableState;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArrayAdapterType extends AdapterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('rows')
            ->setAllowedTypes('rows', 'array');
    }

    public function getResult(DataTableState $state, array $options): DataTableResult
    {
        $rows = $options['rows'];

        // filters
        $search = $state->getFormData()['search'] ?? null;
        if ($search) {
            $rows = array_values(array_filter($rows, static fn (array $row) => str_contains($row['name'], $search)));
        }

        // paging: -1 means "all rows"
        $length = $state->getLength() >= 0 ? $state->getLength() : null;

        return new DataTableResult(\array_slice($rows, $state->getStart(), $length), \count($rows));
    }
}
```

```php
$builder->useAdapter(ArrayAdapterType::class, ['rows' => $rows]);
```

`getResult()` receives the [`DataTableState`](component/datatable/adapters#datatablestate) — offset,
length, sort order and filter values — and returns a
[`DataTableResult`](component/datatable/adapters#datatableresult): the rows of the current page and
the total count **before** paging. Honour all four; the table trusts what you return. Sorting comes
as a list of `['order_by' => string[], 'direction' => …]`, where `order_by` holds the columns'
`order_by` values; `direction` is passed through as sent by the browser, normally lower-case.

To report a failure to the user, throw `AdapterException` — see
[Errors](component/datatable/adapters#errors).

**Rows that are arrays.** Columns and the table read rows through the PropertyAccess component,
which needs bracket paths for arrays. The table reads `id_path` (default `id`) on every row, so a
table fed arrays fails with `Cannot read property "id" from an array` until you set both:

```php
// the table type
$resolver->setDefault('id_path', '[id]');   // or null, if rows have no id

// each column
$builder->add('name', options: ['property_path' => '[name]']);
```

Returning objects avoids both.

**Doctrine-based adapters.** Extend `DoctrineAdapterType` instead of `AdapterType` and implement
`getQueryBuilder()` as well: it gives you the `class`, `em`, `query_alias` and `query` options, and
lets controllers call `$table->getAdapterQueryBuilder()` — used for exports and bulk actions. Or
extend `EntityAdapterType` and override only `getQueryBuilder()`, calling the parent first, to keep
its paging and sorting.

## Testing your types

Types are services, so a `KernelTestCase` reaches them through the factory:

```php
use Poncho\AdminBundle\Lib\DataTable\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;

$factory = self::getContainer()->get(DataTableFactory::class);

// a column on its own
$column = $factory->createColumn('amount', MoneyColumnType::class, [
    'currency' => 'USD',
    'property_path' => '[amount]',   // the row below is an array
]);
$this->assertSame('<span class="text-nowrap">1.234,56 USD</span>', $column->render(['amount' => 123456]));
```

```php
// a whole table, as the browser would query it
$table = $factory->create(InvoiceTableType::class);
$table->handleRequest(Request::create('/', 'POST', [
    '_dtid' => $table->getId(),
    'start' => 0,
    'length' => 25,
    $table->getOption('toolbar_form_name') => ['search' => 'alpha'],
]));

$json = json_decode($table->getCallbackResponse()->getContent(), true);
$this->assertSame(2, $json['recordsTotal']);
```
