# Create a CRUD

```bash
php bin/console make:admin:table
```

It asks three questions:

1. **The entity** — a new or an existing class name. A new one is created with just an id.
2. **The controller** class name.
3. **The edit view** — `modal`, editing in a dialog over the table, or `page`, on its own page.

and generates the following. Existing entity, repository and form classes are reused as they are;
the table type and controller must not exist yet, or the maker stops:

| File | |
| --- | --- |
| `src/Entity/<Name>.php`, `src/Repository/<Name>Repository.php` | Only if they don't exist yet |
| `src/Form/<Name>Type.php` | The edit form — only if it doesn't exist yet |
| `src/DataTable/<Name>TableType.php` | A search filter, an *Add* button, id and action columns |
| `src/Controller/…` | `index`, `edit` and `delete` actions |
| `templates/…` | The index page, and the edit page or modal |

Then:

1. Add fields to the entity, the form and the table's columns.
2. Fill in the `TODO` in the table type's adapter, which decides what the search matches:
   ```php
   if (isset($formData['search'])) {
       DoctrineUtils::matchAll($qb, ['e.name', 'e.description'], $formData['search']);
   }
   ```
3. Update the schema and add the route to your menu.

## Trees

```bash
php bin/console make:admin:tree
```

asks the same questions and generates a nested-set CRUD: the entity carries `left`, `right`, `level`,
`root` and `parent`, the table is a [tree table](component/datatable/tree), and the controller gains
a `move` action for reordering.

## Before production

The generated `delete` action, and the tree maker's `move` action, accept any HTTP method but check
a CSRF token — the table's `deleteLink()`/`moveLinks()` attach one automatically. If you add your
own action to a generated table, or wire a route by hand, it gets no such protection for free — see
[Security](security#csrf-on-delete-move-and-bulk-action-routes).

The generated toasts (*Item updated*, *Item deleted*) are plain English strings; translate them as
you see fit.
