<?php

namespace Poncho\AdminBundle\Lib\Form;

use Poncho\AdminBundle\Lib\Form\Subscriber\AutocompleteTypeSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class AutoCompleteType extends AbstractType implements DataMapperInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        // Add a custom block prefix to inner field to ease theming:
        array_splice($view['autocomplete']->vars['block_prefixes'], -1, 0, 'poncho_autocomplete_inner');
        // this IS A compound (i.e. has children) field
        // however, we only render the child "autocomplete" field. So for rendering, fake NOT compound
        // This is a hack and we should check into removing it in the future
        $view->vars['compound'] = false;
        // the above, unfortunately, can also trick other things that might use
        // "compound" for other reasons. This, at least, leaves a hint.
        $view->vars['compound_data'] = true;

        $attr = $view->vars['attr'] ?? [];

        $attr['is'] = 'poncho-autocomplete';

        $attr['data-tom-select-settings'] = json_encode($options['tom_select_settings']);

        $attr['data-input-template'] = $options['input_template'];
        $attr['data-option-template'] = $options['option_template'];
        $attr['data-load-url'] = !empty($options['url']) ? $options['url'] : $this->urlGenerator->generate($options['route'], $options['route_params']);
        $attr['data-min-char'] = $options['min_characters'];

        $view->vars['attr'] = $attr;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (empty($options['route']) && empty($options['url'])) {
            throw new \LogicException('Yout must define either option "route" or "url".');
        }

        $builder
            ->addEventSubscriber(new AutocompleteTypeSubscriber())
            ->setDataMapper($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'multiple' => false,
            // force display errors on this form field
            'error_bubbling' => false
        ]);

        $resolver
            ->setDefault('option_template', null)
            ->setAllowedTypes('option_template', ['null', 'string']);

        $resolver
            ->setDefault('input_template', null)
            ->setAllowedTypes('input_template', ['null', 'string']);

        $resolver
            ->setDefault('tom_select_settings', [])
            ->setAllowedTypes('tom_select_settings', 'array');

        $resolver
            ->setDefault('url', null)
            ->setAllowedTypes('url', ['null', 'string']);

        $resolver
            ->setDefault('route', null)
            ->setAllowedTypes('route', ['null', 'string']);

        $resolver
            ->setDefault('route_params', [])
            ->setAllowedTypes('route_params', 'array');

        $resolver
            ->setDefault('min_characters', 1)
            ->setAllowedTypes('min_characters', 'int');

        $resolver->setRequired(['class']);
    }

    public function getBlockPrefix(): string
    {
        return 'poncho_autocomplete';
    }

    public function mapDataToForms(mixed $viewData, \Traversable $forms): void
    {
        $form = current(iterator_to_array($forms, false));
        $form->setData($viewData);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        $form = current(iterator_to_array($forms, false));
        $viewData = $form->getData();
    }
}
