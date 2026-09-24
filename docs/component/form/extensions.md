# Form extensions

Poncho registers two form type extensions. Being extensions, they add options to **every** form in
your application, not only the admin's.

## Input addons — every FormType

Put content before or after a field, in a Bootstrap input group:

| Option | Type | Default | |
| --- | --- | --- | --- |
| `input_prefix_text` | `?string` | `null` | Text before the field, in an `input-group-text` |
| `input_suffix_text` | `?string` | `null` | Text after the field |
| `input_prefix` | `?string` | `null` | Raw HTML before the field |
| `input_suffix` | `?string` | `null` | Raw HTML after the field |
| `input_addon_container_class` | `string` | `input-group` | Class of the wrapping element |

```php
$builder->add('budget', MoneyType::class, ['currency' => false, 'input_prefix_text' => '€']);
$builder->add('weight', NumberType::class, ['input_suffix_text' => 'kg']);
$builder->add('website', UrlType::class, [
    'input_prefix' => '<span class="input-group-text"><i class="mdi mdi-web"></i></span>',
]);
```

`input_prefix_text` wins over `input_prefix` when both are set.

!> Both the `*_text` and the raw variants are inserted as HTML. Keep them to static strings.

## Horizontal layout classes — every FormType

| Option | Type | Default | |
| --- | --- | --- | --- |
| `label_class` | `?string` | `poncho_admin.form.label_class` | Column class of the label |
| `group_class` | `?string` | `poncho_admin.form.group_class` | Column class of the field |

Only used by the horizontal theme. A field without its own value inherits the **root** form's, then
the configured default. See [Form theme](component/form/theme#horizontal-layout).

## Autocomplete — ChoiceType and EntityType

| Option | Type | Default | |
| --- | --- | --- | --- |
| `ub_autocomplete` | `bool` | `false` | Render with the Tom Select widget |
| `option_template` | `?string` | `null` | Mustache template of each option |
| `input_template` | `?string` | `null` | Mustache template of the selected item(s) |
| `tom_select_settings` | `array` | `[]` | Extra Tom Select settings |

```php
$builder->add('rocket', EntityType::class, [
    'class' => Rocket::class,
    'ub_autocomplete' => true,
    'option_template' => '{{text}} <small class="text-muted">#{{value}}</small>',
]);
```

All options are rendered into the page up front. For a large table, use
[`AutoCompleteType`](component/form/types#autocompletetype), which loads them on demand.

The `ub_` prefix is a leftover from the bundle's Umbrella origins; the name is kept for backward
compatibility.

!> **Known issue: `expanded` is forced to `false` on every choice field.** The extension's
normaliser applies whether or not `ub_autocomplete` is set, so any `ChoiceType`, `EnumType` or
`EntityType` with `expanded: true` in your application renders as a `<select>` instead of radio
buttons or checkboxes. The fix is a one-line change to that normaliser.
