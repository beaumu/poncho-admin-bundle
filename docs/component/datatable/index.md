# DataTable

A DataTable is a server-side table: paging, sorting and filtering all run in your PHP code, and the
browser only ever holds the current page. It is rendered by [datatables.net](https://datatables.net)
on the client, wrapped in a `<poncho-datatable>` custom element.

This page walks through a complete example. The rest of the section is reference:

| Page | Covers |
| --- | --- |
| [Table options](component/datatable/options) | Every option of `DataTableType`, and the `buildRowView()` hook |
| [Columns](component/datatable/columns) | The built-in column types and their options |
| [Actions](component/datatable/actions) | Toolbar buttons and per-row action links |
| [Adapters](component/datatable/adapters) | Where rows come from: Doctrine, nested sets, or any PHP callable |
| [Filters](component/datatable/filters) | The toolbar form and full-text search |
| [Selection & bulk actions](component/datatable/selection) | Selectable rows and actions over a selection |
| [Tree tables](component/datatable/tree) | Hierarchical data |
| [JavaScript](component/datatable/javascript) | The `<poncho-datatable>` element and its API |

## 1. The table type

```php
namespace App\DataTable;

use App\Entity\Mission;
use Doctrine\ORM\QueryBuilder;
use Poncho\AdminBundle\Lib\DataTable\Action\ButtonAddActionType;
use Poncho\AdminBundle\Lib\DataTable\Column\ActionColumnType;
use Poncho\AdminBundle\Lib\DataTable\Column\DateColumnType;
use Poncho\AdminBundle\Lib\DataTable\ColumnActionBuilder;
use Poncho\AdminBundle\Lib\DataTable\DataTableBuilder;
use Poncho\AdminBundle\Lib\DataTable\DataTableType;
use Poncho\AdminBundle\Lib\Form\SearchType;
use Poncho\AdminBundle\Utils\DoctrineUtils;

class MissionTableType extends DataTableType
{
    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        // toolbar
        $builder->addFilter('search', SearchType::class);
        $builder->addAction('add', ButtonAddActionType::class, [
            'route' => 'app_admin_mission_edit',
            'xhr' => true,
        ]);

        // columns
        $builder->add('name');
        $builder->add('launchedAt', DateColumnType::class, ['order' => 'DESC']);
        $builder->add('__actions__', ActionColumnType::class, [
            'build' => function (ColumnActionBuilder $actions, Mission $mission) {
                $actions->editLink([
                    'route' => 'app_admin_mission_edit',
                    'route_params' => ['id' => $mission->id],
                    'xhr' => true,
                ]);
            },
        ]);

        // data
        $builder->useEntityAdapter([
            'class' => Mission::class,
            'query' => function (QueryBuilder $qb, array $formData) {
                if (isset($formData['search'])) {
                    DoctrineUtils::matchAll($qb, ['e.name'], $formData['search']);
                }
            },
        ]);
    }
}
```

`buildTable()` configures three things, in any order:

- **The toolbar** — filters (a Symfony form) and actions (buttons).
- **The columns** — `add(string $name, string $type = PropertyColumnType::class, array $options = [])`.
  With no type, a column reads the property of the same name.
- **The adapter** — exactly one. Here, a Doctrine query over `Mission`, aliased `e`.

A table with no adapter throws `You must configure an adapter.` when it is built.

## 2. The controller

One action serves both the page and the table's own AJAX requests:

```php
namespace App\Controller\Admin;

use App\DataTable\MissionTableType;
use Poncho\AdminBundle\Lib\Controller\AdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/mission')]
class MissionController extends AdminController
{
    #[Route('')]
    public function index(Request $request): Response
    {
        $table = $this->createTable(MissionTableType::class);
        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getCallbackResponse();
        }

        return $this->render('admin/mission/index.html.twig', [
            'table' => $table,
        ]);
    }
}
```

`handleRequest()` decides whether this request is aimed at the table. It is, when the HTTP method
matches the table's `method` option (`POST` by default) **and** the request carries `_dtid` equal to
the table's id. On the first page load neither is true, so the page renders normally. Once the page
is up, `<poncho-datatable>` posts back to the same URL and the action returns JSON instead.

`getCallbackResponse()` throws a `LogicException` if you call it without first checking
`isCallback()`.

## 3. The template

```twig
{% extends '@PonchoAdmin/layout.html.twig' %}

{% block content %}
    {{ render_table(table) }}
{% endblock %}
```

That is the whole template. `@PonchoAdmin/datatable.html.twig` does exactly this, so for a page that
is only a table you can render it directly:

```php
return $this->render('@PonchoAdmin/datatable.html.twig', ['table' => $table]);
```

## What happens on the wire

The element posts the DataTables state plus your toolbar fields:

```
_dtid=app_datatable_missiontable
draw=3  start=25  length=25
order[0][column]=1  order[0][dir]=desc
app_datatable_missiontable_tbf[search]=apollo
```

The response is the JSON datatables.net expects:

```json
{
    "draw": 3,
    "recordsTotal": 132,
    "recordsFiltered": 132,
    "data": [
        { "0": "Apollo 11", "1": "16/07/1969", "2": "<a …>", "DT_RowAttr": { "data-id": 11 } }
    ]
}
```

Each row gets a `data-id` attribute read from the object's `id` property (see the `id_path` option).
Selection and tree tables depend on it.

If the adapter throws an `AdapterException`, the response becomes
`{"error": "<message>"}` with status 500 and the message is displayed in the table body.

!> That message is inserted into the page as HTML. Never put user input in an `AdapterException`
message. See [Security](security).

## Using the table outside a request

`submit()` fills the state from an array instead of a `Request`, which is useful for exports and
tests:

```php
$table = $this->createTable(MissionTableType::class);
$table->submit(['start' => 0, 'length' => -1, 'app_datatable_missiontable_tbf' => ['search' => 'apollo']]);

$result = $table->getAdapterResult();          // DataTableResult: getData(), getCount()
$qb     = $table->getAdapterQueryBuilder();     // only for Doctrine adapters
```

`length` of `-1` means no limit.
