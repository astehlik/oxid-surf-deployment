<?php

$finder = PhpCsFixer\Finder::create()
    ->in(
        [
            'src',
            'tests',
        ]
    );

$config = new PhpCsFixer\Config();
return $config->setRules(include __DIR__ . '/vendor/de-swebhosting/php-codestyle/PhpCsFixer/Psr12DefaultRules.php')
    ->setRiskyAllowed(true)
    ->setFinder($finder);
