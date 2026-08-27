<?php

namespace Poncho\AdminBundle\Lib\Form\Extension;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutoCompleteExtension extends AbstractTypeExtension
{
    public const AUTOCOMPLETE_OPTION = 'ub_autocomplete';

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options[self::AUTOCOMPLETE_OPTION]) {
            $attr = $view->vars['attr'] ?? [];
            unset($attr['placeholder']);

            $attr['is'] = 'poncho-autocomplete';

            $attr['data-tom-select-settings'] = json_encode($options['tom_select_settings']);

            $attr['data-input-template'] = $options['input_template'];
            $attr['data-option-template'] = $options['option_template'];

            $view->vars['attr'] = $attr;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault(self::AUTOCOMPLETE_OPTION, false)
            ->setAllowedTypes(self::AUTOCOMPLETE_OPTION, 'bool');

        $resolver
            ->setDefault('option_template', null)
            ->setAllowedTypes('option_template', ['null', 'string']);

        $resolver
            ->setDefault('input_template', null)
            ->setAllowedTypes('input_template', ['null', 'string']);

        $resolver
            ->setNormalizer('expanded', fn (Options $options) => false)
            ->setNormalizer('placeholder', fn (Options $options, $value) => $value);

        $resolver
            ->setDefault('tom_select_settings', [])
            ->setAllowedTypes('tom_select_settings', 'array');
    }

    public static function getExtendedTypes(): iterable
    {
        return [ChoiceType::class, EntityType::class];
    }
}
