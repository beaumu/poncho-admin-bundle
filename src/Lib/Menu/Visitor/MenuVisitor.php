<?php

namespace Poncho\AdminBundle\Lib\Menu\Visitor;

use Poncho\AdminBundle\Lib\Menu\DTO\Menu;

interface MenuVisitor
{
    public function visit(Menu $menu): void;
}
