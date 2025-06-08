<?php
$finder = PhpCsFixer\Finder::create()->in(__DIR__ . '/src');
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
