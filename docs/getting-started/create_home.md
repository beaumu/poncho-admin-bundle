# Create your first page

```bash
php bin/console make:admin:home
```

It asks for a controller class name and generates three things.

**A controller**

```php
// src/Controller/Admin/HomeController.php
namespace App\Controller\Admin;

use Poncho\AdminBundle\Lib\Controller\AdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class HomeController extends AdminController
{
    #[Route('')]
    public function index(): Response
    {
        return $this->render('admin/home/index.html.twig');
    }
}
```

`/admin` is only the default prefix. The route has no explicit name, so Symfony derives one:
`app_admin_home_index`. The firewall `make:admin:security` creates redirects there after login.

Extending [`AdminController`](controller) is optional; it adds helpers for tables, JS responses,
toasts and Doctrine.

**A template**

```twig
{# templates/admin/home/index.html.twig #}
{% extends '@PonchoAdmin/layout.html.twig' %}

{% block content %}
    <h1>Welcome</h1>
{% endblock %}
```

Every admin page extends `@PonchoAdmin/layout.html.twig`, which provides the sidebar, the top bar
and the breadcrumb. Its blocks are listed under [Twig](twig#the-layout).

**A menu**

```php
// src/Menu/AdminMenu.php
namespace App\Menu;

use Poncho\AdminBundle\Lib\Menu\Builder\MenuBuilder;
use Poncho\AdminBundle\Menu\BaseAdminMenu;

class AdminMenu extends BaseAdminMenu
{
    public function buildMenu(MenuBuilder $builder, array $options): void
    {
        $builder->root()
            ->add('home')
                ->icon('mdi mdi-home')
                ->route('app_admin_home_index');
    }
}
```

and registers it:

```yaml
# config/packages/poncho_admin.yaml
poncho_admin:
    menu: App\Menu\AdminMenu
```

See [Menu](component/menu/quick_start) to add sections.

Next: [Configure security](getting-started/configure_security).
