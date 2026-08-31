<?php

namespace Poncho\AdminBundle\Lib\DataTable\DTO;

use Poncho\AdminBundle\Lib\DataTable\Action\ActionType;
use Twig\Environment;

class Action
{
    public function __construct(protected ActionType $type, protected array $options)
    {
    }

    public function getType(): ActionType
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function render(Environment $twig): string
    {
        return $this->type->render($twig, $this->options);
    }
}
