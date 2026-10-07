<?php

namespace Poncho\AdminBundle\Tests\Unit\Maker;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Poncho\AdminBundle\Maker\MakeHome;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Bundle\MakerBundle\Util\ComposerAutoloaderFinder;
use Symfony\Bundle\MakerBundle\Util\MakerFileLinkFormatter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Regression test for make:admin:home's generated config: it must write a
 * new, bundle-owned file rather than editing config/packages/poncho_admin.yaml
 * in place.
 */
class MakeHomeTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/poncho_maker_test_' . uniqid();
        mkdir($this->projectDir . '/config/packages', 0777, true);
        file_put_contents($this->projectDir . '/config/packages/poncho_admin.yaml', "poncho_admin:\n    user:\n        class: App\\Entity\\AdminUser\n");
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->projectDir);
    }

    public function testItDoesNotTouchTheExistingFile(): void
    {
        $this->generate();

        $this->assertSame(
            "poncho_admin:\n    user:\n        class: App\\Entity\\AdminUser\n",
            file_get_contents($this->projectDir . '/config/packages/poncho_admin.yaml')
        );
    }

    public function testItWritesANewFileWithTheExpectedContent(): void
    {
        $this->generate('App\\Menu\\CustomMenu');

        $config = Yaml::parseFile($this->projectDir . '/config/packages/poncho_admin_home.yaml');
        $this->assertSame('Admin', $config['poncho_admin']['app_name']);
        $this->assertSame('App\\Menu\\CustomMenu', $config['poncho_admin']['menu']);
    }

    private function generate(string $menuClass = 'App\\Menu\\AdminMenu'): void
    {
        $fileManager = new FileManager(
            new Filesystem(),
            new AutoloaderUtil(new ComposerAutoloaderFinder('App')),
            new MakerFileLinkFormatter(),
            $this->projectDir
        );
        $generator = new Generator($fileManager, 'App');
        $helper = new MakeHelper($this->createStub(ManagerRegistry::class), $this->projectDir);
        $maker = new MakeHome($helper);

        $method = new \ReflectionMethod(MakeHome::class, 'createMenuConfig');
        $method->setAccessible(true);
        $method->invoke($maker, $generator, $menuClass);

        $generator->writeChanges();
    }
}
