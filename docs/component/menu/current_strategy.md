# Current item

The **current** item is the one highlighted in the sidebar; it and its ancestors are also marked
**active**, which expands their sections, and the trail from the root to it forms the breadcrumb
and the page title.

## How it is found

If no item was forced with `current()`, the menu is walked depth-first and the **first** item whose
matching routes fit the request becomes current.

An item matches when:

1. one of its matching routes equals the request's `_route`, **and**
2. every parameter declared for that route is present in the request — as a route attribute or in
   the query string — with an equal value.

`route()` registers its route as a matching route, so a plain item matches its own page:

```php
public function buildMenu(MenuBuilder $builder, array $options): void
{
    $builder->root()
        ->add('missions')
            ->add('all')
                ->route('app_mission_index')
                ->end()
            ->add('failed')
                ->route('app_mission_index', ['status' => 'failed'])
                ->end();
}
```

| Request | Current item |
| --- | --- |
| `/mission` | `all` |
| `/mission?status=failed` | `all` — it comes first, and declares no parameter to rule it out |
| `/mission/42` (another route) | none |

Order matters: put the **more specific** item first when routes overlap:

```php
->add('failed')
    ->route('app_mission_index', ['status' => 'failed'])
    ->end()
->add('all')
    ->route('app_mission_index')
    ->end();
```

Now `?status=failed` selects `failed`, and a plain `/mission` selects `all` — `failed` does not match
it, because a declared parameter that is **absent** from the request never matches.

Values are compared loosely (`!=`), so `['id' => 1]` matches `?id=1`.

## Matching more routes

A mission's edit page should keep *Missions* highlighted. Add its route with `matchRoute()`:

```php
->add('missions')
    ->route('app_mission_index')
    ->matchRoute('app_mission_edit')
    ->matchRoute('app_mission_show');
```

Parameters work the same way: `matchRoute('app_mission_edit', ['type' => 'crewed'])`.

## Forcing it

`current()` sets the current item outright and skips matching altogether:

```php
use Symfony\Component\HttpFoundation\RequestStack;

class AdminMenu extends BaseAdminMenu
{
    public function __construct(Environment $twig, PonchoAdminConfiguration $configuration, private readonly RequestStack $requestStack)
    {
        parent::__construct($twig, $configuration);
    }

    public function buildMenu(MenuBuilder $builder, array $options): void
    {
        $isArchive = 'archive' === $this->requestStack->getMainRequest()?->query->get('view');

        $builder->root()
            ->add('missions')
                ->route('app_mission_index')
                ->current($isArchive);
    }
}
```

`current(false)` only unsets the current item if it was this one.

Prefer `matchRoute()` when a route is all you need: it keeps the rule inside the menu and needs no
injection.
