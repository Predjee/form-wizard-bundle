<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->sets([
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::COMMON,
        SetList::STRICT,
    ]);

    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, [
        'imports_order' => ['class', 'function', 'const'],
        'sort_algorithm' => 'alpha',
    ]);

    $ecsConfig->skip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
        MethodChainingIndentationFixer::class => [
            __DIR__ . '/src/YiggleFormWizardBundle.php',
        ],
    ]);
};
