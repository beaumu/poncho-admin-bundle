<?= "<?php\n"; ?>

namespace <?= $namespace ?>;

use Poncho\AdminBundle\Menu\BaseAdminMenu;
use Poncho\AdminBundle\Lib\Menu\Builder\MenuBuilder;

class AdminMenu extends BaseAdminMenu
{

    public function buildMenu(MenuBuilder $builder, array $options): void
    {
        $builder->root()
            ->add('Home')
                ->icon('uil-home')
                ->route('<?= $route['name_prefix'] ?>_index');
    }

}