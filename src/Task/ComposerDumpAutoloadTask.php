<?php

declare(strict_types=1);

namespace De\SWebhosting\OxidSurf\Task;

use TYPO3\Surf\Task\Composer\AbstractComposerTask;

class ComposerDumpAutoloadTask extends AbstractComposerTask
{
    /**
     * Command to run
     *
     * @var string
     */
    protected string $command = 'dump-autoload';

    /**
     * Arguments for the command
     *
     * @var array
     */
    protected array $arguments = [
        '--no-ansi',
        '--no-interaction',
        '--no-dev',
        '--classmap-authoritative'
    ];
}
