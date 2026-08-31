<?= "<?php\n"; ?>

namespace <?= $namespace ?>;

use <?= $entity->getFullName() ?>;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
                $builder->moveLinks([
                    'route' => '<?= $route['name_prefix'] ?>_move',
                    'route_params' => ['id' => $e->id]
                ]);
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

        $builder->useNestedEntityAdapter([
            'class' => <?= $entity->getShortName() ?>::class,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'tree' => true
        ]);
    }
}
