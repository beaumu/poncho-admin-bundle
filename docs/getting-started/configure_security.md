# Configure security

Run maker
```bash
php bin/console make:admin:security
```

The following files will be updated :
 - `config/packages/security.yaml`
 - `config/packages/poncho_admin.yaml`
 - `config/routes.yaml`
 
And the following files we be created :
- `src\Entity\AdminUser.php`

It may be necessary to adjust the order of the firewalls in the `config/packages/security.yaml` file, the newly created `admin` firewall must be 
before `main` firewall

Add entry on menu to manage admin user :
```php
// src/Menu/AdminMenu.php
public function buildMenu(MenuBuilder $builder, array $options)
{
    $builder->root()
        ->add('Users')
            ->icon('uil-user')
            ->route('poncho_admin_user_index');

}
```

Regenerate Symfony cache `php bin/console cache:clear` \
Update doctrine schema `php bin/console doctrine:schema:update --force` \
Et voila, now you have to be logged to access administration backends, moreover you can manage users.

Run following command to create a new admin user:
```bash
php bin/console poncho_admin:create:user
```

