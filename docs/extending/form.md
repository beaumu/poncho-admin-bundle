# Extending: form widgets

The bundle's own interactive fields — the date picker, the autocomplete, the password toggle — are
each three pieces, and yours can be built the same way:

1. a **form type**, holding the options and passing them to the template;
2. a **theme block**, rendering the field's HTML;
3. a **custom element**, bringing it to life in the browser.

The custom element is what makes the field work everywhere, including in modals and in HTML
inserted by a [JsResponse](component/jsresponse/index): the browser initialises it whenever it
enters the page, with no event wiring.

The example builds a colour field with preset swatches.

## 1. The form type

```php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ColorPickerType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['swatches'] = $options['swatches'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('swatches', ['#0ea5e9', '#f59e0b', '#10b981'])
            ->setAllowedTypes('swatches', 'string[]');
    }

    public function getParent(): string
    {
        return TextType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'color_picker';
    }
}
```

This is a standard Symfony form type — see Symfony's
[How to Create a Custom Form Field Type](https://symfony.com/doc/current/form/create_custom_field_type.html).
Every [option the bundle adds](component/form/extensions) to all fields — `input_prefix_text`,
`label_class`, `group_class`… — works on it too.

## 2. The theme block

The block prefix above makes Symfony look for a block named `color_picker_widget`:

```twig
{# templates/form/widgets.html.twig #}
{% block color_picker_widget -%}
    <color-picker id="{{ id }}_picker" data-swatches="{{ swatches|json_encode }}">
        {{- block('form_widget_simple') -}}
    </color-picker>
{%- endblock %}
```

Register the file as a form theme for the whole application:

```yaml
# config/packages/twig.yaml
twig:
    form_themes:
        - '@PonchoAdmin/lib/form/layout.html.twig'
        - 'form/widgets.html.twig'
```

Themes registered here are kept when a form is also themed with
[`poncho_form_theme()`](component/form/theme), so the block is found in both cases. Calling
`block('form_widget_simple')` — rather than writing the `<input>` yourself — keeps the bundle's
input groups, so `input_prefix_text: '#'` still renders as an addon.

## 3. The custom element

```js
// assets/admin/ColorPicker.js
export default class ColorPicker extends HTMLElement {
    connectedCallback() {
        this.input = this.querySelector('input')

        const swatches = JSON.parse(this.dataset.swatches || '[]')
        const bar = document.createElement('div')
        bar.className = 'd-flex gap-1 mt-1'

        for (const color of swatches) {
            const button = document.createElement('button')
            button.type = 'button'
            button.className = 'btn btn-sm border'
            button.style.background = color
            button.title = color
            button.addEventListener('click', () => this.setValue(color))
            bar.appendChild(button)
        }

        this.appendChild(bar)
    }

    // public API: usable from other scripts and from JsResponse::call()
    setValue(value) {
        this.input.value = value
        this.input.dispatchEvent(new Event('change', {bubbles: true}))
    }
}
```

```js
// assets/admin.js — your application's admin entry
import ColorPicker from './admin/ColorPicker'

customElements.define('color-picker', ColorPicker)
```

and load that entry after the bundle's scripts — see
[Adding your own JavaScript and CSS](frontend/index#adding-your-own-javascript-and-css).

Some conventions the bundle's elements follow, worth copying:

- **Initialise in `connectedCallback()`**, not the constructor — children are not available yet in
  the constructor when the element is parsed. Clean up timers and listeners on `window` or
  `document` in `disconnectedCallback()`.
- **Pass configuration as `data-*` attributes**, JSON-encoded when structured, and escape them in
  Twig (the default).
- **Expose methods for what callers need.** `$this->js()->call('setValue', ['#000000'], '#form_color_picker')`
  then works from the server — hence the `id` in the theme block.
- **Dispatch `change` on the real input** when the value changes, so other code and the form
  submission see it.
- **Use an autonomous element** (`<color-picker>`) rather than a customised built-in
  (`<div is="…">`): Safari supports only the former natively.

`window.poncho` gives you the translator, toasts, the spinner and the confirmation dialog — see
[Frontend](frontend/index).

## Form type extensions

To add an option to **every** field rather than build a new one, write a Symfony form type
extension, as the bundle does for its own options — see [Form extensions](component/form/extensions)
and Symfony's [How to Create a Form Type Extension](https://symfony.com/doc/current/form/create_form_type_extension.html).
