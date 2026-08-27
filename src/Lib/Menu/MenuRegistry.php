<?php

namespace Poncho\AdminBundle\Lib\Menu;

use Poncho\AdminBundle\Lib\Menu\Visitor\MenuVisitor;

class MenuRegistry
{
    public const TAG_TYPE = 'poncho.menu.type';
    public const TAG_VISITOR = 'poncho.menu.visitor';

    /**
     * @var MenuType[]
     */
    protected array $types = [];

    /**
     * @var MenuVisitor[]
     */
    protected array $visitors = [];

    public function registerType(string $name, MenuType $type): void
    {
        $this->types[$name] = $type;
    }

    public function getType(string $name): MenuType
    {
        if (!isset($this->types[$name])) {
            throw new \InvalidArgumentException(\sprintf('Menu "%s" doesn\'t exist, maybe you have forget to register it ?', $name));
        }

        return $this->types[$name];
    }

    public function registerVisitor(string $name, MenuVisitor $visitor): void
    {
        $this->visitors[$name] = $visitor;
    }

    public function getVisitor(string $name): MenuVisitor
    {
        if (!isset($this->visitors[$name])) {
            throw new \InvalidArgumentException(\sprintf('MenuVisitor "%s" doesn\'t exist, maybe you have forget to register it ?', $name));
        }

        return $this->visitors[$name];
    }
}
