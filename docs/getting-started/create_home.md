# Create your first admin page

Run maker
```bash
php bin/console make:admin:home
```
Files generated are :

#### Controller

```php
// src/Controller/Admin/HomeController.php
<?php

namespace App\Controller\Admin;

use Symfony\Component\Routing\Attribute\Route;
use Poncho\AdminBundle\Lib\Controller\AdminController;

#[Route('/admin')]
class HomeController extends AdminController
{

    #[Route('')]
    public function index()
    {
        return $this->render('admin/home/index.html.twig');
    }

}
```
The `/admin` URL is only a default value, so you can change it.
There's no need to define an explicit name for this route. Symfony autogenerates a route name (`app_admin_home_index` for this action) but you can define an explicit route name to simplify your code.

The super class `AdminController` provides some helper to use Poncho Components (`DataTable`, `JsResponse` ...), this is not mandatory to extend it.


#### Twig template
```twig
{# templates/admin/home/index.html.twig #}
{% extends "@PonchoAdmin/layout.html.twig" %}
```
All your admin view must extend `@PonchoAdmin/layout.html.twig`.

#### Admin Menu

```php
// src/Menu/AdminMenu.php
<?php

namespace App\Menu;

use Poncho\AdminBundle\Menu\BaseAdminMenu;
use Poncho\AdminBundle\Lib\Menu\Builder\MenuBuilder;

class AdminMenu extends BaseAdminMenu
{

    public function buildMenu(MenuBuilder $builder, array $options)
    {
        $builder->root()
            // Create a new entry
            ->add('Home')
                // css class of icon
                ->icon('uil-home')
                // Route of menu entry
                ->route('app_admin_home_index');
    }

}
```

```yaml
# app/config/packages/poncho_admin.yaml
poncho_admin:
  menu: App\Menu\AdminMenu
```
