<?php

namespace Poncho\AdminBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Poncho\AdminBundle\Lib\DataTable\Adapter\AdapterType;
use Poncho\AdminBundle\Lib\DataTable\Column\ColumnType;
use Poncho\AdminBundle\Lib\DataTable\DataTableConfiguration;
use Poncho\AdminBundle\Lib\DataTable\DataTableRegistry;
use Poncho\AdminBundle\Lib\DataTable\DataTableType;
use Poncho\AdminBundle\Lib\Form\Extension\FormTypeExtension;
use Poncho\AdminBundle\Lib\Menu\MenuRegistry;
use Poncho\AdminBundle\Lib\Menu\MenuType;
use Poncho\AdminBundle\Lib\Menu\Visitor\MenuVisitor;
use Poncho\AdminBundle\Notification\NotificationProviderInterface;
use Poncho\AdminBundle\Service\UserManagerInterface;
use Poncho\AdminBundle\PonchoAdminConfiguration;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class PonchoAdminExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('lib.php');
        $this->configureLib($container, $config, $loader);

        $loader->load('services.php');
        $this->configureUser($container, $config, $loader);
        $this->configureNotification($container, $config, $loader);

        $container->getDefinition(PonchoAdminConfiguration::class)
            ->setArgument(0, $config);
    }

    private function configureLib(ContainerBuilder $container, array $config, LoaderInterface $loader): void
    {
        $container
            ->register(DataTableConfiguration::class)
            ->setArgument(0, $config['datatable']);

        $container
            ->getDefinition(FormTypeExtension::class)
            ->setArgument(0, $config['form']['label_class'])
            ->setArgument(1, $config['form']['group_class']);

        $container->registerForAutoconfiguration(DataTableType::class)->addTag(DataTableRegistry::TAG_TYPE);
        $container->registerForAutoconfiguration(ColumnType::class)->addTag(DataTableRegistry::TAG_COLUMN_TYPE);
        $container->registerForAutoconfiguration(AdapterType::class)->addTag(DataTableRegistry::TAG_ADAPTER_TYPE);

        $container->registerForAutoconfiguration(MenuType::class)->addTag(MenuRegistry::TAG_TYPE);
        $container->registerForAutoconfiguration(MenuVisitor::class)->addTag(MenuRegistry::TAG_VISITOR);
    }

    private function configureUser(ContainerBuilder $container, array $config, LoaderInterface $loader): void
    {
        $loader->load('user.php');
        $container->setAlias(UserManagerInterface::class, $config['user']['manager']);
        $container->setParameter('poncho_admin.user.class', $config['user']['class']);

        if ($config['user']['profile']['enabled']) {
            $loader->load('userProfile.php');
        }
    }

    private function configureNotification(ContainerBuilder $container, array $config, LoaderInterface $loader): void
    {
        if ($config['notification']['enabled']) {
            $loader->load('notification.php');
            $provider = $config['notification']['provider'];

            if (empty($provider)) {
                throw new \InvalidArgumentException('You must specify a provider if "umbrela_admin.notification" was enabled.');
            }

            $container->setAlias(NotificationProviderInterface::class, $provider);
        }
    }
}
