<?php

namespace Poncho\AdminBundle\Tests\Unit\Maker;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Poncho\AdminBundle\Maker\MakeNotification;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Bundle\MakerBundle\Util\ComposerAutoloaderFinder;
use Symfony\Bundle\MakerBundle\Util\MakerFileLinkFormatter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Regression tests for make:admin:notification's generated config: it must
 * write new, bundle-owned files rather than editing config/routes.yaml and
 * config/packages/poncho_admin.yaml in place — both are plain associative
 * trees (no "cannotBeOverwritten" list involved, unlike security.yaml's
 * access_control), so splitting them across files is safe. See
 * MakeAdminSecurityTest for the case where that is not true.
 */
class MakeNotificationTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/poncho_maker_test_' . uniqid();
        mkdir($this->projectDir . '/config/packages', 0777, true);
        mkdir($this->projectDir . '/config/routes', 0777, true);
        file_put_contents($this->projectDir . '/config/routes.yaml', "app_:\n    resource: '../src/Controller/'\n    type: attribute\n");
        file_put_contents($this->projectDir . '/config/packages/poncho_admin.yaml', "poncho_admin:\n    app_name: Existing App\n");
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->projectDir);
    }

    public function testItDoesNotTouchExistingFiles(): void
    {
        $this->generate();

        $this->assertSame(
            "app_:\n    resource: '../src/Controller/'\n    type: attribute\n",
            file_get_contents($this->projectDir . '/config/routes.yaml')
        );
        $this->assertSame(
            "poncho_admin:\n    app_name: Existing App\n",
            file_get_contents($this->projectDir . '/config/packages/poncho_admin.yaml')
        );
    }

    public function testItWritesNewFilesWithTheExpectedContent(): void
    {
        $this->generate('App\\Notification\\CustomProvider');

        $routes = Yaml::parseFile($this->projectDir . '/config/routes/poncho_admin_notification.yaml');
        $this->assertSame('@PonchoAdminBundle/config/routes/notification.php', $routes['poncho_admin_notification_']['resource']);

        $config = Yaml::parseFile($this->projectDir . '/config/packages/poncho_admin_notification.yaml');
        $this->assertSame('App\\Notification\\CustomProvider', $config['poncho_admin']['notification']['provider']);
        $this->assertSame(10, $config['poncho_admin']['notification']['poll_interval']);
    }

    private function generate(string $providerClass = 'App\\Notification\\AdminNotificationProvider'): void
    {
        $fileManager = new FileManager(
            new Filesystem(),
            new AutoloaderUtil(new ComposerAutoloaderFinder('App')),
            new MakerFileLinkFormatter(),
            $this->projectDir
        );
        $generator = new Generator($fileManager, 'App');
        $helper = new MakeHelper($this->createStub(ManagerRegistry::class), $this->projectDir);
        $maker = new MakeNotification($helper);

        $routeMethod = new \ReflectionMethod(MakeNotification::class, 'createRouteConfig');
        $routeMethod->setAccessible(true);
        $routeMethod->invoke($maker, $generator);

        $configMethod = new \ReflectionMethod(MakeNotification::class, 'createNotificationConfig');
        $configMethod->setAccessible(true);
        $configMethod->invoke($maker, $generator, $providerClass);

        $generator->writeChanges();
    }
}
