# Form theme

Poncho's form types and extensions need one of its form themes to render correctly. Both extend
Symfony's Bootstrap 5 themes:

| Theme | Extends |
| --- | --- |
| `@PonchoAdmin/lib/form/layout.html.twig` | `bootstrap_5_layout.html.twig` |
| `@PonchoAdmin/lib/form/layout_horizontal.html.twig` | `bootstrap_5_horizontal_layout.html.twig` |

## For every form

```yaml
# config/packages/twig.yaml
twig:
    form_themes: ['@PonchoAdmin/lib/form/layout.html.twig']
```

## For one form: `poncho_form_theme()`

```twig
{{ poncho_form_theme(form) }}
{{ form_start(form) }}
    {{ form_rest(form) }}
{{ form_end(form) }}
```

```
poncho_form_theme(FormView $form, ?string $layout = null, bool $useDefaultThemes = true)
```

| Argument | |
| --- | --- |
| `$layout` | `'default'` or `'horizontal'`. `null` uses `poncho_admin.form.layout` |
| `$useDefaultThemes` | Keep the globally configured themes as fallbacks, as Twig's own `form_theme` does |

The bundle's templates — the edit page, every form modal, the datatable toolbar, the login and
profile pages — already call it, so their forms follow your configured layout.

## Horizontal layout

In the horizontal layout, labels and fields sit side by side in a Bootstrap grid. The column classes
default from configuration:

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    form:
        layout: horizontal       # default | horizontal
        label_class: col-sm-2
        group_class: col-sm-10
```

and can be overridden per form or per field with the `label_class` and `group_class` options.
A value set on the root form applies to all its fields:

```php
$form = $this->createForm(MissionType::class, $mission, [
    'label_class' => 'col-md-3',
    'group_class' => 'col-md-9',
]);

// or on a single field
$builder->add('description', TextareaType::class, ['label_class' => 'col-12', 'group_class' => 'col-12']);
```

See [Form extensions](component/form/extensions).
