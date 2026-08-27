<?php

namespace Poncho\AdminBundle\DataTable;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\RouterInterface;
use Poncho\AdminBundle\Entity\BaseAdminUser;
use Poncho\AdminBundle\Lib\DataTable\Action\ButtonAddActionType;
use Poncho\AdminBundle\Lib\DataTable\Column\ActionColumnType;
use Poncho\AdminBundle\Lib\DataTable\Column\BooleanColumnType;
use Poncho\AdminBundle\Lib\DataTable\Column\ColumnType;
use Poncho\AdminBundle\Lib\DataTable\Column\DateColumnType;
use Poncho\AdminBundle\Lib\DataTable\Column\PropertyColumnType;
use Poncho\AdminBundle\Lib\DataTable\ColumnActionBuilder;
use Poncho\AdminBundle\Lib\DataTable\DataTableBuilder;
use Poncho\AdminBundle\Lib\DataTable\DataTableType;
use Poncho\AdminBundle\Lib\Form\SearchType;
use Poncho\AdminBundle\PonchoAdminConfiguration;
use Poncho\AdminBundle\Utils\DoctrineUtils;

class UserTableType extends DataTableType
{
    public function __construct(protected PonchoAdminConfiguration $config, protected RouterInterface $router)
    {
    }

    public function buildTable(DataTableBuilder $builder, array $options): void
    {
        $builder->addFilter('search', SearchType::class);
        $builder->addAction('add', ButtonAddActionType::class, [
            'route' => 'poncho_admin_user_edit',
            'text' => 'action.add_user',
            'xhr' => true,
            'translation_domain' => 'PonchoAdmin'
        ]);

        $builder->add('name', ColumnType::class, [
            'render_html' => fn (BaseAdminUser $user) => \sprintf(
                '<a href data-xhr="%s">%s</a>',
                $this->router->generate('poncho_admin_user_edit', ['id' => $user->id]),
                htmlspecialchars($user->getFullName())
            ),
            'order' => 'ASC',
            'order_by' => ['firstname', 'lastname'],
            'label' => 'label.name',
            'translation_domain' => 'PonchoAdmin'
        ]);
        $builder->add('email', PropertyColumnType::class, [
            'label' => 'label.email',
            'translation_domain' => 'PonchoAdmin'
        ]);
        $builder->add('createdAt', DateColumnType::class, [
            'label' => 'label.created_at',
            'translation_domain' => 'PonchoAdmin'
        ]);
        $builder->add('active', BooleanColumnType::class, [
            'label' => 'label.active',
            'translation_domain' => 'PonchoAdmin'
        ]);
        $builder->add('__action__', ActionColumnType::class, [
            'build' => function (ColumnActionBuilder $builder, BaseAdminUser $e) {
                $builder->editLink([
                    'route' => 'poncho_admin_user_edit',
                    'route_params' => ['id' => $e->id],
                    'xhr' => true
                ]);
                $builder->deleteLink([
                    'route' => 'poncho_admin_user_delete',
                    'route_params' => ['id' => $e->id]
                ]);
            }
        ]);

        $builder->useEntityAdapter([
            'class' => $this->config->userClass(),
            'query' => function (QueryBuilder $qb, $formData) {
                if (isset($formData['search'])) {
                    DoctrineUtils::matchAll($qb, ['e.firstname', 'e.lastname', 'e.email'], $formData['search']);
                }
            }
        ]);
    }
}
