<?php

namespace Poncho\AdminBundle\Tests\Unit\Maker;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Documents, against a real kernel boot rather than reading Symfony's source,
 * exactly why MakeAdminSecurity::updateFirewallAndAccessControl() has to edit
 * the application's own config/packages/security.yaml instead of writing a new
 * file the way the rest of that maker's config does:
 *
 * - a *new* firewall key defined in a second file, alongside one that already
 *   has at least one firewall, is a hard container-compile error;
 * - "access_control" set in more than one file at all is a hard error too,
 *   regardless of whether both files use flow or block list style.
 *
 * If a future Symfony version relaxes either restriction, this test starts
 * failing — at which point MakeAdminSecurity's split can be reconsidered.
 */
class SecurityConfigCannotSplitAcrossFilesTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/poncho_security_split_test_' . uniqid();
        mkdir($this->dir . '/config/packages', 0777, true);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->dir);
    }

    public function testANewFirewallInASecondFileIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('not allowed to define new elements for path "security.firewalls"');

        $this->bootWith(
            "security:\n    firewalls:\n        main:\n            security: false\n",
            "security:\n    firewalls:\n        admin:\n            security: false\n"
        );
    }

    public function testAccessControlSetInTwoFilesIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('"security.access_control" cannot be overwritten');

        $this->bootWith(
            "security:\n    firewalls:\n        main:\n            security: false\n    access_control:\n        - { path: ^/, roles: PUBLIC_ACCESS }\n",
            "security:\n    access_control:\n        - { path: ^/admin, roles: ROLE_ADMIN }\n"
        );
    }

    public function testANewProviderInASecondFileIsFine(): void
    {
        $this->bootWith(
            "security:\n    firewalls:\n        main:\n            security: false\n    providers:\n        a:\n            memory: ~\n",
            "security:\n    providers:\n        b:\n            memory: ~\n"
        );

        $this->addToAssertionCount(1); // reached without throwing
    }

    private function bootWith(string $fileA, string $fileB): void
    {
        file_put_contents($this->dir . '/config/packages/a.yaml', $fileA);
        file_put_contents($this->dir . '/config/packages/z.yaml', $fileB);

        $kernel = new class('test', false, $this->dir) extends Kernel {
            use MicroKernelTrait;

            public function __construct(string $env, bool $debug, private readonly string $dir)
            {
                parent::__construct($env, $debug);
            }

            public function registerBundles(): iterable
            {
                return [new FrameworkBundle(), new SecurityBundle()];
            }

            public function getProjectDir(): string
            {
                return $this->dir;
            }

            public function getCacheDir(): string
            {
                return $this->dir . '/var/cache';
            }

            public function getLogDir(): string
            {
                return $this->dir . '/var/log';
            }
        };

        $kernel->boot();
    }
}
