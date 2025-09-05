<?php

use App\Actions\Concerns\RecursiveConfigs;

test('it should return one config when no extends are defined', function () {
    $configs = (new RecursiveConfigs(
        base_path('tests/Fixtures/test-a/scanner.json'),
    ))->execute();

    expect($configs)->toHaveCount(1);
    expect($configs[0])->toBe([
        'lang_path' => 'lang/',
        'paths' => [
            0 => 'resources/',
        ],
        'extensions' => [
            0 => '.php',
        ],
        'methods' => [
            0 => '__',
            1 => 'trans',
            2 => 'trans_choice',
        ],
        'base_path' => base_path('tests/Fixtures/test-a'),
    ]);
});

test('it should return multiple configs when extends are defined', function () {
    $configs = (new RecursiveConfigs(
        base_path('tests/Fixtures/test-f/scanner.json'),
    ))->execute();

    expect($configs)->toHaveCount(2);
    expect($configs[0])->toBe([
        'lang_path' => 'lang1/',
        'paths' => [
            0 => 'resources1/',
        ],
        'extensions' => [
            0 => '.php',
        ],
        'methods' => [
            0 => '__',
            1 => 'trans',
            2 => 'trans_choice',
        ],
        'base_path' => base_path('tests/Fixtures/test-f/module1'),
    ]);
    expect($configs[1])->toBe([
        'lang_path' => 'lang2/',
        'paths' => [
            0 => 'resources2/',
        ],
        'extensions' => [
            0 => '.php',
        ],
        'methods' => [
            0 => '__',
            1 => 'trans',
            2 => 'trans_choice',
        ],
        'base_path' => base_path('tests/Fixtures/test-f/module2'),
    ]);
});
