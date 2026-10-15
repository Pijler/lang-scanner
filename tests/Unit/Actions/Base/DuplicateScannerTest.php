<?php

use App\Actions\Base\DuplicateScanner;
use App\Actions\Scanner;

beforeEach(function () {
    $this->setStaticProperty(DuplicateScanner::class, 'cache', []);
});

test('it should check if remove is enabled via option', function () {
    console('default', ['--remove' => true]);

    $duplicateScanner = resolve(DuplicateScanner::class);

    expect($this->callMethod($duplicateScanner, 'remove'))->toBeTrue();

    app()->forgetInstance(DuplicateScanner::class);

    console('default', ['--remove' => false]);

    $duplicateScanner = resolve(DuplicateScanner::class);

    expect($this->callMethod($duplicateScanner, 'remove'))->toBeFalse();
});

test('it should filter empty arrays recursively', function () {
    console('default', []);

    $input = [
        'a' => 1,
        'b' => [],
        'c' => [
            'd' => [],
            'e' => 'filled',
            'f' => [
                'g' => [],
                'h' => 'value',
            ],
        ],
        'i' => [],
    ];

    $expected = [
        'a' => 1,
        'c' => [
            'e' => 'filled',
            'f' => [
                'h' => 'value',
            ],
        ],
    ];

    $duplicateScanner = resolve(DuplicateScanner::class);

    $return = $this->callMethod($duplicateScanner, 'filterArray', [$input]);

    expect($return)->toEqual($expected);
});

test('it should return duplicates and filtered result', function () {
    $cacheKey = 'test';

    console('default', []);

    $this->setStaticProperty(DuplicateScanner::class, 'cache', [
        $cacheKey => [
            'a' => 1,
            'b' => 2,
            'nested' => [
                'x' => 'y',
            ],
        ],
    ]);

    $current = [
        'a' => 1,
        'b' => 3,
        'nested' => [
            'x' => 'y',
            'z' => 'w',
        ],
        'emptyArray' => [],
    ];

    $duplicateScanner = resolve(DuplicateScanner::class);

    [$result, $duplicates] = $this->callMethod($duplicateScanner, 'getDuplicates', [$current, $cacheKey]);

    expect($duplicates)->toEqual([
        'a',
        'b',
        'nested.x',
    ]);

    expect($result)->toEqual([
        'nested' => [
            'z' => 'w',
        ],
    ]);
});

test('it should return empty duplicates if no cache', function () {
    $cacheKey = 'empty';

    console('default', []);

    $current = [
        'foo' => 'bar',
        'nested' => ['x' => 'y'],
    ];

    $duplicateScanner = resolve(DuplicateScanner::class);

    [$result, $duplicates] = $this->callMethod($duplicateScanner, 'getDuplicates', [$current, $cacheKey]);

    expect($duplicates)->toBeEmpty();
    expect($result)->toEqual($current);
});

test('it should report every missing and duplicate key as an issue', function () {
    run('default', [
        '--duplicate' => true,
        '--config' => base_path('tests/Fixtures/test-o/scanner.json'),
    ]);

    $duplicateScanner = $this->callProperty(resolve(Scanner::class), 'duplicate');

    $changes = $this->callProperty($duplicateScanner, 'changes');

    expect($changes)->not->toBeEmpty();

    foreach ($changes as $change) {
        expect($change['issues'])->toHaveCount($change['count']);
    }

    expect($changes[1]['issues'])->toBe(['own', 'shared']);
});
