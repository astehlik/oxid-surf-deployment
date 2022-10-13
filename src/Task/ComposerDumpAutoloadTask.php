<?php

declare(strict_types=1);

namespace De\SWebhosting\OxidSurf\Task;

use TYPO3\Surf\Task\Composer\AbstractComposerTask;

class ComposerDumpAutoloadTask extends AbstractComposerTask
{
    /**
     * @var string[]
     */
    protected array $arguments = [
        '--no-ansi',
        '--no-interaction',
        '--no-dev',
        '--classmap-authoritative',
    ];

    protected string $command = 'dump-autoload';
}
