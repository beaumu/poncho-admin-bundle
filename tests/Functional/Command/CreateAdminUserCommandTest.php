<?php

namespace Poncho\AdminBundle\Tests\Functional\Command;

use Poncho\AdminBundle\Service\UserManagerInterface;
use Poncho\AdminBundle\Tests\App\Entity\AdminUser;
use Poncho\AdminBundle\Tests\Functional\DbUtils;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateAdminUserCommandTest extends KernelTestCase
{
    public function testCreateUserHashesPasswordAndPersists(): void
    {
        self::bootKernel();
        DbUtils::initDb(self::$kernel);

        $application = new Application(self::$kernel);
        $command = $application->find('poncho_admin:create:user');
        $tester = new CommandTester($command);

        $tester->setInputs(['Ada', 'Lovelace', 'ada@example.test', 's3cret']);
        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);

        /** @var UserManagerInterface $manager */
        $manager = self::getContainer()->get(UserManagerInterface::class);

        /** @var AdminUser $user */
        $user = self::getContainer()->get('doctrine')->getRepository(AdminUser::class)->findOneBy(['email' => 'ada@example.test']);

        $this->assertNotNull($user, 'User must have been persisted.');
        $this->assertNotEmpty($user->password, 'Password must have been hashed and set.');
        $this->assertNull($user->plainPassword, 'Plain password must be erased after hashing.');

        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        $this->assertTrue($hasher->isPasswordValid($user, 's3cret'));
    }
}
