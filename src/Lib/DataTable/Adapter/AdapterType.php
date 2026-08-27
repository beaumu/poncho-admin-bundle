<?php

namespace Poncho\AdminBundle\Lib\DataTable\Adapter;

use Poncho\AdminBundle\Lib\DataTable\AdapterException;
use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableResult;
use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableState;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AdapterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    /**
     * @throws AdapterException
     */
    abstract public function getResult(DataTableState $state, array $options): DataTableResult;
}
