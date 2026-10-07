<?php

namespace Poncho\AdminBundle\Tests\Unit\Maker;

use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Poncho\AdminBundle\Maker\MakeAdminSecurity;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Bundle\MakerBundle\Util\ComposerAutoloaderFinder;
use Symfony\Bundle\MakerBundle\Util\MakerFileLinkFormatter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * Regression tests for make:admin:security's generated config.
 *
 * Most of it now lives in new, bundle-owned files instead of editing
 * config/routes.yaml and config/packages/security.yaml in place — this is
 * deliberate, see each production method's docblock for which Symfony config
 * nodes can and cannot be split across files this way (verified against a
 * real kernel boot, not assumed).
 *
 * "firewalls" and "access_control" are the exception: Symfony hard-rejects
 * splitting either across files, so that one edit still lands in the
 * application's own security.yaml — what changed there is that it is now a
 * real merge (existing firewalls and access_control rules both survive)
 * instead of replacing the whole array, which is what this test suite mostly
 * guards against.
 */
class MakeAdminSecurityTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . '/poncho_maker_test_' . uniqid();

        // Seed pre-existing files exactly as a real application would have them,
        // with content of their own that must survive.
        mkdir($this->projectDir . '/config/packages', 0777, true);
        mkdir($this->projectDir . '/config/routes', 0777, true);
        file_put_contents(
            $this->projectDir . '/config/packages/security.yaml',
            "security:\n    firewalls:\n        main: {}\n    access_control:\n        - { path: ^/, roles: PUBLIC_ACCESS }\n"
        );
        file_put_contents($this->projectDir . '/config/routes.yaml', "app_:\n    resource: '../src/Controller/'\n    type: attribute\n");
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->projectDir);
    }

    public function testItDoesNotTouchConfigRoutesYaml(): void
    {
        $this->generate();

        $this->assertSame(
            "app_:\n    resource: '../src/Controller/'\n    type: attribute\n",
            file_get_contents($this->projectDir . '/config/routes.yaml'),
            'The application\'s own config/routes.yaml must not be touched.'
        );
    }

    public function testItWritesNewFilesThatSymfonyAutoImports(): void
    {
        $this->generate();

        $this->assertFileExists($this->projectDir . '/config/routes/poncho_admin_security.yaml');
        $this->assertFileExists($this->projectDir . '/config/packages/poncho_admin_security.yaml');
    }

    public function testItPreservesTheExistingFirewallAndAccessControlRules(): void
    {
        $this->generate();

        $config = Yaml::parseFile($this->projectDir . '/config/packages/security.yaml');

        $this->assertArrayHasKey('main', $config['security']['firewalls'], 'The pre-existing "main" firewall must survive.');
        $this->assertArrayHasKey('admin', $config['security']['firewalls'], 'The new "admin" firewall must be added.');

        $paths = array_column($config['security']['access_control'], 'path');
        $this->assertContains('^/', $paths, 'The pre-existing access_control rule must survive.');
        $this->assertContains('^/admin', $paths, 'The new access_control rules must be added.');
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

    public function testGeneratedRoutesFileMatchesTheRealRouteResources(): void
    {
        $this->generate();

        $routes = Yaml::parseFile($this->projectDir . '/config/routes/poncho_admin_security.yaml');

        $this->assertSame('@PonchoAdminBundle/config/routes/profile.php', $routes['poncho_admin_profile_']['resource']);
        $this->assertSame('@PonchoAdminBundle/config/routes/user.php', $routes['poncho_admin_user_']['resource']);
        $this->assertSame('@PonchoAdminBundle/config/routes/security.php', $routes['poncho_admin_security_']['resource']);
    }

    public function testGeneratedConfigUsesTheGivenUserClass(): void
    {
        $this->generate('App\\Entity\\CustomAdmin');

        $ownConfig = Yaml::parseFile($this->projectDir . '/config/packages/poncho_admin_security.yaml');
        $this->assertSame('App\\Entity\\CustomAdmin', $ownConfig['poncho_admin']['user']['class']);
        $this->assertArrayHasKey('App\\Entity\\CustomAdmin', $ownConfig['security']['password_hashers']);
        $this->assertSame('App\\Entity\\CustomAdmin', $ownConfig['security']['providers']['admin_entity_provider']['entity']['class']);

        $securityYaml = Yaml::parseFile($this->projectDir . '/config/packages/security.yaml');
        $this->assertSame('admin_entity_provider', $securityYaml['security']['firewalls']['admin']['provider']);
    }

    /**
     * The other direction: a freshly-scaffolded application with no access_control
     * yet at all takes the plain YamlSourceManipulator path, not the text splice.
     */
    public function testWorksWhenAccessControlDoesNotExistYet(): void
    {
        file_put_contents($this->projectDir . '/config/packages/security.yaml', "security:\n    firewalls:\n        main: {}\n");

        $this->generate();

        $config = Yaml::parseFile($this->projectDir . '/config/packages/security.yaml');
        $paths = array_column($config['security']['access_control'], 'path');
        $this->assertContains('^/admin', $paths);
        $this->assertCount(4, $config['security']['access_control']);
    }

    private function generate(string $userClass = 'App\\Entity\\AdminUser'): void
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
        $io = new ConsoleStyle(new \Symfony\Component\Console\Input\ArrayInput([]), new \Symfony\Component\Console\Output\NullOutput());

        $routeMethod = new \ReflectionMethod(MakeAdminSecurity::class, 'createRouteConfig');
        $routeMethod->setAccessible(true);
        $routeMethod->invoke($maker, $generator);

        $securityMethod = new \ReflectionMethod(MakeAdminSecurity::class, 'createSecurityConfig');
        $securityMethod->setAccessible(true);
        $securityMethod->invoke($maker, $generator, $userClass);

        $fwMethod = new \ReflectionMethod(MakeAdminSecurity::class, 'updateFirewallAndAccessControl');
        $fwMethod->setAccessible(true);
        $fwMethod->invoke($maker, $io, $generator, $userClass);

        $generator->writeChanges();
    }

    private function renderAccessControl(): array
    {
        $this->generate();

        $config = Yaml::parseFile($this->projectDir . '/config/packages/security.yaml');

        return $config['security']['access_control'];
    }
}
