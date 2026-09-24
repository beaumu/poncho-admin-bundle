# Extending Poncho

Everything the bundle renders can be replaced or added to from your application, without forking
it. This page lists every extension point; the pages after it show how to build each one.

## Extension points

| You want to… | Build | Guide |
| --- | --- | --- |
| Reuse a table setup across screens | A `DataTableType` subclass, with options | [DataTable](extending/datatable#table-types) |
| Show a value your way in every table | A column type | [DataTable](extending/datatable#column-types) |
| Add a reusable toolbar or row button | An action type | [DataTable](extending/datatable#action-types) |
| Read rows from an API, a file, a search engine | An adapter type | [DataTable](extending/datatable#adapter-types) |
| Change how a table behaves in the browser | A DataTable plugin | [DataTable JavaScript](component/datatable/javascript#plugins) |
| Add a menu other than the sidebar | A `MenuType` | [Other menus](component/menu/custom_menu) |
| Change menu items after they are built — badges, visibility | A menu visitor | [Menu](extending/menu) |
| Make the server trigger new behaviour in the browser | A JsResponse action | [JsResponse client side](component/jsresponse/client#custom-actions) |
| Add a form field with its own widget | A form type, a theme block and a custom element | [Form widgets](extending/form) |
| Feed the notification bell | A notification provider | [Notifications](component/notification) |
| Change how users are stored, created or reset | A user manager | [Users](extending/user) |
| Change any page's markup | A template override | [Templates](twig#overriding-templates) |
| Change colours, fonts, spacing | A compiled theme | [Theming](frontend/theming) |
| Change or add wording | Translations | [Translations](translations) |

## The rules every type follows

DataTable types, column types, action types, adapter types, menu types and menu visitors all work
the same way — the way Symfony form types do. Knowing these five rules saves most debugging:

**1. A type is a service, found by its class name.** Extend the base class and, with the default
`autoconfigure: true`, the bundle tags your class for you. You then refer to it everywhere by its
fully qualified class name:

```php
$builder->add('price', MoneyColumnType::class);
```

The name is really the **service id**. The default `App\` resource loading registers every class
under its own class name, which is why this works. If you register a type by hand under another id,
or turn autoconfiguration off, tag it yourself:

| Base class | Tag |
| --- | --- |
| `Lib\DataTable\DataTableType` | `poncho.datatable.type` |
| `Lib\DataTable\Column\ColumnType` | `poncho.datatable.columntype` |
| `Lib\DataTable\Action\ActionType` | `poncho.datatable.actiontype` |
| `Lib\DataTable\Adapter\AdapterType` | `poncho.datatable.adaptertype` |
| `Lib\Menu\MenuType` | `poncho.menu.type` |
| `Lib\Menu\Visitor\MenuVisitor` | `poncho.menu.visitor` |

The error `ColumnType "…" doesn't exist, maybe you have forget to register it ?` means the class is
not a tagged service under that id.

**2. A type is shared.** One instance serves every table in the request. Never store per-table data
in a property; everything specific to one use arrives through `$options`.

**3. Options are declared with the OptionsResolver.** Declare every option your type accepts in
`configureOptions()`. Unknown options throw, which catches typos.

**4. Reuse is plain PHP inheritance.** There is no `getParent()`. Extend the type you want to build
on and call `parent::configureOptions()` first — otherwise the parent's options disappear:

```php
class MoneyColumnType extends PropertyColumnType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);   // keeps property_path, order, …
        // …
    }
}
```

The options every type has — `name`, `label`, `order`, `render`… for columns; `name` and
`send_state` for actions — are added by the base class's `final` static `defaultConfigureOptions()`
before yours, so they cannot be removed, only given new defaults.

**5. Services go in the constructor.** Types are autowired: inject the router, the translator, an
HTTP client — whatever `render()` or `getResult()` needs.

## Before you extend

Check that an option does not already do it. Most one-off needs are covered by:

- a column's `render` / `render_html` callable ([Columns](component/datatable/columns)),
- the adapter's `query` callable ([Adapters](component/datatable/adapters)),
- `buildRowView()` on the table type, for row classes and attributes
  ([Options](component/datatable/options#customising-rows-buildrowview)),
- `ColumnActionBuilder::link()` with your own `route` ([Actions](component/datatable/actions)).

Build a type when the same code appears in a second table.
