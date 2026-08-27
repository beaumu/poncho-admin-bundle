<?php

namespace Poncho\AdminBundle\Lib\DataTable\Adapter;

use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableResult;
use Poncho\AdminBundle\Lib\DataTable\DTO\DataTableState;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CallableAdapterType extends AdapterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('callable')
            ->setAllowedTypes('callable', 'callable');
    }

    public function getResult(DataTableState $state, array $options): DataTableResult
    {
        return \call_user_func($options['callable'], $state);
    }
}
