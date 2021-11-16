<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayListItemNewlineFixer;
use Symplify\CodingStandard\Fixer\ArrayNotation\ArrayOpenerAndCloserNewlineFixer;
use Symplify\CodingStandard\Sniffs\Debug\CommentedOutCodeSniff;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(BlankLineAfterOpeningTagFixer::class);
    $services->set(CommentedOutCodeSniff::class);
    $services->set(YodaStyleFixer::class)
        ->call('configure', [
            [
                'equal' => true,
                'identical' => true,
                'less_and_greater' => null,
            ],
        ]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::COMMON,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_73_MIGRATION,
        SetList::PSR_12,
        SetList::CLEAN_CODE,
        SetList::DEAD_CODE,
    ]);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/ecs.php',
    ]);

    $parameters->set(Option::SKIP, [
        ArrayIndentationFixer::class => [
            '*Test.php',
        ],
        ArrayListItemNewlineFixer::class => [
            '*Test.php',
        ],
        ArrayOpenerAndCloserNewlineFixer::class => [
            '*Test.php',
        ],
        ClassDefinitionFixer::class => [
            '*Test.php',
        ],
        VoidReturnFixer::class => [
            '*Test.php',
        ],
    ]);
};
