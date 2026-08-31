<?= "<?php\n"; ?>

namespace <?= $namespace ?>;

use <?= $entity->getFullName() ?>;
use Doctrine\ORM\QueryBuilder;
use Poncho\AdminBundle\Lib\Form\SearchType;
use Poncho\AdminBundle\Lib\DataTable\Action\ButtonAddActionType;
use Poncho\AdminBundle\Lib\DataTable\Column\ActionColumnType;
use Poncho\AdminBundle\Lib\DataTable\Column\PropertyColumnType;
use Poncho\AdminBundle\Lib\DataTable\ColumnActionBuilder;
use Poncho\AdminBundle\Lib\DataTable\DataTableBuilder;
use Poncho\AdminBundle\Lib\DataTable\DataTableType;

class <?= $class_name ?> extends DataTableType
{
    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        $builder->addFilter('search', SearchType::class);

        $builder->addAction('add', ButtonAddActionType::class, [
            'route' => '<?= $route['name_prefix'] ?>_edit',
<?php if ('modal' === $edit_view_type) { ?>
            'xhr' => true
<?php } ?>
        ]);

        $builder->add('id', PropertyColumnType::class, [
            'render' => fn(<?= $entity->getShortName() ?> $e) => sprintf('# %d', $e->id)
        ]);
        $builder->add('__action__', ActionColumnType::class, [
            'build' => function (ColumnActionBuilder $builder, <?= $entity->getShortName() ?> $e) {
                $builder->editLink([
                    'route' => '<?= $route['name_prefix'] ?>_edit',
                    'route_params' => ['id' => $e->id],
<?php if ('modal' === $edit_view_type) { ?>
                    'xhr' => true
<?php } ?>
                ]);
                $builder->deleteLink([
                    'route' => '<?= $route['name_prefix'] ?>_delete',
                    'route_params' => ['id' => $e->id]
                ]);
            }
        ]);

        $builder->useEntityAdapter([
            'class' => <?= $entity->getShortName() ?>::class,
            'query' => function(QueryBuilder $qb, array $formData) {

                // TODO : add closure to filter result depending on $formData['search'] value
                if (isset($formData['search'])) {
                    // You can use Poncho\AdminBundle\Utils\DoctrineUtils to filter results
                    // DoctrineUtils::matchAll($qb, ['e.x', 'e.y'], $formData['search']);
                }

            }
        ]);
    }

}
