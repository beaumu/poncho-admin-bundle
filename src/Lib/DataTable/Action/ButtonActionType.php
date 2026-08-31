<?php

namespace Poncho\AdminBundle\Lib\DataTable\Action;

use Poncho\AdminBundle\Utils\Utils;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ButtonActionType extends LinkActionType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefault('class', 'btn btn-primary')
            ->setDefault('text', static fn (Options $options) => Utils::humanize($options['name']));
    }
}
