<?php

namespace Poncho\AdminBundle\Tests\Unit\Maker;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Poncho\AdminBundle\Maker\MakeAdminSecurity;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Bundle\MakerBundle\Util\ComposerAutoloaderFinder;
use Symfony\Bundle\MakerBundle\Util\MakerFileLinkFormatter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Regression test for the "access_control locks out password reset" bug:
 * the generated patterns must actually match the routes the bundle ships,
 * or a logged-out visitor can never reach the reset flow.
 */
class MakeAdminSecurityTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/poncho_maker_test_' . uniqid();
        mkdir($this->projectDir . '/config/packages', 0777, true);
        file_put_contents($this->projectDir . '/config/packages/security.yaml', "security:\n    firewalls: {}\n");
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->projectDir);
    }

    public function testAccessControlPatternsMatchTheRealRoutes(): void
    {
        $accessControl = $this->renderAccessControl();

        // Every route the bundle's security.php actually exposes, with the /admin
        // prefix make:admin:security registers it under.
        $collection = new RouteCollection();
        $loader = new PhpFileLoader(new FileLocator(__DIR__ . '/../../../config/routes'));
        $imported = $loader->load('security.php');
        foreach ($imported as $name => $route) {
            $collection->add($name, $route);
        }

        $publicRoutes = ['poncho_admin_login', 'poncho_admin_security_passwordresetrequest', 'poncho_admin_security_passwordresetcheckemail', 'poncho_admin_security_passwordreset'];

        foreach ($publicRoutes as $name) {
            $route = $collection->get($name);
            $this->assertNotNull($route, "Route \"$name\" must exist in config/routes/security.php.");
            $path = '/admin' . $route->getPath();

            $matched = false;
            foreach ($accessControl as $rule) {
                if ('PUBLIC_ACCESS' !== $rule['roles']) {
                    continue;
                }
                if (1 === preg_match('#' . $rule['path'] . '#', $path)) {
                    $matched = true;
                    break;
                }
            }

            $this->assertTrue($matched, \sprintf('Route "%s" (%s) must be reachable by an unauthenticated user through one of the generated access_control rules.', $name, $path));
        }
    }

    /**
     * Regression test: the profile page changes the password/e-mail without
     * asking for the current one, so a stolen remember-me session must not be
     * enough to reach it — it needs the first, more specific match.
     */
    public function testProfileRequiresAFreshLogin(): void
    {
        $accessControl = $this->renderAccessControl();

        $matchedRole = null;
        foreach ($accessControl as $rule) {
            if (1 === preg_match('#' . $rule['path'] . '#', '/admin/profile')) {
                $matchedRole = $rule['roles'];
                break;
            }
        }

        $this->assertSame('IS_AUTHENTICATED_FULLY', $matchedRole, 'The first access_control rule matching "/admin/profile" must require a fresh login.');
    }

    private function renderAccessControl(): array
    {
        $fileManager = new FileManager(
            new Filesystem(),
            new AutoloaderUtil(new ComposerAutoloaderFinder('App')),
            new MakerFileLinkFormatter(),
            $this->projectDir
        );
        $generator = new Generator($fileManager, 'App');

        $helper = new MakeHelper($this->createStub(ManagerRegistry::class), $this->projectDir);
        $maker = new MakeAdminSecurity($helper);

        $io = new SymfonyStyle(new \Symfony\Component\Console\Input\ArrayInput([]), new \Symfony\Component\Console\Output\NullOutput());
        $method = new \ReflectionMethod(MakeAdminSecurity::class, 'updateSecurityConfig');
        $method->setAccessible(true);
        $method->invoke($maker, $io, $generator, 'App\\Entity\\AdminUser');
        $generator->writeChanges();

        $written = file_get_contents($this->projectDir . '/config/packages/security.yaml');
        $config = \Symfony\Component\Yaml\Yaml::parse($written);

        return $config['security']['access_control'];
    }
}
