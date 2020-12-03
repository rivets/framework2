<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Preset
    |--------------------------------------------------------------------------
    |
    | This option controls the default preset that will be used by PHP Insights
    | to make your code reliable, simple, and clean. However, you can always
    | adjust the `Metrics` and `Insights` below in this configuration file.
    |
    | Supported: "default", "laravel", "symfony", "magento2", "drupal"
    |
    */

    'preset' => 'default',

    /*
    |--------------------------------------------------------------------------
    | IDE
    |--------------------------------------------------------------------------
    |
    | This options allow to add hyperlinks in your terminal to quickly open
    | files in your favorite IDE while browsing your PhpInsights report.
    |
    | Supported: "textmate", "macvim", "emacs", "sublime", "phpstorm",
    | "atom", "vscode".
    |
    | If you have another IDE that is not in this list but which provide an
    | url-handler, you could fill this config with a pattern like this:
    |
    | myide://open?url=file://%f&line=%l
    |
    */

    'ide' => null,

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may adjust all the various `Insights` that will be used by PHP
    | Insights. You can either add, remove or configure `Insights`. Keep in
    | mind, that all added `Insights` must belong to a specific `Metric`.
    |
    */

    'exclude' => [
        'class/framework/utility/jwt',
        'devel',
        'install',
        //'install.php',
        'testing',
        'composer.json',
        //  'path/to/directory-or-file'
    ],

    'add' => [

         //  ExampleMetric::class => [
        //      ExampleInsight::class,
        //  ]
    ],

    'remove' => [
        NunoMaduro\PhpInsights\Domain\Insights\Composer\ComposerLockMustBeFresh::class,
        NunoMaduro\PhpInsights\Domain\Insights\Composer\ComposerMustBeValid::class,
        // NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenDefineGlobalConstants::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenGlobals::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses::class,
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits::class,
        NunoMaduro\PhpInsights\Domain\Sniffs\ForbiddenSetterSniff::class,

        PHP_CodeSniffer\Standards\Generic\Sniffs\CodeAnalysis\UselessOverridingMethodSniff::class, // gives error pages nopage.php
        PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\NoSilencedErrorsSniff::class,
        PHP_CodeSniffer\Standards\PEAR\Sniffs\WhiteSpace\ScopeClosingBraceSniff::class, // this might be buggy
        PHP_CodeSniffer\Standards\PSR1\Sniffs\Files\SideEffectsSniff::class,
        PHP_CodeSniffer\Standards\PSR2\Sniffs\ControlStructures\SwitchDeclarationSniff::class,

        ObjectCalisthenics\Sniffs\NamingConventions\ElementNameMinimalLengthSniff::class,

        PHP_CodeSniffer\Standards\Generic\Sniffs\Commenting\TodoSniff::class,
        PHP_CodeSniffer\Standards\PSR2\Sniffs\Files\ClosingTagSniff::class,
        PHP_CodeSniffer\Standards\PSR2\Sniffs\Files\EndFileNewlineSniff::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\Formatting\SpaceAfterNotSniff::class,
        PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\LowerCaseConstantSniff::class,

        PhpCsFixer\Fixer\Basic\BracesFixer::class,   // complains about correctly positioned comments....
        PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer::class, // complains about formatted initialisers.
        PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer::class, // not sure what this is meant to do - ordering of members?
        PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer::class, // buggy
        PhpCsFixer\Fixer\Comment\NoTrailingWhitespaceInCommentFixer::class, // buggy?
        PhpCsFixer\Fixer\ControlStructure\NoBreakCommentFixer::class,
        PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer::class, // buggy?
        PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer::class, // this one seems to be buggy
        PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer::class, // this one also seems buggy
        PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer::class, // this one seems to be buggy
        PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer::class,
        PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer::class, // complains about correct formatting...

        SlevomatCodingStandard\Sniffs\Classes\ClassConstantVisibilitySniff::class,
        SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\AssignmentInConditionSniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowEmptySniff::class,
        SlevomatCodingStandard\Sniffs\ControlStructures\DisallowYodaComparisonSniff::class,
        SlevomatCodingStandard\Sniffs\Namespaces\UseDoesNotStartWithBackslashSniff::class,
        SlevomatCodingStandard\Sniffs\Operators\DisallowEqualOperatorsSniff::class,
        SlevomatCodingStandard\Sniffs\PHP\UselessParenthesesSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DeclareStrictTypesSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\PropertyTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DisallowMixedTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\DisallowArrayTypeHintSyntaxSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\ParameterTypeHintSniff::class,
        SlevomatCodingStandard\Sniffs\TypeHints\UselessConstantTypeHintSniff::class,

    ],

    'config' => [
        NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh::class => [
             'maxComplexity' => 20,
        ],
        NunoMaduro\PhpInsights\Domain\Insights\ForbiddenGlobals::class => [
            'exclude' => [
                'framework/utility/*',
                'framework/ajax.php',
                'framework/pages/*',
                'model/*',
                'modelextend/*',
                'config/*',
                'pages/*',
                'support/*',
            ],
        ],

        ObjectCalisthenics\Sniffs\Metrics\MaxNestingLevelSniff::class => [
            'maxNestingLevel' => 6,
        ],
        \ObjectCalisthenics\Sniffs\Metrics\MethodPerClassLimitSniff::class => [
            'maxCount' => 15,
        ],

        ObjectCalisthenics\Sniffs\Files\ClassTraitAndInterfaceLengthSniff::class => [
            'maxLength' => 600,
        ],
        ObjectCalisthenics\Sniffs\Files\FunctionLengthSniff::class => [
            'maxLength' => 200,
        ],
        ObjectCalisthenics\Sniffs\Metrics\PropertyPerClassLimitSniff::class => [
            'maxCount' => 20,
        ],

        PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 190,
            'absoluteLineLimit' => 190
        ],

        PhpCsFixer\Fixer\Basic\BracesFixer::class => [
            'allow_single_line_closure' => false,
            'position_after_anonymous_constructs' => 'same', // possible values ['same', 'next']
            'position_after_control_structures' => 'next', // possible values ['same', 'next']
            'position_after_functions_and_oop_constructs' => 'next', // possible values ['same', 'next']
        ],

        SlevomatCodingStandard\Sniffs\Namespaces\NamespaceSpacingSniff::class => [
            'linesCountBeforeNamespace' => 0,
            'linesCountAfterNamespace' => 1,
        ],
        SlevomatCodingStandard\Sniffs\Namespaces\UseSpacingSniff::class => [
            'linesCountBeforeFirstUse' => 1,
            'linesCountBetweenUseTypes' => 0,
            'linesCountAfterLastUse' => 0,
        ],
        SlevomatCodingStandard\Sniffs\TypeHints\ReturnTypeHintSpacingSniff::class => [
           'spacesCountBeforeColon' => 1,
        ],
        //  ExampleInsight::class => [
        //      'key' => 'value',
        //  ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Requirements
    |--------------------------------------------------------------------------
    |
    | Here you may define a level you want to reach per `Insights` category.
    | When a score is lower than the minimum level defined, then an error
    | code will be returned. This is optional and individually defined.
    |
    */

    'requirements' => [
//        'min-quality' => 0,
//        'min-complexity' => 0,
//        'min-architecture' => 0,
//        'min-style' => 0,
//        'disable-security-check' => false,
    ],

];
