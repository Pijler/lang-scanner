<?php

use App\Repositories\ConfigurationJsonRepository;

test('it should return the config content', function () {
    $repository = new ConfigurationJsonRepository(
        path: base_path('tests/Fixtures/scanner.json')
    );

    $config = $this->callMethod($repository, 'get');

    expect($config)->toBeArray();
    expect($config)->toHaveKeys(['extends', 'scanner']);

    expect($config['extends'])->toBe([]);
    expect($config['scanner'])->toBe([
        [
            'lang_path' => 'lang/',
            'paths' => [
                0 => 'app/',
                1 => 'resources/',
            ],
            'extensions' => [
                0 => '.php',
                1 => '.html',
            ],
            'methods' => [
                0 => '__(*)',
                1 => 'trans(*)',
                2 => 'trans_choice(*)',
            ],
        ],
    ]);

    $repository = new ConfigurationJsonRepository(
        path: base_path('tests/Fixtures/test-b/scanner.json')
    );

    $config = $this->callMethod($repository, 'get');

    expect($config)->toBeArray();
    expect($config)->toHaveKeys(['scanner']);

    expect($config['scanner'])->toBe([
        [
            'lang_path' => 'lang/',
            'paths' => [
                0 => 'resources/',
            ],
            'extensions' => [
                0 => '.php',
            ],
            'methods' => [
                0 => '__(*)',
                1 => 'trans(*)',
                2 => 'trans_choice(*)',
            ],
        ],
    ]);
});

test('it should return the extends config', function () {
    $repository = new ConfigurationJsonRepository(
        path: base_path('tests/Fixtures/scanner.json')
    );

    $extends = $repository->extends();

    expect($extends)->toBeArray();
    expect($extends)->toBe([]);

    $repository = new ConfigurationJsonRepository(
        path: base_path('tests/Fixtures/test-f/scanner.json')
    );

    $extends = $repository->extends();

    expect($extends)->toBeArray();
    expect($extends)->toBe([
        '/module1/scanner.json',
        '/module2/scanner.json',
    ]);
});

test('it should return the scanner config', function () {
    $repository = new ConfigurationJsonRepository(
        path: base_path('tests/Fixtures/scanner.json')
    );

    $scanner = $repository->scanner();

    expect($scanner)->toBeArray();
    expect($scanner)->toBe([
        [
            'lang_path' => 'lang/',
            'paths' => [
                0 => 'app/',
                1 => 'resources/',
            ],
            'extensions' => [
                0 => '.php',
                1 => '.html',
            ],
            'methods' => [
                0 => '__(*)',
                1 => 'trans(*)',
                2 => 'trans_choice(*)',
            ],
        ],
    ]);

    $repository = new ConfigurationJsonRepository(
        path: base_path('tests/Fixtures/test-f/scanner.json')
    );

    $scanner = $repository->scanner();

    expect($scanner)->toBeArray();
    expect($scanner)->toBe([]);
});
