<?php

declare(strict_types=1);

namespace De\SWebhosting\OxidSurf\Application;

use De\SWebhosting\OxidSurf\Task\ComposerDumpAutoloadTask;
use TYPO3\Surf\Application\BaseApplication;
use TYPO3\Surf\Domain\Model\Deployment;
use TYPO3\Surf\Domain\Model\Workflow;
use TYPO3\Surf\Task\LocalShellTask;
use TYPO3\Surf\Task\ShellTask;

class OxidEshop extends BaseApplication
{
    public function __construct(string $name = 'Oxid eShop')
    {
        parent::__construct($name);

        $this->setOption('keepReleases', 3);
        $this->setOption('composerCommandPath', 'composer');
        $this->setOption('hardClean', true);

        $this->setOption('rsyncExcludes', self::getRsyncExcludes());

        $this->addSymlink('source/config.inc.override.php', '../../../shared/source/config.inc.override.php');

        $this->addSymlink('source/out/contents', '../../../../shared/out/contents');
        $this->addSymlink('source/out/downloads', '../../../../shared/out/downloads');
        $this->addSymlink('source/out/pictures', '../../../../shared/out/pictures');
    }

    /**
     * @return string[]
     */
    public static function getRsyncExcludes(): array
    {
        return [
            '/deployment',
            '/source/out/contents',
            '/source/out/downloads',
            '/source/out/pictures',
            '/source/Setup',
            '/config.inc.override.php',
            '/config.inc.php',
            '.git',
            '.idea',
            '.phpstorm.meta.php',
            '.editorconfig',
            '.env',
            '.env.dist',
            '.gitignore',
            '.gitlab-ci.yml',
            'node_modules',
            'COPYING',
            'README',
            'README',
            'LICENSE',
            '.gitmodules',
            'Gruntfile.js',
            'package.json',
            '.phpstorm.meta.php',
            'tsconfig.json',
            'tslint.json',
            'Vagrantfile',
            'webpack.mix.js',
            'yarn.lock',
        ];
    }

    public function registerTasks(Workflow $workflow, Deployment $deployment): void
    {
        parent::registerTasks($workflow, $deployment);

        $this->enableHardLinkReleaseIfAvailable($workflow);

        $this->registerFixPermissionsTask($workflow);
        $this->registerGruntBuildTask($workflow);

        $workflow->addTask(ComposerDumpAutoloadTask::class, 'update', $this);
    }

    /**
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    private function enableHardLinkReleaseIfAvailable(Workflow $workflow): void
    {
        if (class_exists('De\\SWebhosting\\TYPO3Surf\\HardlinkReleaseRegisterer')) {
            /** @noinspection RedundantSuppression */
            /** @noinspection PhpFullyQualifiedNameUsageInspection */
            /** @noinspection PhpUndefinedClassInspection */
            /** @noinspection PhpUndefinedNamespaceInspection */
            (new \De\SWebhosting\TYPO3Surf\HardlinkReleaseRegisterer())
                ->replaceSymlinkWithHardlinkRelease($workflow, $this);
        }
    }

    private function registerFixPermissionsTask(Workflow $workflow): void
    {
        $workflow->defineTask(
            'De\\SWebhosting\\Surf\\DefinedTask\\FixPermissionsTask',
            ShellTask::class,
            ['command' => 'chmod 400 {releasePath}/source/config.inc.php']
        );

        $workflow->addTask('De\\SWebhosting\\Surf\\DefinedTask\\FixPermissionsTask', 'update', $this);
    }

    private function registerGruntBuildTask(Workflow $workflow): void
    {
        if (!($this->hasOption('enableGruntWaveBuild') && $this->getOption('enableGruntWaveBuild'))) {
            return;
        }

        $workflow->defineTask(
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
            ]
        );

        $workflow->afterStage('package', 'De\\SWebhosting\\Surf\\DefinedTask\\GruntBuildTask', $this);
    }
}
