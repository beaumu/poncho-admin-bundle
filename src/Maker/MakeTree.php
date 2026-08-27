<?php

namespace Poncho\AdminBundle\Maker;

use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;

class MakeTree extends AbstractMaker
{
    private const NAME = 'make:admin:tree';
    private const DESCRIPTION = 'Generate a CRUD with Tree DataTable view';

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
        $dependencies->addClassDependency(StofDoctrineExtensionsBundle::class, 'stof/doctrine-extensions-bundle');
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityClass = $this->helper->askEntityClass($io);
        $controllerClass = $this->helper->askControllerClass($io, $this->helper->getDefaultControllerClassFromEntityClass($entityClass));
        $editViewType = $this->helper->askEditViewTypeClass($io);

        // class details
        $entity = $generator->createClassNameDetails($entityClass, 'Entity\\');
        $repository = $generator->createClassNameDetails($entityClass, 'Repository\\', 'Repository');
        $form = $generator->createClassNameDetails($entityClass, 'Form\\', 'Type');
        $table = $generator->createClassNameDetails($entityClass, 'DataTable\\', 'TableType');
        $controller = $generator->createClassNameDetails($controllerClass, 'Controller\\', 'Controller');

        $routeConfig = $this->helper->getRouteConfig($controller);

        $vars = [
            'entity' => $entity,
            'repository' => $repository,
            'form' => $form,
            'table' => $table,
            'tree_table' => true,
            'controller' => $controller,
            'route' => $routeConfig,
            'index_template' => '@PonchoAdmin/datatable.html.twig',
            'edit_view_type' => $editViewType,
            'edit_template' => Str::asFilePath($controller->getRelativeNameWithoutSuffix()) . '/edit.html.twig'
        ];

        // add operation
        $generator->generateClass(
            $entity->getFullName(),
            $this->helper->template('NestedEntity.tpl.php'),
            $vars
        );
        $generator->generateClass(
            $repository->getFullName(),
            $this->helper->template('NestedRepository.tpl.php'),
            $vars
        );
        $generator->generateClass(
            $form->getFullName(),
            $this->helper->template('NestedFormType.tpl.php'),
            $vars
        );
        $generator->generateClass(
            $table->getFullName(),
            $this->helper->template('NestedTableType.tpl.php'),
            $vars
        );
        $generator->generateClass(
            $controller->getFullName(),
            $this->helper->template('Controller.tpl.php'),
            $vars
        );
        $templateName = MakeHelper::VIEW_MODAL === $editViewType ? 'template_edit_modal.tpl.php' : 'template_edit.tpl.php';
        $generator->generateTemplate(
            $vars['edit_template'],
            $this->helper->template($templateName),
            $vars
        );

        $generator->writeChanges();
        $this->successMessage($io, $routeConfig['base_path'], $routeConfig['name_prefix'] . '_index');
    }

    private function successMessage(ConsoleStyle $io, string $path, string $route): void
    {
        $this->writeSuccessMessage($io);

        $io->writeln('Next:');
        $io->writeln('  1) Activate tree extension for StofDoctrineExtensionsBundle (check <href=https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/configuration.html>Documentation</>).');
        $io->newLine();
        $io->writeln(<<<'CONFIG'
            # Example of config
            # packages/config/stof_doctrine_extensions.yaml
            stof_doctrine_extensions:
                orm:
                    default:
                        tree: true
            CONFIG);
        $io->newLine();

        $io->writeln('  2) Update your database schema with command <fg=yellow>"php bin/console doctrine:schema:update --force"</>.');
        $io->writeln(\sprintf('  3) Add section for route <fg=yellow>"%s"</> on your Admin menu.', $route));

        $io->newLine();
        $io->writeln(\sprintf('Open your browser, go to "%s" and enjoy!', $path));
        $io->newLine();
    }
}
