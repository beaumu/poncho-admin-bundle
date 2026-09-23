# Tree tables

A tree table shows hierarchical rows — categories, org charts, nested menus — indented under their
parent, with a caret to expand and collapse each branch.

## Requirements

The row data must be a **nested set**: each node carries a left value, a depth and a parent. The
usual way to get one is Gedmo's `Tree` extension through
[StofDoctrineExtensionsBundle](https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/index.html),
which Poncho does **not** install for you:

```bash
composer require stof/doctrine-extensions-bundle
```

```yaml
# config/packages/stof_doctrine_extensions.yaml
stof_doctrine_extensions:
    orm:
        default:
            tree: true
```

`make:admin:tree` checks for the bundle and refuses to run until it is installed.

## The fastest route

```bash
php bin/console make:admin:tree
```

generates a nested-set entity, its `NestedTreeRepository`, a form, a tree table type and a
controller with add, edit, move and delete actions. See [Makers](makers).

## By hand

```php
use Poncho\AdminBundle\Lib\DataTable\DataTableBuilder;
use Poncho\AdminBundle\Lib\DataTable\DataTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryTableType extends DataTableType
{
    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        $builder->add('name');
        $builder->add('__actions__', ActionColumnType::class, [
            'build' => function (ColumnActionBuilder $actions, Category $category) {
                $actions->moveLinks([
                    'route' => 'category_move',
                    'route_params' => ['id' => $category->id],
                ]);
            },
        ]);

        $builder->useNestedEntityAdapter(Category::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('tree', true);
    }
}
```

`tree: true` switches the defaults of `paging` and `orderable` to `false` and turns on the tree
rendering. [`NestedEntityAdapterType`](component/datatable/adapters#nestedentityadaptertype) loads every node
in left-value order, skipping the root.

## How the tree is built

Each row carries two attributes:

- `data-id`, from the `id_path` option (default `id`),
- `data-parent-id`, from `parent_path` + `id_path` (default `parent.id`).

The client links rows through them, so **rows must arrive parents-first** — which left-value order
guarantees. A row whose parent is not in the result set is shown at the top level.

| Option | Default | |
| --- | --- | --- |
| `tree` | `false` | |
| `id_path` | `id` | |
| `parent_path` | `parent` | |
| `tree_column_index` | `0` | Column receiving the indentation and caret |

Each level is indented by 40 px. That width is not configurable from PHP.

!> With `tree_column_index` other than `0`, branches still expand and collapse, but the caret's
open/closed icon does not update: the client toggles it on the first cell instead of the tree
column.

## Starting expanded

Branches start collapsed. Expand one by setting `collapsed` to `false` on its row:

```php
public function buildRowView(RowView $view, DataTable $dataTable, array $options): void
{
    $view->collapsed = $view->source->level > 2;
}
```

## Reordering

`moveUpLink()`, `moveDownLink()` and `moveLinks()` send an XHR to your route with
`direction: up|down` added to `route_params`. With Gedmo's `NestedTreeRepository`:

```php
#[Route('/category/move/{id}/{direction}')]
public function move(CategoryRepository $repository, int $id, string $direction): Response
{
    $category = $this->findOrNotFound(Category::class, $id);

    'up' === $direction ? $repository->moveUp($category) : $repository->moveDown($category);

    return $this->js()->reloadTable();
}
```

## Picking a parent in a form

Use [`NestedEntityType`](component/form/types#nestedentitytype): a tree-aware select that indents options by
level and can disable a node and its descendants — so a category cannot be made its own parent.
