<?php

namespace Poncho\AdminBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;

class MakeNotification extends AbstractMaker
{
    private const NAME = 'make:admin:notification';
    private const DESCRIPTION = 'Generate an admin notification provider';

    public function __construct(private readonly MakeHelper $helper)
    {
    }

    public static function getCommandName(): string
    {
        return self::NAME;
    }

    public static function getCommandDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entity = $generator->createClassNameDetails('AdminNotification', 'Entity\\');
        $repository = $generator->createClassNameDetails($entity->getShortName(), 'Repository\\', 'Repository');
        $provider = $generator->createClassNameDetails('AdminNotification', 'Notification\\', 'Provider');

        $vars = [
            'entity' => $entity,
            'repository' => $repository,
            'provider' => $provider
        ];

        $generator->generateClass(
            $entity->getFullName(),
            $this->helper->template('notification/NotificationEntity.tpl.php'),
            $vars
        );
        $generator->generateClass(
            $repository->getFullName(),
            $this->helper->template('EntityRepository.tpl.php'),
            $vars
        );
        $generator->generateClass(
            $provider->getFullName(),
            $this->helper->template('notification/NotificationProvider.tpl.php'),
            $vars
        );

        $this->updateRouteConfig($io, $generator);
        $this->updatePonchoAdminConfig($io, $generator, $provider->getFullName());

        $generator->writeChanges();
        $this->successMessage($io, $provider->getFullName());
    }

    private function updateRouteConfig(ConsoleStyle $io, Generator $generator): void
    {
        $configPath = 'config/routes.yaml';

        if (!$this->helper->fileExists($configPath)) {
            $io->warning('The file "config/routes.yaml" does not exist. PHP & XML configuration formats are currently not supported. You have to register routes manually :');
            $io->text([
                'poncho_admin_notification_:',
                '    resource: \'@PonchoAdminBundle/config/routes/notification.php\'',
                '    prefix: /admin',
                ''
            ]);

            return;
        }

        $manipulator = new YamlSourceManipulator($this->helper->getFileContents($configPath));
        $data = $manipulator->getData();

        $data['poncho_admin_notification_'] = [
            'resource' => '@PonchoAdminBundle/config/routes/notification.php',
            'prefix' => '/admin'
        ];

        $manipulator->setData($data);
        $generator->dumpFile($configPath, $manipulator->getContents());
    }

    private function updatePonchoAdminConfig(SymfonyStyle $io, Generator $generator, string $providerClass): void
    {
        $configPath = 'config/packages/poncho_admin.yaml';

        $configContent = $this->helper->fileExists($configPath)
            ? $this->helper->getFileContents($configPath)
            : 'poncho_admin:';

        $manipulator = new YamlSourceManipulator($configContent);
        $data = $manipulator->getData();
        $data['poncho_admin']['notification']['provider'] = $providerClass;
        $data['poncho_admin']['notification']['poll_interval'] = 10;

        $manipulator->setData($data);
        $generator->dumpFile($configPath, $manipulator->getContents());
    }

    private function successMessage(ConsoleStyle $io, string $providerClass): void
    {
        $this->writeSuccessMessage($io);

        $io->text([
            'Next:',
            '  1) Update your database schema with command <fg=yellow>"php bin/console doctrine:schema:update --force"</>.',
            \sprintf('  2) Customize how notification are fetched on class <fg=yellow>%s</>".', $providerClass),
            '  3) Create and send new notification.',
        ]);

        $io->newLine();
        $io->writeln('Read more about it on <href=https://beaumu.github.io/poncho-admin-bundle/#/component/notification>Documentation</>');
        $io->newLine();
    }
}
