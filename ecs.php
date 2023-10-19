<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

    $ecsConfig->sets([
        // run and fix, one by one
        SetList::SPACES,
        SetList::ARRAY,
	    SetList::DOCBLOCK,
	    SetList::SYMPLIFY,
	    // SetList::COMMON, // adds declare(strict_types=1)
	    SetList::CLEAN_CODE,
        SetList::PSR_12,
    ]);
};
