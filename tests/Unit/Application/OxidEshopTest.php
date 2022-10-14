<?php

declare(strict_types=1);

namespace De\SWebhosting\OxidSurf\Tests\Unit\Application;

use De\SWebhosting\OxidSurf\Application\OxidEshop;
use De\SWebhosting\OxidSurf\Task\ComposerDumpAutoloadTask;
use De\SWebhosting\TYPO3Surf\HardlinkReleaseTask;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\Generic\CreateSymlinksTask;
use TYPO3\Surf\Task\LocalShellTask;
use TYPO3\Surf\Task\ShellTask;
use TYPO3\Surf\Task\SymlinkReleaseTask;

/**
 * @covers \OxidEshop
 */
class OxidEshopTest extends TestCase
{
    private OxidEshop $oxidApplication;

    protected function setUp(): void
    {
        $this->oxidApplication = new OxidEshop();
    }

    /**
     * @return MockObject&Workflow
     */
    public function createWorkflowMock(): Workflow
    {
        $workflowMock = $this->createMock(Workflow::class);
        $workflowMock->method('addTask')->willReturn($workflowMock);
        $workflowMock->method('afterTask')->willReturn($workflowMock);
        return $workflowMock;
    }

    /**
     * @return array<int, array<int, bool|int|string>>
     */
    public function optionsDataProvider(): array
    {
        return [
            [
                'keepReleases',
                3,
            ],
            [
                'composerCommandPath',
                'composer',
            ],
            [
                'hardClean',
                true,
            ],
        ];
    }

    public function testNameIsSet(): void
    {
        self::assertSame('Oxid eShop', $this->oxidApplication->getName());
    }

    /**
     * @dataProvider optionsDataProvider
     *
     * @param int|string $expectedValue
     */
    public function testOptionsAreSet(string $optionName, $expectedValue): void
    {
        self::assertTrue($this->oxidApplication->hasOption($optionName), 'Option ' . $optionName . ' was not set.');
        self::assertSame($expectedValue, $this->oxidApplication->getOption($optionName));
    }

    public function testRegisterTasksCallsParent(): void
    {
        // The symlinks option is set in the parent application.
        self::assertFalse($this->oxidApplication->hasOption(CreateSymlinksTask::class . '[symlinks]'));
        $this->oxidApplication->registerTasks($this->createWorkflowMock(), $this->createMock(Deployment::class));
        self::assertTrue($this->oxidApplication->hasOption(CreateSymlinksTask::class . '[symlinks]'));
    }

    public function testRegisterTasksEnablesHardlinkRelease(): void
    {
        $workflowMock = $this->createWorkflowMock();

        $workflowMock->expects(self::once())
            ->method('removeTask')
            ->with(SymlinkReleaseTask::class);

        $this->expectAddTaskIsCalled(
            $workflowMock,
            8,
            HardlinkReleaseTask::class,
            'switch',
            $this->oxidApplication
        );

        $this->oxidApplication->registerTasks($workflowMock, $this->createMock(Deployment::class));
    }

    public function testRegisterTasksGruntBuildNotRegisteredIfDisabled(): void
    {
        $workflowMock = $this->createWorkflowMock();
        $workflowMock->expects(self::exactly(2))->method('defineTask');
        $this->oxidApplication->registerTasks($workflowMock, $this->createMock(Deployment::class));
    }

    public function testRegisterTasksGruntBuildRegisteredIfEnabled(): void
    {
        $this->oxidApplication->setOption('enableGruntWaveBuild', true);

        $workflowMock = $this->createWorkflowMock();

        $this->expectDefineTaskIsCalled(
            $workflowMock,
            3,
            'De\\SWebhosting\\Surf\\DefinedTask\\GruntBuildTask',
            LocalShellTask::class,
            [
                'command' => [
                    'yarn global add grunt-cli',
                    'cd {workspacePath}/vendor/oxid-esales/wave-theme',
                    'yarn install',
                    'cd {workspacePath}',
                    'yarn install',
                    '~/.yarn/bin/grunt build',
                ],
            ],
        );

        $this->oxidApplication->registerTasks($workflowMock, $this->createMock(Deployment::class));
    }

    public function testRegisterTasksRegistersAddsComposerDumpAutoloadTask(): void
    {
        $workflowMock = $this->createWorkflowMock();

        $this->expectAddTaskIsCalled(
            $workflowMock,
            10,
            ComposerDumpAutoloadTask::class,
            'update',
            $this->oxidApplication
        );

        $this->oxidApplication->registerTasks($workflowMock, $this->createMock(Deployment::class));
    }

    public function testRegisterTasksRegistersFixPermissionsTask(): void
    {
        $workflowMock = $this->createWorkflowMock();

        $this->expectDefineTaskIsCalled(
            $workflowMock,
            2,
            'De\\SWebhosting\\Surf\\DefinedTask\\FixPermissionsTask',
            ShellTask::class,
            ['command' => 'chmod 400 {releasePath}/source/config.inc.php']
        );

        $this->expectAddTaskIsCalled(
            $workflowMock,
            9,
            'De\\SWebhosting\\Surf\\DefinedTask\\FixPermissionsTask',
            'update',
            $this->oxidApplication
        );

        $this->oxidApplication->registerTasks($workflowMock, $this->createMock(Deployment::class));
    }

    public function testRsyncExcludesAreSet(): void
    {
        $expectedExcludes = OxidEshop::getRsyncExcludes();
        self::assertSame($expectedExcludes, $this->oxidApplication->getOption('rsyncExcludes'));
    }

    public function testSymlinksAreConfigured(): void
    {
        $expectedSymlinks = [
            'source/config.inc.override.php' => '../../../shared/source/config.inc.override.php',
            'source/out/contents' => '../../../../shared/out/contents',
            'source/out/downloads' => '../../../../shared/out/downloads',
            'source/out/pictures' => '../../../../shared/out/pictures',
        ];
        self::assertSame($expectedSymlinks, $this->oxidApplication->getSymlinks());
    }

    /**
     * @param MockObject&Workflow $workflowMock
     */
    private function expectAddTaskIsCalled(
        Workflow $workflowMock,
        int $at,
        string $task,
        string $stage,
        Application $application = null
    ): void {
        $consecutive = [];

        for ($i = 1; $i < $at; $i++) {
            $consecutive[] = [
                self::anything(),
                self::anything(),
                self::anything(),
            ];
        }

        $consecutive[] = [
            $task,
            $stage,
            $application,
        ];

        $workflowMock->expects(self::atLeast($at))
            ->method('addTask')
            ->withConsecutive(...$consecutive);
    }

    /**
     * @param MockObject&Workflow $workflowMock
     * @param array<string, array<int, string>|string> $options
     */
    private function expectDefineTaskIsCalled(
        Workflow $workflowMock,
        int $at,
        string $taskName,
        string $baseTask,
        array $options
    ): void {
        $consecutive = [];

        for ($i = 1; $i < $at; $i++) {
            $consecutive[] = [
                self::anything(),
                self::anything(),
                self::anything(),
            ];
        }

        $consecutive[] = [
            $taskName,
            $baseTask,
            $options,
        ];

        $workflowMock->expects(self::exactly($at))
            ->method('defineTask')
            ->withConsecutive(...$consecutive);
    }
}
