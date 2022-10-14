<?php

declare(strict_types=1);

namespace De\SWebhosting\OxidSurf\Tests\Unit\Task;

use De\SWebhosting\OxidSurf\Task\ComposerDumpAutoloadTask;
use PHPUnit\Framework\TestCase;
use TYPO3\Surf\Domain\Model\Application;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Service\ShellCommandService;

/**
 * @covers \De\SWebhosting\OxidSurf\Task\ComposerDumpAutoloadTask
 */
class ComposerDumpAutoloadTaskTest extends TestCase
{
    public function testCallsDumpAutoLoad(): void
    {
        $nodeMock = $this->createMock(Node::class);

        $deploymentMock = $this->createMock(Deployment::class);
        $deploymentMock->method('getWorkspaceWithProjectRootPath')->willReturn('workspace/project-root');

        $applicationMock = $this->createMock(Application::class);

        $task = new ComposerDumpAutoloadTask();

        $commandServiceMock = $this->createMock(ShellCommandService::class);
        $commandServiceMock->expects(self::exactly(2))
            ->method('executeOrSimulate')
            ->withConsecutive(
                [
                    'test -f ' . escapeshellarg('workspace/project-root/composer.json'),
                    $nodeMock,
                    $deploymentMock,
                    true,
                ],
                [
                    [
                        'cd ' . escapeshellarg('workspace/project-root'),
                        'composer dump-autoload --no-ansi --no-interaction --no-dev --classmap-authoritative 2>&1',
                    ],
                    $nodeMock,
                    $deploymentMock,
                    false,
                ]
            )
            ->willReturn(true);

        $task->setShellCommandService($commandServiceMock);

        $task->execute(
            $nodeMock,
            $applicationMock,
            $deploymentMock,
            [
                'useApplicationWorkspace' => true,
                'composerCommandPath' => 'composer',
            ]
        );
    }
}
