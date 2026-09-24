# Form types

All live in `Poncho\AdminBundle\Lib\Form` and need a [Poncho form theme](component/form/theme).

## DatepickerType

A text field with a [flatpickr](https://flatpickr.js.org/) calendar. The data is a `DateTime`.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `format` | `string` | `d/m/Y`, or `d/m/Y H:i` with `enable_time` | Used both to display the value and to parse it back |
| `enable_time` | `bool` | `false` | Adds a time picker |
| `min` | `DateTimeInterface\|string\|null` | `null` | Earliest selectable date. Strings go through `new DateTime()`: `'today'`, `'+1 week'` |
| `max` | `DateTimeInterface\|string\|null` | `null` | Latest selectable date |
| `allow_input` | `bool` | `true` | Allow typing the date as well as picking it |

```php
$builder->add('launchedAt', DatepickerType::class, [
    'enable_time' => true,
    'min' => 'today',
]);
```

Things to know:

- `format` is handed to both PHP (`DateTime::createFromFormat()`) and flatpickr (`dateFormat`), so
  stick to tokens the two share: `d`, `m`, `Y`, `y`, `H`, `i`, `s`, `j`, `n`.
- The value is a **mutable** `\DateTime`. A property typed `\DateTimeImmutable` needs a conversion.
- An unparseable string becomes `null` rather than a validation error. Add a `NotNull` constraint
  if the date is required.
- The calendar is localised from `<html lang>`, but only **French** is bundled besides English;
  other locales, Dutch included, fall back to English.

## AutoCompleteType

An entity select that loads its options from a URL as the user types, instead of rendering every
row of the table into the page. Built on [Tom Select](https://tom-select.js.org/).

| Option | Type | Default | |
| --- | --- | --- | --- |
| `class` | `string` | **required** | Entity class |
| `route` | `?string` | `null` | Route answering the searches |
| `route_params` | `array` | `[]` | |
| `url` | `?string` | `null` | Used instead of `route` |
| `multiple` | `bool` | `false` | |
| `min_characters` | `int` | `1` | Characters typed before searching |
| `option_template` | `?string` | `null` | [Mustache](https://mustache.github.io/) template of each option in the dropdown |
| `input_template` | `?string` | `null` | Mustache template of the selected item(s) |
| `tom_select_settings` | `array` | `[]` | Extra [Tom Select settings](https://tom-select.js.org/docs/) |

One of `route` or `url` is required. Any `EntityType` option — `choice_label`, `em`, … — is also
accepted.

```php
$builder->add('rocket', AutoCompleteType::class, [
    'class' => Rocket::class,
    'route' => 'rocket_search',
    'option_template' => '<strong>{{text}}</strong><br><small>{{manufacturer}}</small>',
]);
```

### The search endpoint

It receives `?query=<typed text>` and returns:

```json
{
    "results": [
        {"value": 12, "text": "Saturn V", "manufacturer": "Boeing"}
    ],
    "next_url": "/rocket/search?query=sat&page=2"
}
```

- `value` is the entity id and `text` its label. Any extra keys are available to the Mustache
  templates.
- `next_url`, when not `null`, is fetched as the user scrolls — pagination is up to you.

```php
#[Route('/rocket/search', name: 'rocket_search')]
public function search(Request $request, RocketRepository $repository): JsonResponse
{
    $query = $request->query->getString('query');
    $page = max(1, $request->query->getInt('page', 1));
    $rockets = $repository->search($query, $page, 20);

    return new JsonResponse([
        'results' => array_map(fn (Rocket $r) => [
            'value' => $r->id,
            'text' => $r->name,
            'manufacturer' => $r->manufacturer,
        ], $rockets),
        'next_url' => $rockets
            ? $this->generateUrl('rocket_search', ['query' => $query, 'page' => $page + 1])
            : null,
    ]);
}
```

On submit, only the selected ids are loaded from the database.

?> An already-selected value is rendered server-side through `EntityType`, with its `choice_label`
(by default `__toString()`). Extra template fields such as `manufacturer` are not available for it,
so write `input_template` to degrade gracefully.

## Autocomplete on any ChoiceType or EntityType

To get the Tom Select widget on a regular, fully rendered choice field, set `ub_autocomplete`:

```php
$builder->add('status', EnumType::class, [
    'class' => MissionStatus::class,
    'ub_autocomplete' => true,
]);
```

It accepts `option_template`, `input_template` and `tom_select_settings` too. See
[Form extensions](component/form/extensions#autocomplete--choicetype-and-entitytype).

## NestedEntityType

An `EntityType` for nested-set entities (see [Tree tables](component/datatable/tree)). Options are
indented by depth and it uses the autocomplete widget by default.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `left_path` | `string` | `left` | |
| `level_path` | `string` | `level` | |
| `min_level` | `int` | `0` | Hide nodes above this depth |
| `disable_node` | `?object` | `null` | Disable this node **and all its descendants** |
| `option_template` | `string` | an indented `<div>` | |

`class` is required, as for any `EntityType`. The default `query_builder` loads nodes with
`level >= min_level`, ordered by left value.

```php
$builder->add('parent', NestedEntityType::class, [
    'class' => Category::class,
    'disable_node' => $category,   // a category cannot become its own parent or descendant
]);
```

## PonchoCollectionType

A `CollectionType` rendered as a table, with add and delete buttons and optional drag-and-drop
ordering.

| Option | Type | Default | |
| --- | --- | --- | --- |
| `sort_by` | `?string` | `null` | Property path receiving each item's position (`1`, `2`, …) on submit. Enables drag-and-drop |
| `max_length` | `?int` | `null` | Hide the add button at this many items |
| `headless` | `bool` | `false` | Hide the header row |
| `add_btn_template` | `?string` | `null` | HTML of the add button |

It also changes the `CollectionType` defaults: `allow_add` and `allow_delete` are `true`,
`by_reference` is `false`, `error_bubbling` is `false`, and `constraints` is `new Valid()`.

```php
$builder->add('crew', PonchoCollectionType::class, [
    'entry_type' => CrewMemberType::class,
    'sort_by' => 'position',
    'max_length' => 6,
]);
```

The header labels come from the entry type's fields.

## PasswordTogglableType

A `PasswordType` with an eye icon that toggles the value between hidden and visible.

## SearchType

A `TextType` for the datatable toolbar: magnifier icon, normalised value. See
[Filters](component/datatable/filters#searchtype).
