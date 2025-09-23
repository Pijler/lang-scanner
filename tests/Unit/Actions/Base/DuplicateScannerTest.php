<?php

use App\Actions\Base\DuplicateScanner;

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
