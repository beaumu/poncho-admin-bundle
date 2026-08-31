<?php

namespace Poncho\AdminBundle\Menu;

use Poncho\AdminBundle\Lib\Menu\Builder\MenuBuilder;
use Poncho\AdminBundle\Lib\Menu\DTO\Breadcrumb;
use Poncho\AdminBundle\Lib\Menu\DTO\Menu;
use Poncho\AdminBundle\Lib\Menu\MenuType;
use Poncho\AdminBundle\PonchoAdminConfiguration;
use Twig\Environment;

class BaseAdminMenu extends MenuType
{
    protected array $defaultRenderOptions;
    protected array $defaultBreadcrumbRenderOptions;

    public function __construct(protected Environment $twig, protected PonchoAdminConfiguration $configuration)
    {
        $this->defaultRenderOptions = [
            'logo_route' => null,
            'logo' => $this->configuration->appLogo(),
            'title' => $this->configuration->appName(),
            'searchable' => false,
            'template' => '@PonchoAdmin/menu/sidebar.html.twig'
        ];

        $this->defaultBreadcrumbRenderOptions = [
            'template' => '@PonchoAdmin/menu/breadcrumb.html.twig'
        ];
    }

    public function buildMenu(MenuBuilder $builder, array $options): void
    {
    }

    public function renderMenu(Menu $menu, array $options): string
    {
        $options = array_merge($this->defaultRenderOptions, $options);

        return $this->twig->render($options['template'], [
            'menu' => $menu,
            'options' => $options
        ]);
    }

    public function renderBreadcrumb(Breadcrumb $breadcrumb, array $options): string
    {
        $options = array_merge($this->defaultBreadcrumbRenderOptions, $options);

        return $this->twig->render($options['template'], [
            'breadcrumb' => $breadcrumb,
            'options' => $options
        ]);
    }
}
