# Filters

The toolbar's filters are a regular Symfony form. Whatever it submits reaches your adapter as
`$formData`.

```php
$builder->addFilter(string|FormBuilderInterface $child, ?string $type = null, array $options = []);
$builder->removeFilter(string $name);
$builder->hasFilter(string $name): bool;
```

`addFilter()` takes the same arguments as `FormBuilderInterface::add()`, so any form type works:

```php
use App\Enum\MissionStatus;
use Poncho\AdminBundle\Lib\Form\DatepickerType;
use Poncho\AdminBundle\Lib\Form\SearchType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

$builder->addFilter('search', SearchType::class);

$builder->addFilter('status', EnumType::class, [
    'class' => MissionStatus::class,
    'placeholder' => 'All statuses',
]);

$builder->addFilter('launchedAfter', DatepickerType::class, [
    'attr' => ['placeholder' => 'Launched after'],
]);
```

and then in the adapter:

```php
'query' => function (QueryBuilder $qb, array $formData) {
    if (isset($formData['status'])) {
        $qb->andWhere('e.status = :status')->setParameter('status', $formData['status']);
    }
    if (isset($formData['launchedAfter'])) {
        $qb->andWhere('e.launchedAt >= :after')->setParameter('after', $formData['launchedAfter']);
    }
},
```

Values arrive **transformed** — an `EnumType` gives you the enum case, a `DatepickerType` a
`DateTime`, an `EntityType` the entity.

## How the toolbar behaves

- **Filters submit themselves.** Changing a select, checkbox or radio reloads the table after
  100 ms; typing or pasting in a text or search field reloads it 200 ms after the last keystroke.
  There is no submit button. For a manual-submit toolbar, override the template through the
  `toolbar_template` option and drop `data-submit="auto"` from the `<form>`.
- **Filters have no labels.** Each is rendered with `form_widget()` only, so give them a
  `placeholder`.
- **Validation is off, and so is CSRF.** The filter form is built with `validation_groups: false`
  and `csrf_protection: false`, fields are not required, and it is submitted on every reload. Treat
  its values as unvalidated input.
- **It must be an array form.** No `data_class`. See `toolbar_form_*` in
  [Table options](component/datatable/options#toolbar).

## SearchType

`Poncho\AdminBundle\Lib\Form\SearchType` is a `TextType` meant for full-text search:

- a magnifier icon inside the field,
- the placeholder `label.search...`,
- the submitted value **normalized**: lowercased, trimmed, inner whitespace collapsed, and an empty
  string turned into `null`.

The `null` is what makes `isset($formData['search'])` a reliable "is the user searching" check.

## DoctrineUtils::matchAll()

```php
DoctrineUtils::matchAll(QueryBuilder $qb, array $fields, string $search, string $parameterPrefix = '_match');
```

Splits `$search` into terms and requires **every** term to match **at least one** field:

```php
DoctrineUtils::matchAll($qb, ['e.name', 'r.name'], 'apollo saturn');
// WHERE (LOWER(CONCAT(e.name, '')) LIKE '%apollo%' OR LOWER(CONCAT(r.name, '')) LIKE '%apollo%')
//   AND (LOWER(CONCAT(e.name, '')) LIKE '%saturn%' OR LOWER(CONCAT(r.name, '')) LIKE '%saturn%')
```

Double quotes keep a phrase together: `"saturn v" apollo` is two terms.

Things to know:

- Fields are lowercased, but **the search string is not**. Pair it with `SearchType`, which
  lowercases for you, or lowercase the value yourself.
- `%` and `_` in the search are not escaped, so they act as `LIKE` wildcards.
- `CONCAT(field, '')` lets non-string columns such as integers be searched too.
- Call it twice on the same query builder with different `$parameterPrefix` values, or the
  parameters collide.

## SearchTrait

`Poncho\AdminBundle\Entity\Trait\SearchTrait` adds a nullable `TEXT` column named `search` to an
entity:

```php
use Poncho\AdminBundle\Entity\Trait\SearchTrait;

#[ORM\Entity]
class Mission
{
    use SearchTrait;
}
```

The bundle does not fill it. The intended use is a denormalized, lowercased copy of the searchable
fields, maintained by your own code — for instance a Doctrine `prePersist`/`preUpdate` listener —
so a search needs only one `LIKE`:

```php
DoctrineUtils::matchAll($qb, ['e.search'], $formData['search']);
```
