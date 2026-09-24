<?php

namespace Poncho\AdminBundle\Tests\Unit\Maker;

use PHPUnit\Framework\TestCase;
use Poncho\AdminBundle\Maker\Utils\MakeHelper;
use Symfony\Bundle\MakerBundle\FileManager;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Util\AutoloaderUtil;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;
use Symfony\Bundle\MakerBundle\Util\ComposerAutoloaderFinder;
use Symfony\Bundle\MakerBundle\Util\MakerFileLinkFormatter;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Renders the maker's Controller.tpl.php exactly as make:admin:table and
 * make:admin:tree would, and checks the result: valid PHP, and the delete
 * (and, for the tree, move) action verifies the CSRF token the table's
 * deleteLink()/moveLinks() now attach — see ColumnActionBuilderTest for the
 * token side of the same regression.
 */
class ControllerSkeletonCsrfTest extends TestCase
{
    private FileManager $fileManager;
    private MakeHelper $helper;

    protected function setUp(): void
    {
        $fileManager = new FileManager(
            new Filesystem(),
            new AutoloaderUtil(new ComposerAutoloaderFinder('App')),
            new MakerFileLinkFormatter(),
            sys_get_temp_dir()
        );
        $this->fileManager = $fileManager;
        $this->helper = new MakeHelper($this->createStub(\Doctrine\Persistence\ManagerRegistry::class), sys_get_temp_dir());
    }

    public function testFlatTableControllerChecksDeleteToken(): void
    {
        $php = $this->render(tree: false);
        $this->assertValidPhp($php);

        $this->assertMatchesRegularExpression(
            '/public function delete\(Request \$request, int \$id\): Response\s*\{\s*if \(!\$this->isCsrfTokenValid\(ColumnActionBuilder::csrfIntention\(\'app_mission_delete\', \[\'id\' => \$id\]\), \$request->query->getString\(\'_token\'\)\)\) \{/',
            $php,
            'delete() must check the CSRF token before doing anything else.'
        );
        $this->assertStringContainsString('use Poncho\AdminBundle\Lib\DataTable\ColumnActionBuilder;', $php);
    }

    public function testTreeTableControllerChecksDeleteAndMoveTokens(): void
    {
        $php = $this->render(tree: true);
        $this->assertValidPhp($php);

        $this->assertMatchesRegularExpression(
            '/public function move\([^)]*Request \$request, int \$id, string \$direction\): Response\s*\{\s*if \(!\$this->isCsrfTokenValid\(ColumnActionBuilder::csrfIntention\(\'app_mission_move\', \[\'id\' => \$id\]\), \$request->query->getString\(\'_token\'\)\)\) \{/',
            $php,
            'move() must check the CSRF token before doing anything else.'
        );
        $this->assertMatchesRegularExpression(
            '/public function delete\(Request \$request, int \$id\): Response\s*\{\s*if \(!\$this->isCsrfTokenValid\(ColumnActionBuilder::csrfIntention\(\'app_mission_delete\', \[\'id\' => \$id\]\), \$request->query->getString\(\'_token\'\)\)\) \{/',
            $php,
            'delete() must check the CSRF token before doing anything else.'
        );
    }

    private function render(bool $tree): string
    {
        $entity = new ClassNameDetails('App\\Entity\\Mission', 'App\\Entity');
        $repository = new ClassNameDetails('App\\Repository\\MissionRepository', 'App\\Repository', 'Repository');
        $form = new ClassNameDetails('App\\Form\\MissionType', 'App\\Form', 'Type');
        $table = new ClassNameDetails('App\\DataTable\\MissionTableType', 'App\\DataTable', 'TableType');
        $controllerClass = new ClassNameDetails('App\\Controller\\MissionController', 'App\\Controller', 'Controller');

        $vars = [
            'entity' => $entity,
            'repository' => $repository,
            'form' => $form,
            'table' => $table,
            'tree_table' => $tree,
            'controller' => $controllerClass,
            'route' => $this->helper->getRouteConfig($controllerClass),
            'index_template' => '@PonchoAdmin/datatable.html.twig',
            'edit_view_type' => MakeHelper::VIEW_MODAL,
            'edit_template' => 'mission/edit.html.twig',
            'class_name' => Str::getShortClassName($controllerClass->getFullName()),
            'namespace' => Str::getNamespace($controllerClass->getFullName()),
        ];

        return $this->fileManager->parseTemplate($this->helper->template('Controller.tpl.php'), $vars);
    }

    private function assertValidPhp(string $php): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'poncho_skeleton_') . '.php';
        file_put_contents($tmp, $php);

        $output = [];
        $exitCode = 0;
        exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $output, $exitCode);
        unlink($tmp);

        $this->assertSame(0, $exitCode, "Generated controller is not valid PHP:\n" . implode("\n", $output) . "\n\n" . $php);
    }
}
