<?php

declare(strict_types=1);

namespace De\SWebhosting\OxidSurf\Tests\Unit\Task;

use De\SWebhosting\OxidSurf\Task\ComposerDumpAutoloadTask;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ComposerDumpAutoloadTask
 */
class ComposerDumpAutoloadTaskTest extends TestCase
{
    public function testTest(): void
    {
        new ComposerDumpAutoloadTask();
    }
}
